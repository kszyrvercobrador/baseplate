<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\ApiControllers\Auth\UserController;
use App\Http\ApiControllers\Auth\LoginController;
use App\Http\ApiControllers\Auth\LogoutController;

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

Route::post('/auth/login', LoginController::class);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/auth/user', UserController::class);
    Route::post('/auth/logout', LogoutController::class);
});
