<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Menu\MenuController;
use App\Http\Controllers\Menu\CartController;
use App\Http\Controllers\Menu\CheckoutController;

/*
|--------------------------------------------------------------------------
| Route Publik - Halaman Menu Pelanggan (via QR)
|--------------------------------------------------------------------------
*/

Route::middleware(['tenant'])->group(function () {

    // Halaman menu publik
    Route::get('/menu/{qr_code_string}', [MenuController::class, 'show'])->name('menu.show');

    // Cart
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{order_number}', [CheckoutController::class, 'success'])->name('checkout.success');
});

/*
|--------------------------------------------------------------------------
| Route Admin - Dashboard Kasir & Admin (Login Required)
|--------------------------------------------------------------------------
*/
Route::middleware(['tenant', 'auth'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Kategori
    Route::resource('categories', CategoryController::class);

    // Produk
    Route::resource('products', ProductController::class);

    // Meja
    Route::resource('tables', TableController::class);
});

// Auth routes (Breeze)
require __DIR__ . '/auth.php';
