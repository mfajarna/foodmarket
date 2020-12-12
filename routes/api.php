<?php

use App\Http\Controllers\API\foodController;
use App\Http\Controllers\API\transactionController;
use App\Http\Controllers\API\userController;
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

// Hanya bisa diakses apabila sudah login
Route::middleware('auth:sanctum')->group(function (){
    Route::get('user', [userController::class, 'fetch']);
    Route::post('user', [userController::class, 'updateProfile']);
    Route::post('user/photo', [userController::class, 'updatePhoto']);
    Route::post('logout', [userController::class, 'logout']);

    Route::get('transaction', [transactionController::class, 'all']);
    Route::post('transaction/{id}', [transactionController::class, 'update']);
});


// Bisa diakses walaupun tidak login
Route::post('login', [userController::class, 'login']);
Route::post('register', [userController::class, 'register']);

Route::post('food', [foodController::class, 'all']);
