<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::controller('App\Http\Controllers\AdminController')->group(function () {
    Route::get('/', 'showLoginForm')->name('show-login-form');
    Route::post('/admin/login', 'login')->name('login');
    Route::middleware('auth.check')->group(function () {
        Route::post('/admin/logout', 'logout')->name('logout');
        Route::get('/dashboard', 'showDashboard')->name('show-dashboard');
    });
});

Route::middleware('auth.check')->group(function () {
    Route::controller('App\Http\Controllers\RestaurantsController')->group(function () {
        Route::prefix('/restaurants')->group(function () {
            Route::get('', 'index')->name('index-restaurants');
            Route::get('/edit/restaurant/{restaurantId}', 'edit')->name('edit-restaurant');
            Route::put('/update/restaurant/{restaurantId}', 'update')->name('update-restaurant');
            Route::delete('/destroy/restaurant/{restaurantId}', 'destroy')->name('destroy-restaurant');
            Route::get('/create', 'create')->name('create-restaurant');
            Route::post('/store', 'store')->name('store-restaurant');
        });
    });
    Route::controller('App\Http\Controllers\UsersController')->group(function () {
        Route::prefix('/users')->group(function () {
            Route::get('', 'index')->name('index-users');
            Route::get('/edit/user/{userId}', 'edit')->name('edit-user');
            Route::put('/update/user/{userId}', 'update')->name('update-user');
            Route::delete('/destroy/user/{userId}', 'destroy')->name('destroy-user');
            Route::post('/create', 'create')->name('create-user');
            Route::get('/store', 'store')->name('store-user');
            Route::post('/ban/user/{userId}', 'ban')->name('ban-user');
            Route::post('/unban/user/{userId}', 'unban')->name('unban-user');
            Route::post('/setRoles/user/{userId}', 'setRoles')->name('set-roles-user');
        });
    });
    Route::controller('App\Http\Controllers\MenusController')->group(function () {
        Route::prefix('/menus')->group(function () {
            Route::get('', 'index')->name('index-menus');
            Route::get('/edit/menu/{menuId}', 'edit')->name('edit-menu');
            Route::put('/update/menu/{menuId}', 'update')->name('update-menu');
            Route::delete('/destroy/menu/{menuId}', 'destroy')->name('destroy-menu');
            Route::post('/create', 'create')->name('create-menu');
            Route::get('/store', 'store')->name('store-menu');
        });
    });
    Route::controller('App\Http\Controllers\CategoriesController')->group(function () {
        Route::prefix('/categories')->group(function () {
            Route::get('', 'index')->name('index-categories');
            Route::get('/edit/category/{categoryId}', 'edit')->name('edit-category');
            Route::put('/update/category/{categoryId}', 'update')->name('update-category');
            Route::delete('/destroy/category/{categoryId}', 'destroy')->name('destroy-category');
            Route::post('/create', 'create')->name('create-category');
            Route::get('/store', 'store')->name('store-category');
        });
    });
    Route::controller('App\Http\Controllers\DishesController')->group(function () {
        Route::prefix('/dishes')->group(function () {
            Route::get('', 'index')->name('index-dishes');
            Route::get('/edit/dish/{dishId}', 'edit')->name('edit-dish');
            Route::put('/update/dish/{dishId}', 'update')->name('update-dish');
            Route::delete('/destroy/dish/{dishId}', 'destroy')->name('destroy-dish');
            Route::post('/create', 'create')->name('create-dish');
            Route::get('/store', 'store')->name('store-dish');
            Route::delete('/deleteFromMenu/menu/{menuId}/dish/{dishId}', 'deleteFromMenu')->name('delete-dish-from-menu');
            Route::post('/addToMenu/dish/{dishId}', 'addToMenu')->name('add-dish-to-menu');
        });
    });
});


