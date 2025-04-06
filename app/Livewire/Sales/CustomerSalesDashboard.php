<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Traits\Filterable;
use App\Traits\InfiniteScrollable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

class CustomerSalesDashboard extends Component
{
    use Filterable, InfiniteScrollable;

    public $search = '';

    public $filters = [
        'type' => '',
        'date_range' => [
            'start' => '',
            'end' => '',
        ],
    ];

    public $viewingSale = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'date'],
        'sortDirection' => ['except' => 'desc'],
        'filters' => ['except' => [
            'type' => '',
            'date_range' => [
                'start' => '',
                'end' => '',
            ],
        ]],
    ];

    public function mount()
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        // Check if user has admin sales data permission - they should use admin.sales instead
        if (Auth::user()->hasPermissionTo('view netsuite sales data')) {
            return redirect()->route('admin.sales');
        }

        // Check if user has permission to view their own orders
        if (! Auth::user()->hasPermissionTo('view own orders')) {
            abort(403, 'Unauthorized');
        }

        // Set default sort field and direction
        $this->sortField = 'date';
        $this->sortDirection = 'desc';

        // Initialize items array
        $this->items = [];
        $this->itemsPerPage = 20;

        // Load initial items
        $this->loadMore($this->getBaseQuery());
    }

    #[Title('Sales History')]
    public function render()
    {
        $this->totalCount = $this->getTotal();

        return view('livewire.sales.customer-sales-dashboard', [
            'sales' => $this->items,
            'transactionTypes' => $this->getTransactionTypes(),
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
            ->with(['items' => function ($query) {
                $query->select('id', 'sale_id', 'sku', 'item_description', 'quantity', 'amount');
            }])
            ->when(Auth::user()->customer_number, function (Builder $query) {
                // Filter by customer_number if available
                $query->where('entity_id', Auth::user()->customer_number);
            })
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $subQuery) {
                    $subQuery->where('tran_id', 'like', '%'.$this->search.'%')
                        ->orWhere('customer_name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filters['type'], function (Builder $query, $type) {
                $query->where('type', $type);
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

    // Add explicit watchers to handle filter changes
    public function updatedSearch()
    {
        $this->resetItems();
    }

    public function updatedFilters()
    {
        $this->resetItems();
    }

    public function getTotal()
    {
        $query = Sale::query()
            ->when(Auth::user()->customer_number, function (Builder $query) {
                // Filter by customer_number if available
                $query->where('entity_id', Auth::user()->customer_number);
            })
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $subQuery) {
                    $subQuery->where('tran_id', 'like', '%'.$this->search.'%')
                        ->orWhere('customer_name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filters['type'], function (Builder $query, $type) {
                $query->where('type', $type);
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
            ->when(Auth::user()->customer_number, function (Builder $query) {
                // Filter by customer_number if available
                $query->where('entity_id', Auth::user()->customer_number);
            })
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->toArray();
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

        // Optionally dispatch an event for Alpine to react to
        $this->dispatch('refresh-data');
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
     * Legacy method for compatibility
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
            ->when(Auth::user()->customer_number, function (Builder $query) {
                // Filter by customer_number if available
                $query->where('entity_id', Auth::user()->customer_number);
            })
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $subQuery) {
                    $subQuery->where('tran_id', 'like', '%'.$this->search.'%')
                        ->orWhere('customer_name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filters['type'], function (Builder $query, $type) {
                $query->where('type', $type);
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
