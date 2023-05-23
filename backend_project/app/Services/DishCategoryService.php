<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;

class DishCategoryService
{
    function checkIsCategoryExist($category)
    {
        return DB::table('categories')->where('category', $category)->exists();
    }
}
