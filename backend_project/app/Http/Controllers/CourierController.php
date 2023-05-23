<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

class CourierController extends Controller
{
    public function getAvailableOrders(Request $request)
    {
        try {
            return Order::query()
                ->where('status', 'Delivery')
                ->where('courier_id', null)
                ->select('id', 'deliveryTime', 'orderTime', 'price', 'address')
                ->selectRaw('restaurant_id AS restaurantId')
                ->get();
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function getCurrentOrders(Request $request)
    {
        try {
            $userId = $request->input('userInfo')['id'];

            return Order::query()
                ->where('status', 'Delivery')
                ->where('courier_id', $userId)
                ->select('id', 'deliveryTime', 'orderTime', 'price', 'address')
                ->selectRaw('restaurant_id AS restaurantId')
                ->get();
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function acceptOrder(Request $request, $orderId)
    {
        try {
            $userId = $request->input('userInfo')['id'];
            $order = Order::findOrFail($orderId);

            if ($order->status != 'Delivery' || $order->courier_id != null) {
                return $this->returnBadResponse(409, 'This order cannot be accepted for delivery');
            }

            $ordersCount = Order::query()
                ->where('status', 'Delivery')
                ->where('courier_id', $userId)
                ->count();

            if ($ordersCount >= 3) {
                return $this->returnBadResponse(409, 'You cannot complete more than three orders at the same time');
            }

            $order->update([
                'courier_id' => $userId
            ]);

            return Order::query()->where('id', $orderId)
                ->select('id', 'deliveryTime', 'orderTime', 'price', 'address')->selectRaw('restaurant_id AS restaurantId')->get();
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined order with id: ' . $orderId);
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function cancelOrder(Request $request, $orderId)
    {
        try {
            $userId = $request->input('userInfo')['id'];
            $order = Order::findOrFail($orderId);
            if ($order->courier_id != $userId) {
                return $this->returnBadResponse(403, 'This order does not belong to you');
            }

            if ($order->status != 'Delivery') {
                return $this->returnBadResponse(403, 'You can`t cancel this order. It isn`t in delivery');
            }

            $order->update([
                'courier_id' => null
            ]);

            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined order with id: ' . $orderId);
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }
}
