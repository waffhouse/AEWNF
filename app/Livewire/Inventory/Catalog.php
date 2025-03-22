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
    }
    
    /**
     * Handle removing a specific filter
     */
    #[On('remove-filter')]
    public function removeSpecificFilter($filter)
    {
        $this->removeFilter($filter);
    }
    
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
    }
    
    /**
     * When brand changes, reset the product list
     */
    public function updatedBrand()
    {
        $this->filtersApplied = true;
        $this->resetItems();
    }
    
    /**
     * When class changes, reset the product list
     */
    public function updatedClass()
    {
        $this->filtersApplied = true;
        $this->resetItems();
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
     * Render the component
     */
    #[Title('Product Catalog')]
    public function render()
    {
        // Determine layout based on authentication status
        $layout = auth()->check() ? 'layouts.app' : 'layouts.guest-catalog';
        
        return view('livewire.inventory.catalog', [
            'brands' => $this->brands,
            'classes' => $this->classes,
        ])->layout($layout);
    }
}