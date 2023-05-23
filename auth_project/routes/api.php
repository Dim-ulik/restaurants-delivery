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

Route::prefix('/user')->controller('App\Http\Controllers\AuthController')->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::middleware('auth.jwt')->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/profile', 'getProfile');
        Route::get('/roles', 'getRoles');
        Route::put('/password', 'changePassword');
        Route::put('/profile', 'putProfile');
        Route::get('/restaurant', 'getRestaurant');
    });
    Route::post('/refresh', 'refresh');
});

Route::fallback(function () {
    return response(["message" => "Undefined route"], 404);
});
