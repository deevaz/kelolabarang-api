<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

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

// ADS
Route::get('/app-ads.txt', function () {
    $path = public_path('storage/app-ads.txt'); // Adjust this path if your app-ads.txt is not in public/storage

    if (File::exists($path)) {
        return Response::file($path, ['Content-Type' => 'text/plain']);
    }

    abort(404); // Or handle the case where the file doesn't exist as you see fit
});

// Auth Routes
Route::prefix('auth')->group(function () {  
    // Password Reset dengan Kode 6 Digit
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']); 
    Route::post('/resend-reset-code', [AuthController::class, 'resendResetCode']); 
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// ! LUPA PASSWORD
// Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/test-email', function() {
    Mail::raw('Hello, this is a test email!', function($message) {
        $message->to('demo@mailtrap.io')->subject('Test Email');
    });
    return 'Email sent!';
});


// ! CEK KESEHATAN
Route::get('/health', function () {
    return response()->json([
        'status' => 'UP',
        'message' => 'Server Kelola Barang berjalan normal'
    ], 200);
});

Route::middleware(['jwt.auth'])->group(function () {
    // ! ganti password
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // ! Get profit
    Route::get('/profit/{userId}', [ProductController::class, 'getProfit']);
    Route::get('/profit/{userId}/{startDate}/{endDate}', [ProductController::class, 'getProfitByDate']);

    // ! USER
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'userProfile']);
    Route::put('/user/edit/{id}', [AuthController::class, 'updateProfile']);
    Route::delete('/user/{id}', [AuthController::class, 'deleteAccount']);
    Route::get('/user/{id}', [AuthController::class, 'getUserById']);

    //! crud products
    Route::get('/products/{userId}', [ProductController::class, 'index']);
    Route::get('/products/{userId}/{category}', [ProductController::class, 'showByCategory']);
    Route::post('/products/{userId}', [ProductController::class, 'store']);
    Route::get('/products/{userId}/{id}', [ProductController::class, 'show']);
    Route::put('/products/{userId}/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{userId}/{id}', [ProductController::class, 'destroy']);

    //! crud suppliers
    Route::get('/suppliers/{userId}', [SuppliersController::class, 'index']);
    Route::post('/suppliers/{userId}', [SuppliersController::class, 'store']);
    Route::get('/suppliers/{userId}/{id}', [SuppliersController::class, 'show']);
    Route::put('/suppliers/{userId}/{id}', [SuppliersController::class, 'update']);
    Route::delete('/suppliers/{userId}/{id}', [SuppliersController::class, 'destroy']);

    //! crud business
    Route::get('/business/{userId}', [BusinessController::class, 'index']);
    Route::post('/business/{userId}', [BusinessController::class, 'store']);
    Route::get('/business/{userId}/{id}', [BusinessController::class, 'show']);
    Route::put('/business/{userId}/{id}', [BusinessController::class, 'update']);
    Route::delete('/business/{userId}/{id}', [BusinessController::class, 'destroy']);

    //! crud stock-in
    Route::get('/stockin/{userId}', [StockInController::class, 'index']);
    Route::post('/stockin/{userId}', [StockInController::class, 'store']);
    Route::get('/stockin/{userId}/{id}', [StockInController::class, 'show']);
    Route::put('/stockin/{userId}/{id}', [StockInController::class, 'update']);
    Route::delete('/stockin/{userId}/{id}', [StockInController::class, 'destroy']);
    Route::get('/stock-in/by-date-range/{userId}', [StockInController::class, 'getByDateRange']);

    //! crud stock-out
    Route::get('/stockout/{userId}', [StockOutController::class, 'index']);
    Route::post('/stockout/{userId}', [StockOutController::class, 'store']);
    Route::get('/stockout/{userId}/{id}', [StockOutController::class, 'show']);
    Route::put('/stockout/{userId}/{id}', [StockOutController::class, 'update']);
    Route::delete('/stockout/{userId}/{id}', [StockOutController::class, 'destroy']);

    Route::get('/stock-out/by-date-range/{userId}', [StockOutController::class, 'getByDateRange']);

    //! History
    Route::get('/history/{userId}', [HistoryController::class, 'getHistory']);
    Route::get('/history/by-date-range/{userId}', [HistoryController::class, 'getFilteredHistory']);

});
