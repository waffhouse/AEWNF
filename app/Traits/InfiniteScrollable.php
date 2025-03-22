<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Trait InfiniteScrollable
 * 
 * Provides reusable functionality for implementing infinite scroll in Livewire components.
 * This trait extracts common pagination logic that can be applied to any model.
 */
trait InfiniteScrollable
{
    /**
     * Collection of loaded items
     *
     * @var array
     */
    public array $items = [];
    
    /**
     * Flag to indicate if there are more pages to load
     *
     * @var bool
     */
    public bool $hasMorePages = true;
    
    /**
     * Flag to indicate if a loading operation is in progress
     *
     * @var bool
     */
    public bool $isLoading = false;
    
    /**
     * Total count of all available items (for informational purposes)
     *
     * @var int
     */
    public int $totalCount = 0;
    
    /**
     * Count of currently loaded items
     *
     * @var int
     */
    public int $loadedCount = 0;
    
    /**
     * Number of items to load per page
     *
     * @var int
     */
    public int $itemsPerPage = 10;
    
    /**
     * Default sort column
     *
     * @var string
     */
    public string $sortField = 'created_at';
    
    /**
     * Default sort direction
     *
     * @var string
     */
    public string $sortDirection = 'desc';
    
    /**
     * Load items with cursor-based pagination for better performance
     *
     * @param Builder $query The base query to paginate
     * @param string $itemsProperty The component property to store items in (defaults to 'items')
     * @return void
     */
    public function loadItems(Builder $query, string $itemsProperty = 'items')
    {
        $this->isLoading = true;
        
        try {
            // Clone query to avoid modifying the original
            $countQuery = clone $query;
            
            // Use a paginator instead of manual offset/limit for better performance
            // We get the total count and items in a single operation
            $perPage = isset($this->perPage) ? $this->perPage : $this->itemsPerPage;
            $paginator = $query->orderBy($this->sortField, $this->sortDirection)
                              ->simplePaginate($perPage, ['*'], 'page', ceil($this->loadedCount / $perPage) + 1);
            
            // Only count total rows when needed (first page or when explicitly requested)
            // This avoids expensive COUNT(*) queries on large tables
            if ($this->loadedCount === 0 || property_exists($this, 'forceCount') && $this->forceCount) {
                $this->totalCount = $countQuery->count();
            }
            
            $newItems = $paginator->items();
            
            // Check if there are more pages directly from the paginator
            $this->hasMorePages = $paginator->hasMorePages();
            
            // Append new items to existing collection (only storing necessary data)
            foreach ($newItems as $item) {
                $this->{$itemsProperty}[] = $item;
            }
            
            // Update loaded count
            $this->loadedCount += count($newItems);
        } catch (\Exception $e) {
            Log::error('Error loading items: ' . $e->getMessage());
            // Fail gracefully in production
            if (!app()->environment('production')) {
                throw $e;
            }
        } finally {
            $this->isLoading = false;
        }
    }
    
    /**
     * Load more items when scrolling
     *
     * @param Builder $query The base query to paginate
     * @param string $itemsProperty The component property to store items in
     * @return void
     */
    public function loadMore(Builder $query, string $itemsProperty = 'items')
    {
        if ($this->hasMorePages && !$this->isLoading) {
            $this->loadItems($query, $itemsProperty);
        }
    }
    
    /**
     * Reset items when filters change
     *
     * @param Builder $query The base query to paginate
     * @param string $itemsProperty The component property to store items in
     * @return void
     */
    public function resetItems(Builder $query, string $itemsProperty = 'items')
    {
        $this->{$itemsProperty} = [];
        $this->loadedCount = 0;
        $this->hasMorePages = true;
        $this->loadItems($query, $itemsProperty);
    }
    
    /**
     * Set sort parameters and reset items
     *
     * @param string $field The field to sort by
     * @param Builder $query The base query to paginate
     * @param string $itemsProperty The component property to store items in
     * @return void
     */
    public function sortBy(string $field, Builder $query, string $itemsProperty = 'items')
    {
        // Toggle sort direction if clicking the same field
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        
        $this->resetItems($query, $itemsProperty);
    }
}