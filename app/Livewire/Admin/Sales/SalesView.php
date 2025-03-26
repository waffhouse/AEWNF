<?php

namespace App\Livewire\Admin\Sales;

use App\Models\Sale;
use App\Traits\AdminAuthorization;
use App\Traits\Filterable;
use App\Traits\InfiniteScrollable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

class SalesView extends Component
{
    use AdminAuthorization, Filterable, InfiniteScrollable;
    
    public $search = '';
    public $filters = [
        'type' => '',
        'customer' => '',
        'date_range' => [
            'start' => '',
            'end' => ''
        ],
    ];
    
    public $viewingSale = null;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'date'],
        'sortDirection' => ['except' => 'desc'],
        'filters' => ['except' => [
            'type' => '',
            'customer' => '',
            'date_range' => [
                'start' => '',
                'end' => ''
            ],
        ]],
    ];
    
    public function mount()
    {
        // Ensure user has admin permissions
        $this->authorize('view netsuite sales data');
        
        // Set default sort field and direction
        $this->sortField = 'date';
        $this->sortDirection = 'desc';
        
        // Initialize items array
        $this->items = [];
        $this->itemsPerPage = 20;
        
        // Load initial items
        $this->loadMore($this->getBaseQuery());
    }
    
    #[Title('All Sales History')]
    public function render()
    {
        $this->totalCount = $this->getTotal();
        
        return view('livewire.admin.sales.sales-view', [
            'sales' => $this->items,
            'transactionTypes' => $this->getTransactionTypes(),
            'customers' => $this->getUniqueCustomers(),
            'summary' => $this->getSummaryData(),
        ])->layout('layouts.app');
    }
    
    /**
     * Get the base query for sales with all filters applied
     */
    public function getBaseQuery()
    {
        return Sale::query()
            ->select('id', 'tran_id', 'type', 'date', 'entity_id', 'customer_name', 'total_amount', 'created_at')
            ->with(['items' => function($query) {
                $query->select('id', 'sale_id', 'sku', 'item_description', 'quantity', 'amount');
            }])
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $subQuery) {
                    $subQuery->where('tran_id', 'like', '%' . $this->search . '%')
                        ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                        ->orWhere('entity_id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['type'], function (Builder $query, $type) {
                $query->where('type', $type);
            })
            ->when($this->filters['customer'], function (Builder $query, $customerId) {
                $query->where('entity_id', $customerId);
            })
            ->when($this->filters['date_range']['start'], function (Builder $query, $startDate) {
                $query->whereDate('date', '>=', Carbon::parse($startDate));
            })
            ->when($this->filters['date_range']['end'], function (Builder $query, $endDate) {
                $query->whereDate('date', '<=', Carbon::parse($endDate));
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }
    
    /**
     * Load more sales data when scrolling
     */
    public function loadMoreSales()
    {
        $this->loadMore($this->getBaseQuery());
    }
    
    public function getTotal()
    {
        $query = Sale::query()
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $subQuery) {
                    $subQuery->where('tran_id', 'like', '%' . $this->search . '%')
                        ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                        ->orWhere('entity_id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['type'], function (Builder $query, $type) {
                $query->where('type', $type);
            })
            ->when($this->filters['customer'], function (Builder $query, $customerId) {
                $query->where('entity_id', $customerId);
            })
            ->when($this->filters['date_range']['start'], function (Builder $query, $startDate) {
                $query->whereDate('date', '>=', Carbon::parse($startDate));
            })
            ->when($this->filters['date_range']['end'], function (Builder $query, $endDate) {
                $query->whereDate('date', '<=', Carbon::parse($endDate));
            });
            
        return $query->count();
    }
    
    public function getTransactionTypes()
    {
        return Sale::select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->toArray();
    }
    
    public function getUniqueCustomers()
    {
        return Sale::select('entity_id', 'customer_name')
            ->distinct()
            ->orderBy('customer_name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->entity_id,
                    'name' => $item->customer_name . ' (' . $item->entity_id . ')'
                ];
            });
    }
    
    public function resetFilters()
    {
        $this->reset('filters', 'search');
        // Will trigger resetItems through Livewire's updated hooks
    }
    
    /**
     * Implement the resetItems method required by the Filterable trait
     */
    public function resetItems(): void
    {
        $this->items = [];
        $this->loadedCount = 0;
        $this->hasMorePages = true;
        
        // Immediately load fresh data with the current filters
        $this->loadMore($this->getBaseQuery());
        
        // Dispatch an event for Alpine to react to
        $this->dispatch('refresh-data');
    }
    
    // Add explicit watchers to handle filter changes
    public function updatedSearch()
    {
        $this->resetItems();
    }
    
    public function updatedFilters()
    {
        $this->resetItems();
    }
    
    /**
     * Handles updating the sort field and resetting items
     */
    public function updatedSortField()
    {
        $this->resetItems();
    }
    
    /**
     * Toggle the sort direction and reset items
     */
    public function toggleSortDirection()
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->resetItems();
    }
    
    /**
     * Sort by a specific field (legacy method for backwards compatibility)
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        
        $this->resetItems();
    }
    
    public function viewSaleDetails($saleId)
    {
        $this->viewingSale = Sale::with('items')->find($saleId);
    }
    
    public function closeModal()
    {
        $this->viewingSale = null;
    }
    
    public function getSummaryData()
    {
        $query = Sale::query()
            ->select(
                'type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $subQuery) {
                    $subQuery->where('tran_id', 'like', '%' . $this->search . '%')
                        ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                        ->orWhere('entity_id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['type'], function (Builder $query, $type) {
                $query->where('type', $type);
            })
            ->when($this->filters['customer'], function (Builder $query, $customerId) {
                $query->where('entity_id', $customerId);
            })
            ->when($this->filters['date_range']['start'], function (Builder $query, $startDate) {
                $query->whereDate('date', '>=', Carbon::parse($startDate));
            })
            ->when($this->filters['date_range']['end'], function (Builder $query, $endDate) {
                $query->whereDate('date', '<=', Carbon::parse($endDate));
            })
            ->groupBy('type');
            
        return $query->get();
    }
}