<?php

namespace App\Services;

use App\Models\Dish;
use Illuminate\Http\Request;

class FiltersSortingPaginationService {
    public static function getDishes(Request $request, $restaurantId = null)
    {
        $validatedData = $request->validate([
            'category' => 'nullable|array',
            'page' => 'nullable|integer',
            'vegetarian' => 'nullable|in:true,false,0,1',
            'sorting' => 'nullable|in:NameAsc,NameDesc,PriceAsc,PriceDesc,RatingAsc,RatingDesc',
            'onlyActive' => 'nullable|in:true,false,0,1',
        ]);
        if ($request->get('category')) {
            foreach ($validatedData['category'] as $category) {
                if (!DishCategoryService::checkIsCategoryExist($category)) {
                    return response(['message' => 'Invalid categories'], 400);
                }
            }
        }

        $query = Dish::query();

        $categories = $request->input('category', []);
        if ($categories) {
            foreach ($categories as $category) {
                $query->orWhere('category', $category);
            }
        }

        $query->where('isDeleted', false);

        if ($restaurantId) {
            $query->where('restaurant_id', $restaurantId);
        }
        else {
            $query->select('id', 'name', 'price', 'description', 'photo', 'isVegetarian', 'category', 'rating', 'isActive')
                ->selectRaw('restaurant_id AS restaurantId');
        }

        $isVegetarian = $request->input('vegetarian');
        if ($isVegetarian == 'true' || $isVegetarian == 1) {
            $query->where('isVegetarian', true);
        }

        $onlyActive = $request->input('onlyActive');
        if ($onlyActive == 'true' || $onlyActive == 1) {
            $query->where('isActive', true);
        }

        $currentPage = $request->input('page');
        $pageSize = 5;
        $allDishes = $query;
        $dishesCount = $allDishes->count();
        $pagesCount = ceil ($dishesCount / $pageSize);
        if ($currentPage) {
            $request->validate([
                'page' => 'nullable|integer|min:1|max:' . $pagesCount,
            ]);
        } else {
            $currentPage = 1;
        }

        $firstDishIndex = ($currentPage - 1) * $pageSize;
        $query->offset($firstDishIndex)->take($pageSize);

        $sorting = $request->input('sorting');
        if ($sorting) {
            switch ($sorting) {
                case 'NameDesc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'NameAsc':
                    $query->orderBy('name');
                    break;
                case 'PriceDesc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'PriceAsc':
                    $query->orderBy('price');
                    break;
                case 'RatingDesc':
                    $query->orderBy('rating', 'desc');
                    break;
                case 'RatingAsc':
                    $query->orderBy('rating');
                    break;
                default:
                    break;
            }
        }

        $dishes = $query->get();

        return [
            'dishes' => $dishes,
            'pagination' => [
                'size' => $pageSize,
                'count' => $pagesCount,
                'current' => (int)$currentPage
            ]
        ];

    }
}
