<?php

namespace App\Livewire\Inventory;

use App\Models\Inventory;
use App\Traits\Filterable;
use App\ViewModels\CatalogViewModel;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class Catalog extends Component
{
    use Filterable;
    
    #[Url]
    public $search = '';
    
    #[Url]
    public $brand = '';
    
    #[Url]
    public $class = '';
    
    #[Url]
    public $state = 'all';
    
    // For filter UI
    public $brands = [];
    public $classes = [];
    
    // View model instance
    protected CatalogViewModel $viewModel;
    
    /**
     * Initialize component with filter configurations
     */
    public function boot()
    {
        $this->viewModel = new CatalogViewModel();
        
        // Set up filter configuration
        $this->filterConfig = [
            'search' => [
                'type' => 'search',
                'fields' => ['sku', 'brand', 'description'],
                'default' => '',
            ],
            'brand' => [
                'type' => 'exact',
                'default' => '',
            ],
            'class' => [
                'type' => 'exact',
                'default' => '',
            ],
            'state' => [
                'type' => 'custom',
                'default' => 'all',
                'apply' => function($query, $value) {
                    // State filtering is handled in the view model
                    // This is just a placeholder for the filter configuration
                    return $query;
                }
            ],
        ];
    }
    
    /**
     * Initialize component
     */
    public function mount()
    {
        // Load filter options from view model
        $this->viewModel = new CatalogViewModel();
        $this->brands = $this->viewModel->getBrands();
        $this->classes = $this->viewModel->getClasses();
        
        // Set initial class filter to the default category if no filter is set
        // This ensures it only happens on the first page load
        // The default category can be changed in CatalogViewModel::DEFAULT_CATEGORY
        if (empty($this->class) && empty($this->search) && empty($this->brand)) {
            $this->class = CatalogViewModel::DEFAULT_CATEGORY;
            $this->filtersApplied = !empty($this->class);
        }
        
        // Load initial products
        $this->loadProducts();
    }
    
    /**
     * Reset items when filters change
     * 
     * Implementation of the abstract method from Filterable trait
     */
    public function resetItems(): void
    {
        // Reset product grid state
        $this->products = [];
        $this->loadedCount = 0;
        $this->hasMorePages = true;
        
        // Load products with the new filters
        $this->loadProducts();
    }
    
    /**
     * Handle the clear all filters event
     */
    #[On('clear-all-filters')]
    public function clearAllFilters()
    {
        $this->clearFilters();
        
        // Dispatch event to scroll to top
        $this->dispatch('scroll-to-top');
    }
    
    /**
     * We don't need to override clearFilters anymore because we're handling
     * the scroll behavior directly in the button click handlers.
     */
    
    /**
     * Handle removing a specific filter
     */
    #[On('remove-filter')]
    public function removeSpecificFilter($filter)
    {
        $this->removeFilter($filter);
        
        // Dispatch event to scroll to top
        $this->dispatch('scroll-to-top');
    }
    
    /**
     * We don't need to override removeFilter anymore because we're handling
     * the scroll behavior directly in the button click handlers.
     */
    
    /**
     * Listen for global filter changes from CatalogFilters component
     */
    #[On('global-filter-changed')]
    public function handleGlobalFilterChanged($data)
    {
        $this->search = $data['search'] ?? '';
        $this->brand = $data['brand'] ?? '';
        $this->class = $data['class'] ?? '';
        $this->state = $data['state'] ?? 'all';
        
        // Mark filters as applied
        $this->filtersApplied = !empty($this->search) || !empty($this->brand) || !empty($this->class) || $this->state !== 'all';
        
        // No need to call resetItems as it would create a loop of events
    }
    
    /**
     * When search changes, reset the product list
     */
    public function updatedSearch()
    {
        $this->filtersApplied = true;
        $this->resetItems();
        
        // Dispatch a browser event to collapse the filter area after search is entered
        $this->dispatch('collapse-filter-area');
        
        // Dispatch event to scroll to top
        $this->dispatch('scroll-to-top');
    }
    
    /**
     * When brand changes, reset the product list
     */
    public function updatedBrand()
    {
        $this->filtersApplied = true;
        $this->resetItems();
        
        // Collapse filter area after selection
        $this->dispatch('collapse-filter-area');
        
        // Dispatch event to scroll to top
        $this->dispatch('scroll-to-top');
    }
    
    /**
     * When class changes, reset the product list
     */
    public function updatedClass()
    {
        $this->filtersApplied = true;
        $this->resetItems();
        
        // Collapse filter area after selection
        $this->dispatch('collapse-filter-area');
        
        // Dispatch event to scroll to top
        $this->dispatch('scroll-to-top');
    }
    
    /**
     * When state changes, reset the product list
     */
    public function updatedState()
    {
        // Only set filtersApplied if state is not 'all'
        if ($this->state !== 'all') {
            $this->filtersApplied = true;
        } else {
            // When setting back to 'all', check if other filters are applied
            $this->filtersApplied = !empty($this->search) || !empty($this->brand) || !empty($this->class);
        }
        $this->resetItems();
        
        // Dispatch event to scroll to top
        $this->dispatch('scroll-to-top');
    }
    
    
    // Properties for product grid functionality
    public $products = [];
    public $hasMorePages = true;
    public $isLoading = false;
    public $totalCount = 0;
    public $loadedCount = 0;
    public $perPage = 25;
    public $viewMode = 'card';
    
    /**
     * Load products using the view model
     */
    public function loadProducts()
    {
        $this->isLoading = true;
        
        // Get filter parameters
        $filters = [
            'search' => $this->search,
            'brand' => $this->brand,
            'class' => $this->class,
            'state' => $this->state,
        ];
        
        // Get products from view model
        $result = $this->viewModel->getProducts(
            $filters, 
            $this->loadedCount, 
            $this->perPage, 
            ['orderBy' => 'brand', 'direction' => 'asc']
        );
        
        // Update component properties
        $this->totalCount = $result['totalCount'];
        $this->hasMorePages = $result['hasMorePages'];
        
        // Append new products to existing collection
        foreach ($result['products'] as $product) {
            $this->products[] = $product;
        }
        
        // Update loaded count
        $this->loadedCount += count($result['products']);
        $this->isLoading = false;
        
        // Dispatch event to notify product grid that products are loaded
        // This will trigger AddToCart components to refresh their state
        $this->dispatch('products-loaded');
    }
    
    /**
     * Load more products when scrolling (called from the view)
     */
    public function loadMore()
    {
        if ($this->hasMorePages && !$this->isLoading) {
            $this->loadProducts();
        }
    }
    
    /**
     * This method is kept for UI display but will always return false
     * since state filtering is managed through user roles
     */
    public function isStateFilterActive()
    {
        // Always return false since state filter is not shown in the UI
        return false;
    }
    
    /**
     * Handle search form submission (especially for mobile)
     * This prevents the keyboard from staying open when searching
     */
    public function submitSearch()
    {
        // This method triggers a refresh with the current search term
        // The search property is already updated through wire:model
        $this->filtersApplied = !empty($this->search);
        $this->resetItems();
        
        // Dispatch event to scroll to top
        $this->dispatch('scroll-to-top');
    }
    
    /**
     * Clear the user's shopping cart
     */
    #[On('clear-cart')]
    public function clearCart()
    {
        if (auth()->check()) {
            $user = auth()->user();
            $cart = $user->cart;
            
            if ($cart) {
                // Check if cart has any items before clearing
                $itemCount = $cart->items()->count();
                
                if ($itemCount === 0) {
                    $this->dispatch('notification', type: 'info', message: 'Your cart is already empty');
                    return;
                }
                
                // Delete all cart items
                $cart->items()->delete();
                
                // Dispatch events to update UI components
                $this->dispatch('cart-updated');
                $this->dispatch('cart-cleared');
                $this->dispatch('notification', type: 'warning', message: 'Your cart has been cleared (' . $itemCount . ' ' . ($itemCount === 1 ? 'item' : 'items') . ')');
            }
        }
    }

    /**
     * Check if the user's cart has items
     */
    public function hasCartItems()
    {
        if (auth()->check()) {
            $user = auth()->user();
            $cart = $user->cart;
            
            if ($cart) {
                return $cart->items()->count() > 0;
            }
        }
        
        return false;
    }
    
    /**
     * Listen for cart updates to refresh the Clear Cart button visibility
     */
    #[On('cart-updated')]
    #[On('cart-cleared')]
    public function refreshCartState()
    {
        // No action needed, the hasCartItems() will be called during the next render cycle
        // This listener just ensures the component gets refreshed
    }
    
    /**
     * Render the component
     */
    #[Title('Product Catalog')]
    public function render()
    {
        // Determine layout based on authentication status
        $layout = auth()->check() ? 'layouts.app' : 'layouts.guest-catalog';
        
        // Check if cart has items
        $hasCartItems = $this->hasCartItems();
        
        return view('livewire.inventory.catalog', [
            'brands' => $this->brands,
            'classes' => $this->classes,
            'hasCartItems' => $hasCartItems,
        ])->layout($layout);
    }
}