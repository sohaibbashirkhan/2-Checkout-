<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController; // Add this line to import the PaymentController

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Add the routes for payments
Route::get('/payments', [PaymentController::class, 'index'])->middleware('auth');
Route::post('/payments', [PaymentController::class, 'store'])->middleware('auth');
Route::get('/payment/{id}/pay', [PaymentController::class, 'pay'])->middleware('auth');
Route::post('/2checkout-webhook', [PaymentController::class, 'webhook']);
