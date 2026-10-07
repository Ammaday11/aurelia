<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Policies\CategoryPolicy;
use App\Policies\ContactMessagePolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Category::class => CategoryPolicy::class,
        Order::class => OrderPolicy::class,
        ContactMessage::class => ContactMessagePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
