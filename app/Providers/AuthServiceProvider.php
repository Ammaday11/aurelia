<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\CustomCharge;
use App\Models\Flower;
use App\Models\Order;
use App\Models\Product;
use App\Models\Ribbon;
use App\Models\User;
use App\Models\WrappingPaper;
use App\Policies\CategoryPolicy;
use App\Policies\ContactMessagePolicy;
use App\Policies\CustomChargePolicy;
use App\Policies\FlowerPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RibbonPolicy;
use App\Policies\UserPolicy;
use App\Policies\WrappingPaperPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Flower::class => FlowerPolicy::class,
        WrappingPaper::class => WrappingPaperPolicy::class,
        Ribbon::class => RibbonPolicy::class,
        CustomCharge::class => CustomChargePolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Backups aren't an Eloquent model (see App\Services\BackupFileInfo's
        // docblock for why), so there's no model to hang a policy off —
        // Admin\BackupController authorizes every action through this gate
        // instead.
        Gate::define('manage-backups', fn ($user) => $user->isAdmin());
    }
}
