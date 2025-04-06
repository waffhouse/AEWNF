<?php

namespace App\Livewire\Admin\Sales;

use App\Models\Sale;
use App\Traits\AdminAuthorization;
use App\Traits\Filterable;
use App\Traits\InfiniteScrollable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SalesList extends Component
{
    use AdminAuthorization, Filterable, InfiniteScrollable;

    // No listeners needed - manual refresh only

    public $search = '';

    // Note: $sortField and $sortDirection are already defined in InfiniteScrollable trait
    // We'll override them in mount() instead
    public $filters = [
        'type' => '',
        'date_range' => [
            'start' => '',
            'end' => '',
        ],
        'customer' => '',
    ];

    public $perPage = 25;

    public $total = 0;

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
            'customer' => '',
        ]],
    ];

    public function mount()
    {
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

    public function render()
    {
        $this->totalCount = $this->getTotal();

        return view('livewire.admin.sales.sales-list', [
            'sales' => $this->items,
            'transactionTypes' => $this->getTransactionTypes(),
        ]);
    }

    /**
     * Get the base query for sales with all filters applied
     */
    public function getBaseQuery()
    {
        // Add a debug statement to check the query
        \Illuminate\Support\Facades\Log::info('Building sales query', [
            'sort_field' => $this->sortField,
            'sort_direction' => $this->sortDirection,
            'search' => $this->search,
            'filters' => $this->filters,
            'loaded_count' => $this->loadedCount,
        ]);

        return Sale::query()
            ->select('id', 'tran_id', 'type', 'date', 'entity_id', 'customer_name', 'total_amount', 'created_at')
            ->with(['items' => function ($query) {
                $query->select('id', 'sale_id', 'sku', 'item_description', 'quantity', 'amount');
            }])
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
            ->when($this->filters['customer'], function (Builder $query, $customer) {
                $query->where('customer_name', 'like', '%'.$customer.'%');
            });
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
            ->when($this->filters['customer'], function (Builder $query, $customer) {
                $query->where('customer_name', 'like', '%'.$customer.'%');
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
        $this->loadMore($this->getBaseQuery());
    }

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
            ->when($this->filters['customer'], function (Builder $query, $customer) {
                $query->where('customer_name', 'like', '%'.$customer.'%');
            })
            ->groupBy('type');

        return $query->get();
    }

    /**
     * Refresh the sales list when data changes
     */
    public function refreshList()
    {
        \Illuminate\Support\Facades\Log::info('SalesList refreshList called - reloading data');

        // Reset the items collection completely
        $this->items = [];
        $this->loadedCount = 0;
        $this->hasMorePages = true;

        // Load fresh data
        $this->loadMore($this->getBaseQuery());

        // No need to dispatch additional events that might cause loops
    }
}
