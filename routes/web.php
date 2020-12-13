<?php

use App\Http\Controllers\API\midtransController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\foodController;
use App\Http\Controllers\userController;
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



// homepage
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// dashboard
Route::prefix('dashboard')
    ->middleware(['auth:sanctum','admin'])
    ->group(function (){
        Route::get('/', [dashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', userController::class);
        Route::resource('food', foodController::class);
    });



// Midtrans related

Route::get('midtrans/success', [midtransController::class, 'success']);
Route::get('midtrans/unfinish', [midtransController::class, 'unfinish']);
Route::get('midtrans/error', [midtransController::class, 'error']);