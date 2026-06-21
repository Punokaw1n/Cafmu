<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Menu\MenuController;
use App\Http\Controllers\Menu\CartController;
use App\Http\Controllers\Menu\CheckoutController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Admin\ReportController;

// Webhook Midtrans (public, tanpa middleware)
Route::post('/webhook/midtrans', [PaymentController::class, 'handleWebhook'])->name('webhook.midtrans');

// Fallback Dashboard redirect to Admin Dashboard
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['tenant', 'auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Route Publik - Halaman Menu Pelanggan (via QR)
|--------------------------------------------------------------------------
*/
Route::middleware(['tenant'])->group(function () {
    Route::get('/menu/{qr_code_string}', [MenuController::class, 'show'])->name('menu.show');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{order_number}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::post('/payment/generate-link/{order}', [PaymentController::class, 'generatePaymentLink'])->name('payment.generate-link');
});

/*
|--------------------------------------------------------------------------
| Route Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['tenant', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('tables', TableController::class);
    Route::patch('tables/{table}/status', [TableController::class, 'updateStatus'])->name('tables.updateStatus');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/transactions', [ReportController::class, 'transactions'])->name('reports.transactions');
});

require __DIR__ . '/auth.php';
