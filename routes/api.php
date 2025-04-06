<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\BusinessController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'userProfile']);

    // crud products
    Route::get('/products/{userId}', [ProductController::class, 'index']);
    Route::post('/products/{userId}', [ProductController::class, 'store']);
    Route::get('/products/{userId}/{id}', [ProductController::class, 'show']);
    Route::put('/products/{userId}/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{userId}/{id}', [ProductController::class, 'destroy']);

    // crud suppliers
    Route::get('/suppliers/{userId}', [SuppliersController::class, 'index']);
    Route::post('/suppliers/{userId}', [SuppliersController::class, 'store']);
    Route::get('/suppliers/{userId}/{id}', [SuppliersController::class, 'show']);
    Route::put('/suppliers/{userId}/{id}', [SuppliersController::class, 'update']);
    Route::delete('/suppliers/{userId}/{id}', [SuppliersController::class, 'destroy']);

    // crud business
    Route::get('/business/{userId}', [BusinessController::class, 'index']);
    Route::post('/business/{userId}', [BusinessController::class, 'store']);
    Route::get('/business/{userId}/{id}', [BusinessController::class, 'show']);
    Route::put('/business/{userId}/{id}', [BusinessController::class, 'update']);
    Route::delete('/business/{userId}/{id}', [BusinessController::class, 'destroy']);
});
