<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Home');
});

Route::get('/car-list', [AppController::class, 'getCar'])->name('car.index');

Route::post('/car-order', [RentalController::class, 'order'])->name('car.order');

Route::get('/car-order/{uuid}', [RentalController::class, 'showCarOrder'])->name('car.order.show');

Route::delete('/car-order-cancel/{uuid}', [RentalController::class, 'orderCancel'])->name('car.order.cancel');

Route::post('/payment/callback', [PaymentController::class, 'callback']);

Route::get('/admin/dashboard', [AppController::class, 'dashboard'])->name('admin.dashboard')->middleware('auth');

Route::prefix('admin')->name('admin.car.')->middleware('auth')->group(function () {
    Route::get('/cars', [CarController::class, 'index'])->name('index');
    Route::post('/cars', [CarController::class, 'store'])->name('store');
    Route::post('/cars/{id}', [CarController::class, 'update'])->name('update');
    Route::delete('/cars/{id}', [CarController::class, 'destroy'])->name('destroy');
});

Route::prefix('admin')->name('admin.customer.')->middleware('auth')->group(function () {
    Route::get('/customer', [CustomerController::class, 'index'])->name('index');
    Route::post('/customer', [CustomerController::class, 'store'])->name('store');
    Route::put('/customer/{id}', [CustomerController::class, 'update'])->name('update');
    Route::delete('/customer/{id}', [CustomerController::class, 'destroy'])->name('destroy');
});


Route::prefix('admin')->name('admin.transaction.')->middleware('auth')->group(function () {
    Route::get('/transaction', [TransactionController::class, 'index'])->name('index');
    Route::post('/transaction', [TransactionController::class, 'store'])->name('store');
    Route::put('/transaction/{uuid}', [TransactionController::class, 'update'])->name('update');
    Route::delete('/transaction/{uuid}', [TransactionController::class, 'destroy'])->name('destroy');
});

Route::prefix('admin')->name('admin.operator.')->middleware('auth')->group(function () {
    Route::get('/operator', [OperatorController::class, 'index'])->name('index');
    Route::post('/operator', [OperatorController::class, 'store'])->name('store');
    Route::put('/operator/{uuid}', [OperatorController::class, 'update'])->name('update');
    Route::delete('/operator/{uuid}', [OperatorController::class, 'destroy'])->name('destroy');
});


Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login')->middleware('guest');
    Route::post('/login', [AuthController::class, 'loginStore'])->name('loginStore')->middleware('guest');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
});
