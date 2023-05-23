<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;
use App\Services\AffiliationsService;

class MenuController extends Controller
{
    public function getDishesList(Request $request, $menuId)
    {
        try {
            $menu = Menu::findOrFail($menuId);

            $dishes = Dish::whereHas('menus', function ($query) use ($menuId) {
                $query->where('menu_id', $menuId);
            })->where('isDeleted', false)->get();

            return [
                'menu' => $menu,
                'dishes' => $dishes
            ];
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined menu with id: ' . $menuId);
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function createMenu(Request $request, $restaurantId)
    {
        try {
            $restaurant = Restaurant::findOrFail($restaurantId);
            $managerRestaurantId = $request->input('userInfo')['restaurantId'];

            if (!AffiliationsService::restaurantAffiliation($managerRestaurantId, $restaurantId)) {
                return $this->returnBadResponse(409, 'You do not have permission to access the restaurant with id: ' . $restaurantId);
            }

            $validatedData = $request->validate([
                'name' => 'required|string',
            ]);

            $menu = Menu::create([
                'name' => $validatedData['name'],
                'restaurant_id' => $restaurantId
            ]);

            return [
                "restaurant" => $restaurant,
                "menu" => $menu
            ];
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined restaurant with id: ' . $restaurantId);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, 'Invalid name parameter: ' . $request->input('name'));
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function deleteMenu(Request $request, $menuId)
    {
        try {
            $menu = Menu::findOrFail($menuId);
            $managerRestaurantId = $request->input('userInfo')['restaurantId'];

            if (!AffiliationsService::menuAffiliation($managerRestaurantId, $menu)) {
                return $this->returnBadResponse(409, 'You do not have permission to access the restaurant with id: ' . $menu->restaurant_id);
            }

            $menu->delete();
            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined menu with id: ' . $menuId);
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function changeMenuName(Request $request, $menuId)
    {
        try {
            $menu = Menu::findOrFail($menuId);
            $managerRestaurantId = $request->input('userInfo')['restaurantId'];

            if (!AffiliationsService::menuAffiliation($managerRestaurantId, $menu)) {
                return $this->returnBadResponse(409, 'You do not have permission to access the restaurant with id: ' . $menu->restaurant_id);
            }

            $validatedData = $request->validate([
                'name' => 'required|string',
            ]);

            $menu->update([
                'name' => $validatedData['name'],
            ]);
            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined menu with id: ' . $menuId);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, 'Invalid name parameter: ' . $request->input('name'));
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function addDishToMenu(Request $request, $menuId, $dishId)
    {
        try {
            $dish = Dish::findOrFail($dishId);
            if ($dish->isDeleted != 0) {
                return $this->returnBadResponse(409, 'Dish with this id has been deleted');
            }
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined dish with id: ' . $dishId);
        }
        try {
            $menu = Menu::findOrFail($menuId);

            if ($menu->restaurant_id != $dish->restaurant_id) {
                return $this->returnBadResponse(409, 'The restaurants of dish and menu are different');
            }

            $managerRestaurantId = $request->input('userInfo')['restaurantId'];
            if (!AffiliationsService::menuAffiliation($managerRestaurantId, $menu)) {
                return $this->returnBadResponse(409, 'You do not have permission to access the restaurant with id: ' . $menu->restaurant_id);
            }

            if ($menu->dishes()->wherePivot('dish_id', $dishId)->exists()) {
                return $this->returnBadResponse(409, 'This dish has already been added to this menu');
            }

            $menu->dishes()->attach($dishId);
            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined menu with id: ' . $menuId);
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    public function removeDishFromMenu(Request $request, $menuId, $dishId)
    {
        try {
            $dish = Dish::findOrFail($dishId);
            if ($dish->isDeleted != 0) {
                return $this->returnBadResponse(409, 'Dish with this id has been deleted');
            }
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined dish with id: ' . $dishId);
        }
        try {
            $menu = Menu::findOrFail($menuId);

            if ($menu->restaurant_id != $dish->restaurant_id) {
                return $this->returnBadResponse(409, 'The restaurants of dish and menu are different');
            }

            $managerRestaurantId = $request->input('userInfo')['restaurantId'];
            if (!AffiliationsService::menuAffiliation($managerRestaurantId, $menu)) {
                return $this->returnBadResponse(409, 'You do not have permission to access the restaurant with id: ' . $menu->restaurant_id);
            }

            $isExists = DB::table('dish_menu')
                ->where('dish_id', $dishId)
                ->where('menu_id', $menuId)
                ->exists();
            if (!$isExists) {
                return $this->returnBadResponse(404, 'The menu with id: `' . $menuId . '` does not contain a dish with id: `' . $dishId . '`');
            }

            $menu->dishes()->detach($dishId);
            return response('', 200);
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }
}
