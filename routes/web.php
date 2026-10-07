<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Storefront Routes
|--------------------------------------------------------------------------
|
| Guest checkout only — there is no customer-facing login. Admin auth
| (Breeze) lives under /admin, wired up in routes/admin.php.
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product:slug}', [ShopController::class, 'show'])->name('shop.show');

Route::get('/about', [PageController::class, 'about'])->name('pages.about');

Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [PageController::class, 'submitContact'])
    ->middleware('throttle:contact')
    ->name('pages.contact.submit');

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add/{product:slug}', [CartController::class, 'add'])->name('add');
    Route::patch('/{rowId}', [CartController::class, 'update'])->name('update');
    Route::delete('/{rowId}', [CartController::class, 'remove'])->name('remove');
});

Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/', [CheckoutController::class, 'store'])
        ->middleware('throttle:checkout')
        ->name('store');
});

Route::get('/order/confirmation/{order:order_number}', [OrderController::class, 'confirmation'])
    ->name('orders.confirmation');

Route::prefix('track-order')->name('orders.track.')->group(function () {
    Route::get('/', [OrderController::class, 'trackShow'])->name('show');
    Route::post('/', [OrderController::class, 'trackResult'])
        ->middleware('throttle:track-order')
        ->name('result');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

/*
|--------------------------------------------------------------------------
| Admin Auth + Panel
|--------------------------------------------------------------------------
*/

// Breeze's auth scaffolding, mounted at /admin/login, /admin/register
// (disabled — see routes/auth.php), /admin/forgot-password, etc. Route
// *names* (login, register, password.request, ...) stay unprefixed since
// Breeze's controllers/middleware reference them by name throughout.
Route::prefix('admin')->group(function () {
    require __DIR__.'/auth.php';
});

Route::middleware('auth')->prefix('admin')->name('profile.')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('destroy');
});

require __DIR__.'/admin.php';
