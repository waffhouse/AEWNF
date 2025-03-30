<?php

namespace App\ViewModels;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CatalogViewModel
{
    /**
     * Default category to display on initial catalog load.
     * Change this value to set a different default category.
     * Set to empty string to show all products by default.
     * 
     * @var string
     */
    public const DEFAULT_CATEGORY = '';
    /**
     * Get distinct brand options for filters
     * 
     * @return array
     */
    public function getBrands(): array
    {
        return Inventory::distinct()
            ->orderBy('brand')
            ->pluck('brand')
            ->filter()
            ->values()
            ->toArray();
    }
    
    /**
     * Get distinct class options for filters
     * 
     * @return array
     */
    public function getClasses(): array
    {
        return Inventory::distinct()
            ->orderBy('class')
            ->pluck('class')
            ->filter()
            ->values()
            ->toArray();
    }
    
    /**
     * Build a filtered query for inventory items
     * 
     * @param array $filters Filter parameters
     * @param array $options Additional query options
     * @return Builder
     */
    public function getFilteredQuery(array $filters = [], array $options = []): Builder
    {
        $query = Inventory::query();
        
        // Apply search if provided
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', $search)
                  ->orWhere('brand', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }
        
        // Filter by brand if selected
        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }
        
        // Filter by class if selected
        if (!empty($filters['class'])) {
            $query->where('class', $filters['class']);
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
        elseif (isset($filters['state']) && $filters['state'] !== 'all') {
            if ($filters['state'] === 'florida') {
                $query->where(function($q) {
                    $q->where('state', 'Florida')
                      ->orWhereNull('state')
                      ->orWhere('state', '');
                });
            } elseif ($filters['state'] === 'georgia') {
                $query->where(function($q) {
                    $q->where('state', 'Georgia')
                      ->orWhereNull('state')
                      ->orWhere('state', '');
                });
            }
        }
        
        // Apply sorting
        $orderBy = $options['orderBy'] ?? 'brand';
        $direction = $options['direction'] ?? 'asc';
        $query->orderBy($orderBy, $direction);
        
        return $query;
    }
    
    /**
     * Get paginated products for display
     * 
     * @param array $filters Filter parameters
     * @param int $offset Pagination offset
     * @param int $limit Items per page
     * @param array $options Additional query options
     * @return array Contains 'totalCount', 'hasMorePages', 'products'
     */
    public function getProducts(array $filters, int $offset, int $limit, array $options = []): array
    {
        $query = $this->getFilteredQuery($filters, $options);
        
        // Get total count for informational purposes
        $totalCount = $query->count();
        
        // Get products for current page
        $products = $query->offset($offset)
                          ->limit($limit + 1) // get one extra to check if there are more
                          ->get();
        
        // Check if there are more products
        $hasMorePages = $products->count() > $limit;
        
        // Remove the extra item we used to check for more
        if ($hasMorePages) {
            $products = $products->slice(0, $limit);
        }
        
        return [
            'totalCount' => $totalCount,
            'hasMorePages' => $hasMorePages,
            'products' => $products->toArray()
        ];
    }
    
    /**
     * Format product data for display
     * 
     * @param array $product
     * @return array
     */
    public function formatProduct(array $product): array
    {
        $state = $product['state'] ?? '';
        $isUnrestricted = empty($state);
        
        return [
            'id' => $product['id'],
            'sku' => $product['sku'],
            'brand' => $product['brand'],
            'class' => $product['class'] ?? '',
            'description' => $product['description'],
            'quantity' => $product['quantity'] ?? 0,
            'fl_price' => $product['fl_price'] ?? null, 
            'ga_price' => $product['ga_price'] ?? null,
            'bulk_price' => $product['bulk_price'] ?? null,
            'state' => $state,
            'isUnrestricted' => $isUnrestricted,
            'isAvailableInFlorida' => $isUnrestricted || $state === 'Florida',
            'isAvailableInGeorgia' => $isUnrestricted || $state === 'Georgia', 
        ];
    }
}