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

// Dashboard route with controller
Route::get('dashboard', function() {
    // Handle clear cart action
    if (request()->has('clear_cart') && auth()->check()) {
        $user = auth()->user();
        $cart = $user->cart;
        
        if ($cart) {
            $cart->items()->delete();
            session()->flash('message', 'Your cart has been cleared.');
        }
    }
    
    // Fetch featured brands (or fall back to popular brands if none are configured)
    $popularBrands = collect([]);
    $user = auth()->user();
    
    if ($user) {
        // Set up state restrictions for queries
        $stateCondition = null;
        if ($user->canViewFloridaItems() && !$user->canViewGeorgiaItems()) {
            $stateCondition = function($q) {
                $q->availableInFlorida();
            };
        } elseif ($user->canViewGeorgiaItems() && !$user->canViewFloridaItems()) {
            $stateCondition = function($q) {
                $q->availableInGeorgia();
            };
        }
        
        // Get featured brands from admin configuration
        $featuredBrandNames = \App\Models\FeaturedBrand::active()
            ->ordered()
            ->pluck('brand')
            ->toArray();
        
        // If no featured brands are configured, fall back to popular brands based on sales
        if (empty($featuredBrandNames)) {
            $featuredBrandNames = \App\Models\SaleItem::join('inventories', 'sale_items.sku', '=', 'inventories.sku')
                ->select('inventories.brand', \DB::raw('SUM(sale_items.quantity) as total_quantity'))
                ->whereNotNull('inventories.brand')
                ->where('inventories.brand', '!=', '')
                ->groupBy('inventories.brand')
                ->orderBy('total_quantity', 'desc')
                ->take(4)
                ->pluck('brand')
                ->toArray();
        }
        
        // For each brand, get a few products
        $brandProducts = [];
        foreach ($featuredBrandNames as $brand) {
            // Get products for this brand - show all products when expanded
            $initialVisible = 3; // Show this many in collapsed view
            
            // Build query for this brand
            $query = \App\Models\Inventory::where('brand', $brand)
                ->where(function($q) {
                    // Handle NULL quantities properly
                    $q->where('quantity', '>', 0)
                      ->orWhereNull('quantity'); // Include items with NULL quantity
                })
                ->orderBy('description');
            
            // Apply state filter based on user permissions
            if ($user->canViewFloridaItems() && !$user->canViewGeorgiaItems()) {
                $query->where(function($q) {
                    $q->where('state', '')
                      ->orWhereNull('state')
                      ->orWhere('state', 'Florida');
                });
            } elseif ($user->canViewGeorgiaItems() && !$user->canViewFloridaItems()) {
                $query->where(function($q) {
                    $q->where('state', '')
                      ->orWhereNull('state')
                      ->orWhere('state', 'Georgia');
                });
            }
            
            // Get all products for this brand without limit
            $products = $query->get();
                
            // Add a flag for initial visibility
            $products->each(function($product, $index) use ($initialVisible) {
                $product->initially_visible = ($index < $initialVisible);
            });
                
            if ($products->count() > 0) {
                $brandProducts[$brand] = $products;
            }
        }
        
        $popularBrands = collect($brandProducts);
    }
    
    // Get cart quantities for products
    $cartQuantities = [];
    $topItems = collect([]);
    
    if (auth()->check()) {
        $user = auth()->user();
        $cartService = app(\App\Services\CartService::class);
        $cartItems = $cartService->getCartItems();
        
        foreach ($cartItems as $inventoryId => $item) {
            $cartQuantities[$inventoryId] = $item['quantity'];
        }
        
        // Get user's top purchased items
        if ($user->customer_number) {
            $topItems = $user->getTopPurchasedItems(10); // Get top 10 items
        }
    }
    
    return view('dashboard', [
        'popularBrands' => $popularBrands,
        'cartQuantities' => $cartQuantities,
        'topItems' => $topItems
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

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
    
    // Sales data routes - redirect to main sales dashboard
    Route::get('/sales', function() {
        return redirect()->route('sales');
    })
        ->middleware('permission:view own orders');
        
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
    
// Sales Invoice generation - allows both admin and customer access
Route::get('/sales/{id}/invoice', [\App\Http\Controllers\SalesInvoiceController::class, 'generateInvoice'])
    ->middleware(['auth', 'permission:view netsuite sales data|view own orders'])
    ->name('sales.invoice');

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
    
// Unified Sales Dashboard - accessible to authenticated users with appropriate permissions
// The component will handle different views based on permissions
Route::get('/sales', \App\Livewire\Sales\SalesDashboard::class)
    ->middleware(['auth', 'permission:view netsuite sales data|view own orders'])
    ->name('sales');
    
// Sales Analytics Dashboard - accessible to users with sync permission
Route::get('/sales/analytics', \App\Livewire\Sales\SalesAnalyticsDashboard::class)
    ->middleware(['auth', 'permission:sync netsuite sales data'])
    ->name('sales.analytics');

require __DIR__.'/auth.php';