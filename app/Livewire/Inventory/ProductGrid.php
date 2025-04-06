<?php

namespace App\Livewire\Inventory;

use App\ViewModels\CatalogViewModel;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class ProductGrid extends Component
{
    // For infinite scroll
    public $products = [];

    public $hasMorePages = true;

    public $isLoading = false;

    public $totalCount = 0;

    public $loadedCount = 0;

    // Access to filter properties
    public $search = '';

    public $brand = '';

    public $class = '';

    public $state = 'all';

    #[Url]
    public $perPage = 25;

    // Always use card view
    public $viewMode = 'card';

    // View model instance
    protected CatalogViewModel $viewModel;

    /**
     * Initialize component
     */
    public function boot()
    {
        $this->viewModel = new CatalogViewModel;
    }

    /**
     * Initialize component with filter values
     */
    public function mount($search = '', $brand = '', $class = '', $state = 'all')
    {
        // Set filter values from parent component
        $this->search = $search;
        $this->brand = $brand;
        $this->class = $class;
        $this->state = $state;

        // Load initial products
        $this->loadProducts();

        return $this;
    }

    /**
     * Get products for display (used by Catalog component)
     */
    public function getProducts()
    {
        return [
            'products' => $this->products,
            'hasMorePages' => $this->hasMorePages,
            'totalCount' => $this->totalCount,
            'loadedCount' => $this->loadedCount,
        ];
    }

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
     * Load more products when scrolling
     */
    public function loadMore()
    {
        if ($this->hasMorePages && ! $this->isLoading) {
            $this->loadProducts();

            // Force refresh the Livewire components for proper initialization
            $this->dispatch('products-loaded');
        }
    }

    /**
     * Reset products when filters change
     */
    public function resetProducts()
    {
        $this->products = [];
        $this->loadedCount = 0;
        $this->hasMorePages = true;
        $this->loadProducts();
    }

    /**
     * Update filter values and reload products
     */
    #[On('filter-changed')]
    public function updateFilters($data)
    {
        $this->search = $data['search'] ?? '';
        $this->brand = $data['brand'] ?? '';
        $this->class = $data['class'] ?? '';
        $this->state = $data['state'] ?? 'all';
        $this->resetProducts();
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.inventory.product-grid');
    }
}
