<?php

namespace App\Livewire\Inventory;

use Livewire\Component;
use Livewire\Attributes\Url;
use App\Traits\Filterable;
use App\ViewModels\CatalogViewModel;

class CatalogFilters extends Component
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
        $this->brands = $this->viewModel->getBrands();
        $this->classes = $this->viewModel->getClasses();
    }
    
    /**
     * Implementation of the abstract method from Filterable trait
     */
    public function resetItems(): void
    {
        // Dispatch a global event for all components to listen for
        $this->dispatch('global-filter-changed', [
            'search' => $this->search,
            'brand' => $this->brand,
            'class' => $this->class,
            'state' => $this->state
        ]);
        
        // Also send the filter-changed event for backward compatibility
        $this->dispatch('filter-changed', [
            'search' => $this->search,
            'brand' => $this->brand,
            'class' => $this->class,
            'state' => $this->state
        ]);
        
        // Also close any mobile filter modal using Alpine event
        $this->dispatch('closeFilterModal');
    }
    
    /**
     * When search changes, notify the parent component
     */
    public function updatedSearch()
    {
        $this->filtersApplied = true;
        $this->resetItems();
    }
    
    /**
     * When brand changes, notify the parent component
     */
    public function updatedBrand()
    {
        $this->filtersApplied = true;
        $this->resetItems();
    }
    
    /**
     * When class changes, notify the parent component
     */
    public function updatedClass()
    {
        $this->filtersApplied = true;
        $this->resetItems();
    }
    
    /**
     * When state changes, notify the parent component
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
     * This method is kept for backward compatibility but will always return false
     * since we no longer show the state filter to users
     */
    public function isStateFilterActive()
    {
        // Always return false since state filter is removed from UI
        return false;
    }
    
    /**
     * Override clearFilters method from Filterable trait
     */
    public function clearFilters(): void
    {
        // Reset each filter to its default value
        foreach ($this->filterConfig as $filter => $config) {
            $defaultValue = $config['default'] ?? '';
            $this->{$filter} = $defaultValue;
        }
        
        $this->filtersApplied = false;
        
        // This will call resetItems which will notify the parent
        $this->resetItems();
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.inventory.catalog-filters', [
            'brands' => $this->brands,
            'classes' => $this->classes,
        ]);
    }
}