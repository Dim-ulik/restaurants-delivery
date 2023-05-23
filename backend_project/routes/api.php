<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::prefix('/restaurant')->controller('App\Http\Controllers\RestaurantController')->group(function () {
    Route::get('', 'getRestaurantsList');
    Route::get('/{restaurantId}/menu', 'getMenusList')->middleware('validate.restaurant');
    Route::get('/{restaurantId}/dish', 'getDishesList')->middleware('validate.restaurant');
    Route::middleware('auth.jwt')->group(function () {
        Route::get('/{restaurantId}/order', 'getOrdersList')->middleware(['validate.restaurant', 'check.role.administration']);
        Route::get('/cook/my/order', 'getCooksOrdersList')->middleware('check.role.cook');
        Route::post('/cook/my/order/{orderId}', 'assignOrderToCook')->middleware(['validate.order', 'check.role.cook']);
        Route::delete('/cook/my/order/{orderId}', 'cancelCookOrder')->middleware(['validate.order', 'check.role.cook']);
    });
});

Route::prefix('/menu')->controller('App\Http\Controllers\MenuController')->group(function () {
    Route::get('/{menuId}', 'getDishesList')->middleware('validate.menu');
    Route::middleware(['auth.jwt', 'check.role.manager'])->group(function () {
        Route::post('/restaurant/{restaurantId}', 'createMenu')->middleware('validate.restaurant');
        Route::delete('/{menuId}', 'deleteMenu')->middleware('validate.menu');
        Route::put('/{menuId}', 'changeMenuName')->middleware('validate.menu');
        Route::post('/{menuId}/dish/{dishId}', 'addDishToMenu')->middleware(['validate.menu', 'validate.dish']);
        Route::delete('/{menuId}/dish/{dishId}', 'removeDishFromMenu')->middleware(['validate.menu', 'validate.dish']);
    });
});

Route::prefix('/dish')->controller('App\Http\Controllers\DishController')->group(function () {
    Route::get('', 'getDishesList');
    Route::get('/categories', 'getDishesCategories');
    Route::get('/{dishId}', 'getDish')->middleware('validate.dish');
    Route::post('/savePhoto', 'getPhotoFromAdmin');
    Route::middleware('auth.jwt')->group(function () {
        Route::get('/{dishId}/rating/check', 'checkCanUserRate')->middleware('validate.dish');
        Route::post('/{dishId}/rating', 'rateDish')->middleware('validate.dish');
        Route::delete('/{dishId}/rating', 'deleteDishRating')->middleware('validate.dish');
        Route::middleware('check.role.manager')->group(function () {
            Route::post('/restaurant/{restaurantId}', 'createDish')->middleware();
            Route::post('/{dishId}', 'updateDish')->middleware('validate.dish');
            Route::delete('/{dishId}', 'deleteDish')->middleware('validate.dish');
            Route::put('/{dishId}/status', 'changeDishActivityStatus')->middleware('validate.dish');
        });
    });
});

Route::prefix('/basket')->controller('App\Http\Controllers\BasketController')->middleware('auth.jwt')->group(function () {
    Route::post('/dish/{dishId}', 'addDishToBasket')->middleware('validate.dish');
    Route::get('', 'getBasket');
    Route::delete('/dish/{dishId}', 'removeDishFromBasket')->middleware('validate.dish');
    Route::delete('', 'emptyBasket');
    Route::put('/dish/{dishId}/amount', 'changeDishAmount')->middleware('validate.dish');
    Route::delete('/inactive', 'removeInactiveDishes');
});

Route::prefix('/order')->controller('App\Http\Controllers\OrderController')->middleware('auth.jwt')->group(function () {
    Route::post('', 'createOrder');
    Route::post('/{orderId}/repeat', 'repeatOrder')->middleware('validate.order');
    Route::put('/{orderId}/status', 'changeOrderStatus')->middleware('validate.order');
    Route::get('', 'getOrdersList');
    Route::get('/{orderId}', 'getOrder')->middleware('validate.order');
});

Route::prefix('/courier')->controller('App\Http\Controllers\CourierController')->middleware(['auth.jwt', 'check.role.courier'])->group(function () {
    Route::get('/order', 'getAvailableOrders');
    Route::get('/my/order', 'getCurrentOrders');
    Route::post('/order/{orderId}', 'acceptOrder')->middleware('validate.order');
    Route::delete('/order/{orderId}', 'cancelOrder')->middleware('validate.order');
});

Route::fallback(function () {
    return response(["message" => "Undefined route"], 404);
});
