<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomChargeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FlowerController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\RibbonController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\WrappingPaperController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
|
| Everything here requires an authenticated admin session. 'auth' confirms
| the visitor is logged in; 'admin' confirms the account is a staff
| account (see App\Http\Middleware\EnsureUserIsAdmin).
|
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', ProductController::class)->except(['show']);
    Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy'])
        ->name('products.images.destroy');
    Route::post('products/{product}/images/reorder', [ProductImageController::class, 'reorder'])
        ->name('products.images.reorder');

    Route::resource('categories', CategoryController::class)->except(['show']);

    Route::resource('flowers', FlowerController::class)->except(['show']);

    Route::resource('wrapping-papers', WrappingPaperController::class)->except(['show'])
        ->parameters(['wrapping-papers' => 'wrappingPaper']);

    Route::resource('ribbons', RibbonController::class)->except(['show']);

    Route::resource('custom-charges', CustomChargeController::class)->except(['show'])
        ->parameters(['custom-charges' => 'customCharge']);

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order:order_number}', [OrderController::class, 'show'])->name('show');
        Route::patch('/{order:order_number}/status', [OrderController::class, 'updateStatus'])->name('status');
        Route::get('/{order:order_number}/invoice', [OrderController::class, 'invoice'])->name('invoice');
        Route::post('/{order:order_number}/charges', [OrderController::class, 'attachCharge'])->name('charges.attach');
        Route::delete('/{order:order_number}/charges/{orderCharge}', [OrderController::class, 'detachCharge'])->name('charges.detach');
    });

    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/{contactMessage}', [MessageController::class, 'show'])->name('show');
        Route::delete('/{contactMessage}', [MessageController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'edit'])->name('edit');
        Route::put('/', [SettingController::class, 'update'])->name('update');
    });
});
