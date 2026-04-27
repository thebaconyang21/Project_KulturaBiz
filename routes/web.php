<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\DashboardController as AdminController;
use App\Http\Controllers\Artisan\ProductController as ArtisanController;


Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/products', [HomeController::class, 'products'])->name('products.index');
Route::get('/products/{slug}', [HomeController::class, 'productShow'])->name('products.show');

Route::get('/cultural-stories', [HomeController::class, 'culturalStories'])->name('cultural.index');
Route::get('/cultural-stories/{slug}', [HomeController::class, 'culturalStoryShow'])->name('cultural.show');


Route::get('/artisans/{id}', [HomeController::class, 'artisanProfile'])->name('artisan.profile');


Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');


Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{productId}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{productId}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
});


Route::middleware('auth')->group(function () {
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/place', [OrderController::class, 'placeOrder'])->name('orders.place');
    Route::get('/orders/confirmation/{orderId}', [OrderController::class, 'confirmation'])->name('orders.confirmation');
    Route::get('/orders/my-orders', [OrderController::class, 'myOrders'])->name('orders.mine');
    Route::get('/orders/track/{orderId}', [OrderController::class, 'track'])->name('orders.track');
    Route::post('/orders/{orderId}/review/{productId}', [OrderController::class, 'review'])->name('orders.review');
});



Route::middleware('auth')->group(function () {
    // Simulated payment checkout page (mirrors PayMongo hosted page)
    Route::get('/payments/simulate/{order}', [\App\Http\Controllers\PaymentController::class, 'simulatePage'])
        ->name('payments.simulate');

    // Process the simulated payment (user clicks Pay Now)
    Route::post('/payments/process/{order}', [\App\Http\Controllers\PaymentController::class, 'processSimulated'])
        ->name('payments.process');

    // PayMongo callback after real GCash/Maya redirect
    Route::get('/payments/callback/{order}', [\App\Http\Controllers\PaymentController::class, 'callback'])
        ->name('payments.callback');
});

// PayMongo webhook (no auth — signed by PayMongo)
Route::post('/webhooks/paymongo', [\App\Http\Controllers\PaymentController::class, 'webhook'])
    ->name('webhooks.paymongo');


Route::middleware(['auth', 'artisan'])->prefix('artisan')->name('artisan.')->group(function () {
    Route::get('/dashboard', [ArtisanController::class, 'dashboard'])->name('dashboard');

    // FIX: /products/create MUST be declared before /products/{id} wildcard
    Route::get('/products', [ArtisanController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ArtisanController::class, 'create'])->name('products.create');
    Route::post('/products', [ArtisanController::class, 'store'])->name('products.store');
    Route::get('/products/{id}/edit', [ArtisanController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [ArtisanController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ArtisanController::class, 'destroy'])->name('products.destroy');

    Route::get('/orders', [ArtisanController::class, 'orders'])->name('orders');
    Route::patch('/orders/{orderId}/status', [ArtisanController::class, 'updateOrderStatus'])->name('orders.status');
});


Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::patch('/users/{id}/approve', [AdminController::class, 'approveArtisan'])->name('users.approve');
    Route::patch('/users/{id}/reject', [AdminController::class, 'rejectArtisan'])->name('users.reject');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');

    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::delete('/products/{id}', [AdminController::class, 'deleteProduct'])->name('products.delete');

    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory'])->name('categories.delete');

    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::patch('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.status');
});