<?php

namespace App\Livewire\Inventory;

use App\Models\Inventory;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

class Catalog extends Component
{
    // For infinite scroll
    public $products = [];
    public $hasMorePages = true;
    public $isLoading = false;
    public $totalCount = 0;
    public $loadedCount = 0;
    
    #[Url]
    public $search = '';
    
    #[Url]
    public $brand = '';
    
    #[Url]
    public $class = '';
    
    #[Url]
    public $state = 'all';
    
    #[Url]
    public $perPage = 25;
    
    // Always use card view
    public $viewMode = 'card';
    
    // For filter UI
    public $brands = [];
    public $classes = [];
    
    // Flag to determine if filters have been applied
    public $filtersApplied = false;
    
    public function mount()
    {
        // Load filter options
        $this->brands = Inventory::distinct()->orderBy('brand')->pluck('brand')->filter()->values()->toArray();
        $this->classes = Inventory::distinct()->orderBy('class')->pluck('class')->filter()->values()->toArray();
        
        // We don't need to force a specific state filter,
        // as the query will automatically apply the state restriction
        // based on the user's state setting
        
        // Load initial products
        $this->loadProducts();
    }
    
    public function loadProducts()
    {
        $this->isLoading = true;
        
        $query = $this->getFilteredQuery();
        
        // Get total count for informational purposes
        $this->totalCount = $query->count();
        
        // Get products for current page
        $newProducts = $query->offset($this->loadedCount)
                             ->limit($this->perPage + 1) // get one extra to check if there are more
                             ->get();
        
        // Check if there are more products
        $this->hasMorePages = $newProducts->count() > $this->perPage;
        
        // Remove the extra item we used to check for more
        if ($this->hasMorePages) {
            $newProducts = $newProducts->slice(0, $this->perPage);
        }
        
        // Append new products to existing collection
        foreach ($newProducts as $product) {
            $this->products[] = $product->toArray();
        }
        
        // Update loaded count
        $this->loadedCount += $newProducts->count();
        
        $this->isLoading = false;
    }
    
    public function loadMore()
    {
        if ($this->hasMorePages && !$this->isLoading) {
            $this->loadProducts();
        }
    }
    
    protected function getFilteredQuery()
    {
        $query = Inventory::query();
        
        // Apply search if provided
        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', $search)
                  ->orWhere('brand', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }
        
        // Filter by brand if selected
        if (!empty($this->brand)) {
            $query->where('brand', $this->brand);
        }
        
        // Filter by class if selected
        if (!empty($this->class)) {
            $query->where('class', $this->class);
        }
        
        // Apply state filtering based on permissions for authenticated users
        // or based on selected state for guests
        if (auth()->check()) {
            $user = auth()->user();
            $showFlorida = $user->canViewFloridaItems();
            $showGeorgia = $user->canViewGeorgiaItems();
            $showUnrestricted = $user->canViewUnrestrictedItems();
            
            // Build dynamic query based on permissions
            $query->where(function($q) use ($showFlorida, $showGeorgia, $showUnrestricted) {
                // Show unrestricted items if allowed
                if ($showUnrestricted) {
                    $q->orWhereNull('state')
                      ->orWhere('state', '');
                }
                
                // Show Florida items if allowed
                if ($showFlorida) {
                    $q->orWhere('state', 'Florida');
                }
                
                // Show Georgia items if allowed
                if ($showGeorgia) {
                    $q->orWhere('state', 'Georgia');
                }
            });
        } 
        // For guests, apply the user's selected state filter
        elseif ($this->state !== 'all') {
            if ($this->state === 'florida') {
                $query->where(function($q) {
                    $q->where('state', 'Florida')
                      ->orWhereNull('state')
                      ->orWhere('state', '');
                });
            } elseif ($this->state === 'georgia') {
                $query->where(function($q) {
                    $q->where('state', 'Georgia')
                      ->orWhereNull('state')
                      ->orWhere('state', '');
                });
            }
        }
        
        // Default sorting by brand
        $query->orderBy('brand', 'asc');
        
        return $query;
    }
    
    // Modified to directly mark filters as applied (not needed for individual handlers below)
    public function applyFilters()
    {
        $this->filtersApplied = true;
        $this->resetProducts();
    }
    
    // For both automatic live updates and manual form submission
    public function updatedSearch()
    {
        $this->filtersApplied = true;
        $this->resetProducts();
        $this->dispatch('scrollToTop');
    }
    
    public function updatedBrand()
    {
        $this->filtersApplied = true;
        $this->resetProducts();
        $this->dispatch('scrollToTop');
    }
    
    public function updatedClass()
    {
        $this->filtersApplied = true;
        $this->resetProducts();
        $this->dispatch('scrollToTop');
    }
    
    public function updatedState()
    {
        // Only set filtersApplied if state is not 'all'
        if ($this->state !== 'all') {
            $this->filtersApplied = true;
        } else {
            // When setting back to 'all', check if other filters are applied
            $this->filtersApplied = !empty($this->search) || !empty($this->brand) || !empty($this->class);
        }
        $this->resetProducts();
        $this->dispatch('scrollToTop');
    }
    
    public function updatedPerPage()
    {
        $this->filtersApplied = true;
        $this->resetProducts();
        $this->dispatch('scrollToTop');
    }
    
    public function resetProducts()
    {
        $this->products = [];
        $this->loadedCount = 0;
        $this->hasMorePages = true;
        $this->loadProducts();
    }
    
    public function clearFilters()
    {
        $this->search = '';
        $this->brand = '';
        $this->class = '';
        $this->state = 'all';
        $this->perPage = 25;
        $this->filtersApplied = false;
        $this->resetProducts();
        $this->dispatch('scrollToTop');
    }
    
    public function removeFilter($type)
    {
        switch ($type) {
            case 'search':
                $this->search = '';
                break;
            case 'brand':
                $this->brand = '';
                break;
            case 'class':
                $this->class = '';
                break;
            case 'state':
                $this->state = 'all';
                break;
        }
        
        // Check if any filters are still applied
        $this->filtersApplied = !empty($this->search) || !empty($this->brand) || !empty($this->class) || $this->state !== 'all';
        
        // If we have no filters applied at all, reset the filtersApplied flag
        if (empty($this->search) && empty($this->brand) && empty($this->class) && $this->state === 'all') {
            $this->filtersApplied = false;
        }
        
        $this->resetProducts();
        $this->dispatch('scrollToTop');
    }
    
    // This method is kept for backward compatibility but will always return false
    // since we no longer show the state filter to users
    public function isStateFilterActive()
    {
        // Always return false since state filter is removed from UI
        return false;
    }
    
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