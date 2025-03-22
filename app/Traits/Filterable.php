<?php

namespace App\Traits;

/**
 * Trait Filterable
 * 
 * Provides standardized filtering functionality for Livewire components
 * that deal with filter-based data retrieval. Centralizes filter management,
 * application, and clearing patterns.
 */
trait Filterable
{
    /**
     * Flag to determine if filters have been applied
     *
     * @var bool
     */
    public $filtersApplied = false;
    
    /**
     * Available filters configuration
     * 
     * This should be set in the component
     * 
     * @var array
     */
    protected $filterConfig = [];
    
    /**
     * Reset filters to their default values
     * 
     * @return void
     */
    public function clearFilters(): void
    {
        foreach ($this->filterConfig as $filter => $config) {
            $defaultValue = $config['default'] ?? '';
            $this->{$filter} = $defaultValue;
        }
        
        $this->filtersApplied = false;
        $this->resetItems();
    }
    
    /**
     * Apply all filters and refresh items
     * 
     * @return void
     */
    public function applyFilters(): void
    {
        $this->filtersApplied = $this->hasActiveFilters();
        $this->resetItems();
    }
    
    /**
     * Remove a specific filter
     * 
     * @param string $filter Filter name to remove
     * @return void
     */
    public function removeFilter(string $filter): void
    {
        if (isset($this->filterConfig[$filter])) {
            $defaultValue = $this->filterConfig[$filter]['default'] ?? '';
            $this->{$filter} = $defaultValue;
        }
        
        $this->filtersApplied = $this->hasActiveFilters();
        $this->resetItems();
    }
    
    /**
     * Check if a specific filter is active
     * 
     * @param string $filter Filter name to check
     * @return bool
     */
    public function isFilterActive(string $filter): bool
    {
        if (!isset($this->filterConfig[$filter])) {
            return false;
        }
        
        $config = $this->filterConfig[$filter];
        $defaultValue = $config['default'] ?? '';
        $currentValue = $this->{$filter};
        
        if (is_array($currentValue)) {
            return !empty(array_filter($currentValue));
        }
        
        return $currentValue !== $defaultValue && $currentValue !== '' && $currentValue !== null;
    }
    
    /**
     * Check if any filters are active
     * 
     * @return bool
     */
    public function hasActiveFilters(): bool
    {
        foreach (array_keys($this->filterConfig) as $filter) {
            if ($this->isFilterActive($filter)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Register a filter listener for a specific filter
     * 
     * Call this in the component's boot method for each filter
     * that should trigger a resetItems action when updated.
     * 
     * @param string $filter Filter property name
     * @return void
     */
    protected function registerFilterListener(string $filter): void
    {
        $this->{'updating' . ucfirst($filter)} = function() use ($filter) {
            $this->filtersApplied = true;
        };
        
        $this->{'updated' . ucfirst($filter)} = function() {
            $this->resetItems();
        };
    }
    
    /**
     * Apply filters to a query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyFiltersToQuery($query)
    {
        foreach ($this->filterConfig as $filter => $config) {
            $value = $this->{$filter};
            
            // Skip empty values
            if ($value === '' || $value === null || (is_array($value) && empty(array_filter($value)))) {
                continue;
            }
            
            // Apply filter based on type
            $type = $config['type'] ?? 'exact';
            $field = $config['field'] ?? $filter;
            
            switch ($type) {
                case 'search':
                    $fields = $config['fields'] ?? [$field];
                    $this->applySearchFilter($query, $value, $fields);
                    break;
                    
                case 'exact':
                    $query->where($field, $value);
                    break;
                    
                case 'in':
                    if (is_array($value)) {
                        $query->whereIn($field, $value);
                    }
                    break;
                    
                case 'date':
                    if (isset($config['operator'])) {
                        $query->where($field, $config['operator'], $value);
                    } else {
                        $query->whereDate($field, $value);
                    }
                    break;
                    
                case 'custom':
                    if (isset($config['apply']) && is_callable($config['apply'])) {
                        $callback = $config['apply'];
                        $callback($query, $value);
                    }
                    break;
            }
        }
        
        return $query;
    }
    
    /**
     * Apply search filter to a query (for text search across multiple fields)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $value
     * @param array $fields
     * @return void
     */
    protected function applySearchFilter($query, string $value, array $fields): void
    {
        if (empty($value) || empty($fields)) {
            return;
        }
        
        $query->where(function($q) use ($value, $fields) {
            $search = '%' . $value . '%';
            
            foreach ($fields as $field) {
                $q->orWhere($field, 'like', $search);
            }
        });
    }
    
    /**
     * Reset items to be implemented by the component
     * 
     * @return void
     */
    abstract public function resetItems(): void;
}