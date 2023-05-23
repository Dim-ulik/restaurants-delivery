<?php

namespace App\Http\Controllers;

use App\Models\Basket;
use App\Models\Dish;
use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderController extends Controller
{
    private function checkDishesInBasket($userId)
    {
        $inactiveDishes = Basket::where('user_id', $userId)->whereHas('dish', function ($query) {
            $query->where('isActive', false);
        });

        if ($inactiveDishes->exists()) {
            return false;
        } else {
            return true;
        }
    }

    private function checkDishesPreviousOrder($orderId)
    {
        $inactiveDishes = DB::table('dish_order')
            ->select('dish_order.*')
            ->join('dishes', 'dish_order.dish_id', '=', 'dishes.id')
            ->where('dish_order.order_id', $orderId)
            ->where(function ($query) {
                $query->where('dishes.isActive', false)
                    ->orWhere('dishes.isDeleted', true);
            });

        if ($inactiveDishes->exists()) {
            return false;
        } else {
            return true;
        }
    }

    private function checkIsUsersOrder($order, $customerId)
    {
        if ($order->customer_id != $customerId) {
            return false;
        }
        return true;
    }

    private function checkAvailableStatus($orderUserId, $userId, $availableStatuses, $validatedData)
    {
        if ($orderUserId == $userId) {
            if (!in_array($validatedData['status'], $availableStatuses)) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    public function createOrder(Request $request)
    {
        try {
            $userId = $request->input('userInfo')['id'];

            if (!$this->checkDishesInBasket($userId)) {
                return $this->returnBadResponse(409, 'You can`t create order - you have inactive dishes in your basket');
            }

            if (!Basket::query()->where('user_id', $userId)->exists()) {
                return $this->returnBadResponse(409, 'You can`t create order - your basket is empty');
            }

            $validatedData = $request->validate([
                'deliveryTime' => 'nullable|date_format:Y-m-d\TH:i:s.v\Z|after:now',
                'address' => 'required|string|min: 5'
            ]);

            $totalPrice = Basket::where('user_id', $userId)
                ->join('dishes', 'baskets.dish_id', '=', 'dishes.id')
                ->sum(DB::raw('baskets.amount * dishes.price'));

            $restaurantId = Basket::where('user_id', $userId)->first()->dish->restaurant_id;

            $order = Order::create([
                'deliveryTime' => \DateTime::createFromFormat('Y-m-d\TH:i:s.v\Z', $validatedData['deliveryTime']),
                'price' => $totalPrice,
                'address' => $validatedData['address'],
                'restaurant_id' => $restaurantId,
                'customer_id' => $userId
            ]);

            $dishes = Basket::query()->where('user_id', $userId)->get();
            foreach ($dishes as $dish) {
                $totalPrice = Dish::find($dish->dish_id)->price * $dish->amount;
                $order->dishes()->attach($dish->dish_id, ['amount' => $dish->amount, 'totalPrice' => $totalPrice]);
            }
            Basket::query()->where('user_id', $userId)->delete();

            return Order::where('id', $order->id)->select('id', 'deliveryTime', 'orderTime', 'price', 'address')
                ->selectRaw('restaurant_id AS restaurantId')->get();
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function repeatOrder(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            $userId = $request->input('userInfo')['id'];

            if (!$this->checkIsUsersOrder($order, $userId)) {
                return $this->returnBadResponse(403, 'This is another customer order');
            }

            $validatedData = $request->validate([
                'deliveryTime' => 'nullable|date_format:Y-m-d\TH:i:s.v\Z|after:now',
                'address' => 'required|string|min: 5'
            ]);

            if (!$this->checkDishesPreviousOrder($orderId)) {
                return $this->returnBadResponse(409, 'Can`t repeat this order. Some dishes from it are deleted or inactive');
            }

            $totalPrice = $order->dishes()
                ->sum(DB::raw('dish_order.amount * dishes.price'));

            $newOrder = Order::create([
                'deliveryTime' => \DateTime::createFromFormat('Y-m-d\TH:i:s.v\Z', $validatedData['deliveryTime']),
                'price' => $totalPrice,
                'address' => $validatedData['address'],
                'restaurant_id' => $order->restaurant_id,
                'customer_id' => $userId
            ]);

            $dishes = DB::table('dish_order')
                ->where('order_id', $orderId)
                ->join('dishes', 'dish_order.dish_id', '=', 'dishes.id')
                ->get();

            foreach ($dishes as $dish) {
                $totalPrice = Dish::find($dish->dish_id)->price * $dish->amount;
                $newOrder->dishes()->attach($dish->dish_id, ['amount' => $dish->amount, 'totalPrice' => $totalPrice]);
            }

            return Order::where('id', $order->id)->select('id', 'deliveryTime', 'orderTime', 'price', 'address')
                ->selectRaw('restaurant_id AS restaurantId')->get();
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined order with id: ' . $orderId);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function changeOrderStatus(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            $userId = $request->input('userInfo')['id'];

            $validatedData = $request->validate([
                'status' => 'required|in:Packaging,Delivery,Delivered,Canceled',
            ]);

            $flag = $this->checkAvailableStatus($order->customer_id, $userId, ['Canceled'], $validatedData) ||
                $this->checkAvailableStatus($order->cook_id, $userId, ['Packaging', 'Delivery'], $validatedData) ||
                $this->checkAvailableStatus($order->courier_id, $userId, ['Canceled', 'Delivered'], $validatedData);

            if (!$flag) {
                return $this->returnBadResponse(403, 'You can`t set this status to this order');
            }

            $flag = ($validatedData['status'] == 'Canceled' && ($order->status != 'Created' && $order->status != 'Delivery')) ||
                ($validatedData['status'] == 'Packaging' && ($order->status != 'Kitchen')) ||
                ($validatedData['status'] == 'Delivery' && ($order->status != 'Packaging')) ||
                ($validatedData['status'] == 'Delivered' && ($order->status != 'Delivery'));

            if ($flag) {
                return $this->returnBadResponse(409, 'You can`t set this status to this order.' . ' You violated the order of setting the status');
            }

            $order->update([
                'status' => $validatedData['status']
            ]);
            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined order with id: ' . $orderId);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function getOrdersList(Request $request)
    {
        try {
            $userId = $request->input('userInfo')['id'];
            return Order::query()->where('customer_id', $userId)
                ->select('id', 'deliveryTime', 'orderTime', 'status', 'price', 'address')
                ->selectRaw('restaurant_id AS restaurantId')->get();
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function getOrder(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            $userId = $request->input('userInfo')['id'];

            if ($order->customer_id != $userId) {
                if ($orderId->cook_id != $userId) {
                    if ($orderId->courier_id != $userId) {
                        return $this->returnBadResponse(403, 'You haven`t permission to this order');
                    }
                }
            }

            $dishes = $order->dishes()
                ->select('dishes.id', 'dishes.name', 'dishes.price', 'dish_order.totalPrice', 'dish_order.amount', 'dishes.photo')
                ->where('dish_order.order_id', $orderId)
                ->get()->toArray();

            return [
                'id' => $orderId,
                'deliveryTime' => $order->deliveryTime,
                'orderTime' => $order->orderTime,
                'status' => $order->status,
                'price' => $order->price,
                'address' => $order->address,
                'restaurantId' => $order->restaurant_id,
                'dishes' => $dishes
            ];
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined order with id: ' . $orderId);
        }  catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }
}
