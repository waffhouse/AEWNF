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
    }
    
    /**
     * Reset items when filters change
     * 
     * Implementation of the abstract method from Filterable trait
     */
    public function resetItems(): void
    {
        $this->dispatch('filter-changed', [
            'search' => $this->search,
            'brand' => $this->brand,
            'class' => $this->class,
            'state' => $this->state
        ]);
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