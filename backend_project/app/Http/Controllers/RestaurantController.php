<?php

namespace App\Http\Controllers;

use App\Auth\JWTAuthentication;
use App\Models\Dish;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Menu;
use App\Services\FiltersSortingPaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AffiliationsService;

class RestaurantController extends Controller
{
    public function getRestaurantsList()
    {
        try {
            return Restaurant::all();
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function getMenusList($restaurantId)
    {
        try {
            Restaurant::findOrFail($restaurantId);
            return Menu::with(['dishes' => function ($query) {
                $query->where('isDeleted', false)->get();
            }])->where('restaurant_id', $restaurantId)->get();
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined restaurant with id: ' . $restaurantId);
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function getDishesList(Request $request, $restaurantId)
    {
        try {
            Restaurant::findOrFail($restaurantId);
            return FiltersSortingPaginationService::getDishes($request, $restaurantId);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined restaurant with id: ' . $restaurantId);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function getOrdersList(Request $request, $restaurantId)
    {
        try {
            Restaurant::findOrFail($restaurantId);
            $usersRestaurantId = $request->input('userInfo')['restaurant_id'];

            if (!AffiliationsService::restaurantAffiliation($usersRestaurantId, $restaurantId)) {
                return $this->returnBadResponse(409, 'You do not have permission to access the restaurant with id: ' . $restaurantId);
            }

            $validatedData = $request->validate([
                'status' => 'nullable|in:Created,Kitchen,Packaging,Delivery,Delivered,Canceled',
            ]);

            $ordersQuery = Order::query()->where('restaurant_id', $restaurantId);
            if ($request->has('status')) {
                $ordersQuery->where('status', $validatedData['status']);
            }

            return $ordersQuery->select('id', 'orderTime', 'deliveryTime', 'price', 'status')->get();
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined restaurant with id: ' . $restaurantId);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, 'Invalid status parameter: ' . $request->input('status'));
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function getCooksOrdersList(Request $request)
    {
        try {
            $cookId = $request->input('userInfo')['id'];

            $ordersQuery = Order::query()->where('cook_id', $cookId)->where('status', 'Kitchen')->orWhere(
                'status', 'Packaging');
            return $ordersQuery->select('id', 'orderTime', 'deliveryTime', 'price', 'status')->get();
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function assignOrderToCook(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            $cookId = $request->input('userInfo')['id'];
            $cookRestaurantId = $request->input('userInfo')['restaurantId'];
            if (!AffiliationsService::orderAffiliation($cookRestaurantId, $order)) {
                return $this->returnBadResponse(409, 'Tou don`t have permission - this order from another restaurant');
            }

            if ($order->status != 'Created' || $order->cook_id != null) {
                if ($order->cook_id == $cookId) {
                    return $this->returnBadResponse(409, 'This order is already assigned to you');
                }
                return $this->returnBadResponse(409, 'This order is already assigned to another cook');
            }

            $validatedData = $request->validate([
                'deliveryTime' => 'required|date_format:Y-m-d\TH:i:s.v\Z|after:now',
            ]);

            $order->update([
                'status' => 'Kitchen',
                'cook_id' => $cookId,
                'deliveryTime' => \DateTime::createFromFormat('Y-m-d\TH:i:s.v\Z', $validatedData['deliveryTime'])
            ]);

            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined order with id: ' . $orderId);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, 'Invalid deliveryTime parameter: ' . $request->input('deliveryTime'));
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function cancelCookOrder(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            $cookId = $request->input('userInfo')['id'];
            $cookRestaurantId = $request->input('userInfo')['restaurantId'];
            if (!AffiliationsService::orderAffiliation($cookRestaurantId, $order)) {
                return $this->returnBadResponse(409, 'Tou don`t have permission - this order from another restaurant');
            }

            if ($order->cook_id != $cookId) {
                return $this->returnBadResponse(403, 'This order is not assigned to you');
            }

            $order->update([
                'status' => 'Created',
                'cook_id' => null
            ]);
            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined order with id: ' . $orderId);
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }
}
