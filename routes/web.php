<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\UserManagement;

// Redirect root to appropriate dashboard for authenticated users or catalog for guests
Route::get('/', function () {
    if (auth()->check()) {
        return app(App\Http\Controllers\AdminController::class)->redirectToDashboard();
    }
    
    // Show unified catalog for guests rather than login page
    return redirect()->route('inventory.catalog');
})->middleware('verify.age');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Admin routes - accessible to admin and staff users
Route::middleware(['auth', 'permission:access admin dashboard|view users|manage orders|view all orders'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard accessible to admin users and staff with appropriate permissions
    Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
});

// Staff routes deprecated - users should access management through admin dashboard
// Route group kept for backward compatibility with existing urls
Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
    // Redirect any old staff URLs to admin dashboard
    Route::get('/users', function() {
        return redirect()->route('admin.dashboard', ['#' => 'users']);
    });
});

// Customer routes - all authenticated users can access
Route::middleware(['auth'])->prefix('customer')->name('customer.')->group(function () {
    // Shopping cart routes - users with 'add to cart' permission
    Route::middleware(['permission:add to cart'])->group(function () {
        Route::get('/cart', \App\Livewire\Cart\CartPage::class)->name('cart');
    });
    
    // Order routes - users with 'view own orders' permission
    Route::middleware(['permission:view own orders'])->group(function () {
        Route::get('/orders', \App\Livewire\Cart\OrdersList::class)->name('orders');
    });
        
    // Order details functionality is now handled by modals within OrdersList and CartPage components
});

// NetSuite inventory API route - users with view catalog permission
Route::middleware(['auth', 'permission:view catalog'])->prefix('api')->name('api.')->group(function () {
    Route::get('/inventory', [\App\Http\Controllers\InventoryController::class, 'index'])->name('inventory');
});

// Cart API routes - for authorized users
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    Route::get('/check-cart', [\App\Http\Controllers\InventoryController::class, 'checkCart'])->name('check-cart');
});

// NetSuite inventory views are now handled through the admin dashboard

// Order Pick Ticket generation
Route::get('/orders/{id}/pick-ticket', [\App\Http\Controllers\OrderPickTicketController::class, 'generatePickTicket'])
    ->middleware(['auth', 'permission:manage orders'])
    ->name('orders.pick-ticket');

// Age verification routes
Route::middleware('guest')->group(function () {
    Route::get('/verify-age', [\App\Http\Controllers\AgeVerificationController::class, 'show'])
        ->name('verify.age');
    Route::post('/verify-age', [\App\Http\Controllers\AgeVerificationController::class, 'verify'])
        ->name('verify.age.submit');
});

// Unified Catalog - accessible to both guests and authenticated users
// The component will handle different layouts and permission-based content
Route::get('/catalog', \App\Livewire\Inventory\Catalog::class)
    ->middleware('verify.age')
    ->name('inventory.catalog');

require __DIR__.'/auth.php';