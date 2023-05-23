<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Rating;
use App\Models\Restaurant;
use App\Services\FiltersSortingPaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;
use App\Services\RatingService;
use App\Services\AffiliationsService;
use App\Services\DishCategoryService;
class DishController extends Controller
{
    private function savePhoto(Request $request)
    {
        if ($request->hasFile('photo')) {
            $file_name = time() . '_' . $request->file('photo')->getClientOriginalName();
            $request->file('photo')->storeAs('uploads', $file_name, 'public');
            return $file_name;
        } else {
            return null;
        }
    }

    private function validateDishInf(Request $request)
    {
        return $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'isVegetarian' => 'nullable|in:true,false,0,1',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'nullable',
            'isActive' => 'nullable|in:true,false,0,1'
        ]);
    }

    public function getDishesList(Request $request)
    {
        try {
            return FiltersSortingPaginationService::getDishes($request);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function getDish($dishId)
    {
        try {
            $dish = Dish::findOrFail($dishId);
            if ($dish->isDeleted === 0) {
                return $dish;
            } else {
                return $this->returnBadResponse(409, 'Dish with this id has been deleted');
            }
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined dish with id: ' . $dishId);
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function checkCanUserRate(Request $request, $dishId)
    {
        try {
            $dish = Dish::findOrFail($dishId);
            if ($dish->isDeleted !== 0) {
                return $this->returnBadResponse(409, 'Dish with this id has been deleted');
            }
            $userId = $request->input('userInfo')['id'];

            return response(RatingService::checkRatingFeasibility($userId, $dishId) ? 'true' : 'false', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined dish with id: ' . $dishId);
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function rateDish(Request $request, $dishId)
    {
        try {
            $dish = Dish::findOrFail($dishId);
            if ($dish->isDeleted !== 0) {
                return $this->returnBadResponse(409, 'Dish with this id has been deleted');
            }
            $customerId = $request->input('userInfo')['id'];

            $validatedData = $request->validate([
                'rating' => 'required|integer|between:1,10'
            ]);

            if (!RatingService::checkRatingFeasibility($customerId, $dishId)) {
                return $this->returnBadResponse(403, 'You can`t rate this dish');
            }

            if (Rating::query()->where('user_id', $customerId)->where('dish_id', $dishId)->exists()) {
                Rating::where('user_id', $customerId)->where('dish_id', $dishId)->update([
                    'rating' => $validatedData['rating']
                ]);
            } else {
                Rating::create([
                    'dish_id' => $dishId,
                    'user_id' => $customerId,
                    'rating' => $validatedData['rating']
                ]);
            }

            RatingService::recalculateDishRating($dishId);
            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined dish with id: ' . $dishId);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, 'Invalid rating parameter: ' . $request->input('rating') . ', it must be integer between 1 and 10');
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function deleteDishRating(Request $request, $dishId)
    {
        try {
            $dish = Dish::findOrFail($dishId);
            if ($dish->isDeleted !== 0) {
                return $this->returnBadResponse(409, 'Dish with this id has been deleted');
            }
            $customerId = $request->input('userInfo')['id'];

            if (!RatingService::checkRatingFeasibility($customerId, $dishId)) {
                return $this->returnBadResponse(403, 'You have not rated this dish');
            }

            Rating::where('user_id', $customerId)->where('dish_id', $dishId)->delete();

            RatingService::recalculateDishRating($dishId);
            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined dish with id: ' . $dishId);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, 'Invalid rating parameter: ' . $request->input('rating') . ', it must be integer between 1 and 10');
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function createDish(Request $request, $restaurantId)
    {
        try {
            Restaurant::findOrFail($restaurantId);
            $usersRestaurantId = $request->input('userInfo')['restaurantId'];
            if (!AffiliationsService::restaurantAffiliation($usersRestaurantId, $restaurantId)) {
                return $this->returnBadResponse(409, 'You do not have permission to access the restaurant with id: ' . $restaurantId);
            }

            $validatedData = $this->validateDishInf($request);
            if ($validatedData['category']) {
                if (!DishCategoryService::checkIsCategoryExist($validatedData['category'])) {
                    return $this->returnBadResponse(400, 'Invalid category');
                }
            }

            Dish::create([
                'name' => $validatedData['name'],
                'price' => $validatedData['price'],
                'description' => $validatedData['description'] ?? null,
                'isVegetarian' => $validatedData['isVegetarian'] ?? false,
                'photo' => $this->savePhoto($request) ?? null,
                'category' => $validatedData['category'] ?? null,
                'isActive' => $validatedData['isActive'] ?? true,
                'restaurant_id' => $restaurantId,
            ]);

            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined restaurant with id: ' . $restaurantId);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function updateDish(Request $request, $dishId)
    {
        try {
            $dish = Dish::findOrFail($dishId);
            if ($dish->isDeleted !== 0) {
                return $this->returnBadResponse(409, 'Dish with this id has been deleted');
            }
            $usersRestaurantId = $request->input('userInfo')['restaurantId'];

            if (!AffiliationsService::dishAffiliation($dish, $usersRestaurantId)) {
                return $this->returnBadResponse(409, 'You do not have permission to access the dish with id: ' . $dishId);
            }

            $validatedData = $this->validateDishInf($request);
            if ($validatedData['category']) {
                if (!DishCategoryService::checkIsCategoryExist($validatedData['category'])) {
                    return $this->returnBadResponse(400, 'Invalid category');
                }
            }

            Dish::find($dishId)->update([
                'name' => $validatedData['name'],
                'price' => $validatedData['price'],
                'description' => $validatedData['description'] ?? null,
                'isVegetarian' => $validatedData['isVegetarian'] ?? false,
                'category' => $validatedData['category'] ?? null,
                'photo' => $this->savePhoto($request) ?? null,
                'isActive' => $validatedData['isActive'] ?? true,
            ]);

            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined dish with id: ' . $dishId);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function deleteDish(Request $request, $dishId)
    {
        try {
            $dish = Dish::findOrFail($dishId);
            if ($dish->isDeleted !== 0) {
                return $this->returnBadResponse(409, 'Dish with this id has been deleted');
            }
            $usersRestaurantId = $request->input('userInfo')['restaurantId'];

            if (!AffiliationsService::dishAffiliation($dish, $usersRestaurantId)) {
                return $this->returnBadResponse(409, 'You do not have permission to access the dish with id: ' . $dishId);
            }

            $dish->isActive = false;
            $dish->isDeleted = true;
            $dish->save();
            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined dish with id: ' . $dishId);
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function changeDishActivityStatus(Request $request, $dishId)
    {
        try {
            $dish = Dish::findOrFail($dishId);
            if ($dish->isDeleted !== 0) {
                return $this->returnBadResponse(409, 'Dish with this id has been deleted');
            }
            $usersRestaurantId = $request->input('userInfo')['restaurantId'];

            if (!AffiliationsService::dishAffiliation($dish, $usersRestaurantId)) {
                return $this->returnBadResponse(409, 'You do not have permission to access the dish with id: ' . $dishId);
            }

            $validatedData = $request->validate([
                'status' => 'required|boolean'
            ]);

            $dish->isActive = $validatedData['status'];
            $dish->save();
            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined dish with id: ' . $dishId);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function getDishesCategories()
    {
        try {
            return DB::table('categories')->select('category')->get();
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function getPhotoFromAdmin(Request $request) {
        try {
            $adminToken = $request->input('token');

            if (!$adminToken) {
                return $this->returnBadResponse(400, 'No token');
            }

            if (!password_verify(env('JWT_SECRET'), $adminToken)) {
                return $this->returnBadResponse(403, 'Wrong token');
            }

            $path = $this->savePhoto($request);

            return response(['path' => $path], 200);
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }
}
