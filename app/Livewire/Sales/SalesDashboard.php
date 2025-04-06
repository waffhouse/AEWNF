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

class SalesDashboard extends Component
{
    use Filterable, InfiniteScrollable;

    public $search = '';

    public $filters = [
        'type' => '',
        'customer' => '',
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
            'customer' => '',
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

        // Check if user has permission to view sales data
        if (! Auth::user()->hasPermissionTo('view netsuite sales data') &&
            ! Auth::user()->hasPermissionTo('view own orders')) {
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
        $canViewAllSales = Auth::user()->hasPermissionTo('view netsuite sales data');

        return view('livewire.sales.sales-dashboard', [
            'sales' => $this->items,
            'transactionTypes' => $this->getTransactionTypes(),
            'customers' => $canViewAllSales ? $this->getUniqueCustomers() : [],
            'summary' => $this->getSummaryData(),
            'canViewAllSales' => $canViewAllSales,
        ])->layout('layouts.app');
    }

    /**
     * Get the base query for sales with all filters applied
     */
    public function getBaseQuery()
    {
        $query = Sale::query()
            ->select('id', 'tran_id', 'type', 'date', 'entity_id', 'customer_name', 'total_amount', 'created_at')
            ->with(['items' => function ($query) {
                $query->select('id', 'sale_id', 'sku', 'item_description', 'quantity', 'amount');
            }]);

        // If user doesn't have admin sales permission, restrict to their customer number
        if (! Auth::user()->hasPermissionTo('view netsuite sales data') && Auth::user()->customer_number) {
            $query->where('entity_id', Auth::user()->customer_number);
        }

        // Apply search filter
        $query->when($this->search, function (Builder $query) {
            $query->where(function (Builder $subQuery) {
                $subQuery->where('tran_id', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_name', 'like', '%'.$this->search.'%');

                // Include entity_id in search only for admin users
                if (Auth::user()->hasPermissionTo('view netsuite sales data')) {
                    $subQuery->orWhere('entity_id', 'like', '%'.$this->search.'%');
                }
            });
        });

        // Apply transaction type filter
        $query->when($this->filters['type'], function (Builder $query, $type) {
            $query->where('type', $type);
        });

        // Apply customer filter (admin only)
        if (Auth::user()->hasPermissionTo('view netsuite sales data')) {
            $query->when($this->filters['customer'], function (Builder $query, $customerId) {
                $query->where('entity_id', $customerId);
            });
        }

        // Apply date range filters
        $query->when($this->filters['date_range']['start'], function (Builder $query, $startDate) {
            $query->whereDate('date', '>=', Carbon::parse($startDate));
        });

        $query->when($this->filters['date_range']['end'], function (Builder $query, $endDate) {
            $query->whereDate('date', '<=', Carbon::parse($endDate));
        });

        // Apply sorting
        return $query->orderBy($this->sortField, $this->sortDirection);
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

        // Dispatch event to scroll to top
        $this->dispatch('scroll-to-top');
    }

    /**
     * Handle search form submission (especially for mobile)
     * This prevents the keyboard from staying open when searching
     */
    public function submitSearch()
    {
        // This method triggers a refresh with the current search term
        // The search property is already updated through wire:model
        $this->resetItems();

        // Dispatch event to scroll to top
        $this->dispatch('scroll-to-top');
    }

    public function updatedFilters()
    {
        $this->resetItems();
    }

    public function getTotal()
    {
        $query = Sale::query();

        // If user doesn't have admin sales permission, restrict to their customer number
        if (! Auth::user()->hasPermissionTo('view netsuite sales data') && Auth::user()->customer_number) {
            $query->where('entity_id', Auth::user()->customer_number);
        }

        // Apply search filter
        $query->when($this->search, function (Builder $query) {
            $query->where(function (Builder $subQuery) {
                $subQuery->where('tran_id', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_name', 'like', '%'.$this->search.'%');

                // Include entity_id in search only for admin users
                if (Auth::user()->hasPermissionTo('view netsuite sales data')) {
                    $subQuery->orWhere('entity_id', 'like', '%'.$this->search.'%');
                }
            });
        });

        // Apply transaction type filter
        $query->when($this->filters['type'], function (Builder $query, $type) {
            $query->where('type', $type);
        });

        // Apply customer filter (admin only)
        if (Auth::user()->hasPermissionTo('view netsuite sales data')) {
            $query->when($this->filters['customer'], function (Builder $query, $customerId) {
                $query->where('entity_id', $customerId);
            });
        }

        // Apply date range filters
        $query->when($this->filters['date_range']['start'], function (Builder $query, $startDate) {
            $query->whereDate('date', '>=', Carbon::parse($startDate));
        });

        $query->when($this->filters['date_range']['end'], function (Builder $query, $endDate) {
            $query->whereDate('date', '<=', Carbon::parse($endDate));
        });

        return $query->count();
    }

    public function getTransactionTypes()
    {
        $query = Sale::select('type');

        // If user doesn't have admin sales permission, restrict to their customer number
        if (! Auth::user()->hasPermissionTo('view netsuite sales data') && Auth::user()->customer_number) {
            $query->where('entity_id', Auth::user()->customer_number);
        }

        return $query->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->toArray();
    }

    public function getUniqueCustomers()
    {
        // Only available for users with admin sales permission
        if (! Auth::user()->hasPermissionTo('view netsuite sales data')) {
            return [];
        }

        return Sale::select('entity_id', 'customer_name')
            ->distinct()
            ->orderBy('customer_name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->entity_id,
                    'name' => $item->customer_name.' ('.$item->entity_id.')',
                ];
            });
    }

    /**
     * Reset all filters and search criteria
     */
    public function resetFilters()
    {
        $this->reset('filters', 'search');

        // Explicitly reset items instead of relying on hooks
        $this->resetItems();

        // Scroll to top
        $this->dispatch('scroll-to-top');
    }

    /**
     * Clear just the date range filter
     */
    public function clearDateFilter()
    {
        $this->filters['date_range'] = [
            'start' => '',
            'end' => '',
        ];

        // Explicitly reset items
        $this->resetItems();

        // Scroll to top
        $this->dispatch('scroll-to-top');
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
            );

        // If user doesn't have admin sales permission, restrict to their customer number
        if (! Auth::user()->hasPermissionTo('view netsuite sales data') && Auth::user()->customer_number) {
            $query->where('entity_id', Auth::user()->customer_number);
        }

        // Apply search filter
        $query->when($this->search, function (Builder $query) {
            $query->where(function (Builder $subQuery) {
                $subQuery->where('tran_id', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_name', 'like', '%'.$this->search.'%');

                // Include entity_id in search only for admin users
                if (Auth::user()->hasPermissionTo('view netsuite sales data')) {
                    $subQuery->orWhere('entity_id', 'like', '%'.$this->search.'%');
                }
            });
        });

        // Apply transaction type filter
        $query->when($this->filters['type'], function (Builder $query, $type) {
            $query->where('type', $type);
        });

        // Apply customer filter (admin only)
        if (Auth::user()->hasPermissionTo('view netsuite sales data')) {
            $query->when($this->filters['customer'], function (Builder $query, $customerId) {
                $query->where('entity_id', $customerId);
            });
        }

        // Apply date range filters
        $query->when($this->filters['date_range']['start'], function (Builder $query, $startDate) {
            $query->whereDate('date', '>=', Carbon::parse($startDate));
        });

        $query->when($this->filters['date_range']['end'], function (Builder $query, $endDate) {
            $query->whereDate('date', '<=', Carbon::parse($endDate));
        });

        return $query->groupBy('type')->get();
    }
}
