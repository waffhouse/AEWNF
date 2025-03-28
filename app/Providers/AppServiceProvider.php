<?php

namespace App\Providers;

use App\Services\CartService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the NetSuite config file
        $this->mergeConfigFrom(
            __DIR__.'/../../config/netsuite.php', 'netsuite'
        );
        
        // Register CartService as a singleton
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService();
        });
        
        // Register OrderService as a singleton
        $this->app->singleton(\App\Services\OrderService::class, function ($app) {
            return new \App\Services\OrderService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register all Livewire components manually to ensure proper namespacing
        \Livewire\Livewire::component('admin.dashboard', \App\Livewire\Admin\Dashboard::class);
        \Livewire\Livewire::component('admin.users.user-management', \App\Livewire\Admin\Users\UserManagement::class);
        \Livewire\Livewire::component('admin.roles.role-management', \App\Livewire\Admin\Roles\RoleManagement::class);
        \Livewire\Livewire::component('admin.permissions.permission-management', \App\Livewire\Admin\Permissions\PermissionManagement::class);
        \Livewire\Livewire::component('admin.inventory.inventory-sync', \App\Livewire\Admin\Inventory\InventorySync::class);
        \Livewire\Livewire::component('admin.orders.order-management', \App\Livewire\Admin\Orders\OrderManagement::class);
        
        // Cart components
        \Livewire\Livewire::component('cart.add-to-cart', \App\Livewire\Cart\AddToCart::class);
        \Livewire\Livewire::component('cart.cart-page', \App\Livewire\Cart\CartPage::class);
        \Livewire\Livewire::component('cart.cart-counter', \App\Livewire\Cart\CartCounter::class);
        
        // Modal components
        \Livewire\Livewire::component('modals.order-detail-modal', \App\Livewire\Modals\OrderDetailModal::class);
        \Livewire\Livewire::component('modals.transaction-detail-modal', \App\Livewire\Modals\TransactionDetailModal::class);
        
        // Set default timezone for Carbon
        Carbon::setToStringFormat('m/d/Y g:i A');

        // Create a Blade directive for formatting dates to EST
        Blade::directive('formatdate', function ($expression) {
            return "<?php echo \Carbon\Carbon::parse($expression)->format('m/d/Y g:i A'); ?>";
        });

        // Create a Blade directive for date only
        Blade::directive('formatdateonly', function ($expression) {
            return "<?php echo \Carbon\Carbon::parse($expression)->format('m/d/Y'); ?>";
        });
        
        // Create a Blade directive for time only
        Blade::directive('formattime', function ($expression) {
            return "<?php echo \Carbon\Carbon::parse($expression)->format('g:i A'); ?>";
        });
    }
}
