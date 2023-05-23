<?php

namespace App\Http\Controllers;

use App\Models\Basket;
use App\Models\Dish;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class BasketController extends Controller
{
    private function checkAnotherRestaurantDishes($restaurantId, $userId)
    {
        $restaurantIds = Basket::where('user_id', $userId)
            ->whereHas('dish')
            ->with('dish.restaurant:id')
            ->get()
            ->pluck('dish.restaurant.id')
            ->unique()
            ->toArray();
        foreach ($restaurantIds as $id) {
            if ($restaurantId != $id) {
                return false;
            }
        }

        return true;
    }

    private function checkIsDishInBasket($userId, $dishId) {
        $isDishExist = Basket::query()->where('dish_id', $dishId)->where('user_id', $userId)->exists();

        if (!$isDishExist) {
            return false;
        }
        return true;
    }

    public function addDishToBasket(Request $request, $dishId)
    {
        try {
            $dish = Dish::findOrFail($dishId);
            if ($dish->isDeleted !== 0) {
                return $this->returnBadResponse(409, 'Dish with this id has been deleted');
            }

            $userId = $request->input('userInfo')['id'];

            if ($this->checkIsDishInBasket($userId, $dishId)) {
                return $this->returnBadResponse(409, 'This dish is already in basket');
            }

            if (!$this->checkAnotherRestaurantDishes($dish->restaurant_id, $userId)) {
                return $this->returnBadResponse(409, 'Your basket contains dishes from other restaurants. ' .
                    'Empty your shopping basket to add dishes from this restaurant');
            }

            if (!$dish->isActive) {
                return $this->returnBadResponse(409, 'The dish with id `' . $dishId . '` isn`t active. You can`t add it to basket');
            }

            Basket::create([
                'dish_id' => $dishId,
                'user_id' => $userId,
                'amount' => 1
            ]);

            return response('', 200);
        }  catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined dish with id: ' . $dishId);
        }  catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function getBasket(Request $request)
    {
        try {
            $userId = $request->input('userInfo')['id'];

            return Basket::with(['dish' => function ($query) {
                $query->select('id', 'name', 'price', 'photo', 'isActive');
            }])
                ->select('dish_id', DB::raw('SUM(amount) as total_amount'))
                ->where('user_id', $userId)
                ->groupBy('dish_id')
                ->get()
                ->map(function ($basket) {
                    return [
                        'id' => $basket->dish->id,
                        'name' => $basket->dish->name,
                        'price' => $basket->dish->price,
                        'amount' => (int) $basket->total_amount,
                        'totalPrice' => $basket->total_amount * $basket->dish->price,
                        'isActive' => $basket->dish->isActive,
                        'photo' => $basket->dish->photo,
                    ];
                });
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function removeDishFromBasket(Request $request, $dishId)
    {
        try {
            Dish::findOrFail($dishId);
            $userId = $request->input('userInfo')['id'];

            if (!$this->checkIsDishInBasket($userId, $dishId)) {
                return $this->returnBadResponse(409, 'This dish isn`t in basket');
            }

            Basket::query()
                ->where('user_id', $userId)
                ->where('dish_id', $dishId)
                ->delete();

            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined dish with id: ' . $dishId);
        }  catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function emptyBasket(Request $request)
    {
        try {
            $userId = $request->input('userInfo')['id'];
            Basket::query()
                ->where('user_id', $userId)
                ->delete();

            return response('', 200);
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function changeDishAmount(Request $request, $dishId)
    {
        try {
            $dish = Dish::findOrFail($dishId);
            $userId = $request->input('userInfo')['id'];

            if (!$this->checkIsDishInBasket($userId, $dishId)) {
                return $this->returnBadResponse(409, 'This dish isn`t in basket');
            }

            $validatedData = $request->validate([
                'amount' => 'required|integer|min:1|max:999'
            ]);

            if (!$dish->isActive) {
                return $this->returnBadResponse(409, 'The dish with id `' . $dishId . '` isn`t active. You can only remove it from basket');
            }

            Basket::query()
                ->where('user_id', $userId)
                ->where('dish_id', $dishId)
                ->update(['amount' => $validatedData['amount']]);

            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined dish with id: ' . $dishId);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function removeInactiveDishes(Request $request)
    {
        try {
            $userId = $request->input('userInfo')['id'];

            $dishes = Basket::whereHas('dish', function ($query) {
                $query->where('isActive', false);
            })->where('user_id', $userId);

            if ($dishes->exists()) {
                $dishes->delete();
                return response(['message' => 'Inactive dishes have been removed'], 200);
            } else {
                return response(['message' => 'There are no inactive dishes in the basket'], 200);
            }
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }
}
