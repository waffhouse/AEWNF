<?php

namespace App\Livewire\Admin\Sales;

use App\Models\Customer;
use App\Traits\AdminAuthorization;
use App\Traits\Filterable;
use App\Traits\InfiniteScrollable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class CustomersWithoutSales extends Component
{
    use AdminAuthorization, Filterable, InfiniteScrollable;

    public $search = '';

    public $filters = [
        'county' => '',
        'home_state' => '',
        'date_range' => [
            'start' => '',
            'end' => '',
        ],
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'company_name'],
        'sortDirection' => ['except' => 'asc'],
        'filters' => ['except' => [
            'county' => '',
            'home_state' => '',
            'date_range' => [
                'start' => '',
                'end' => '',
            ],
        ]],
    ];

    public function mount()
    {
        $this->authorize('manage orders');

        // Set default sort field and direction
        $this->sortField = 'company_name';
        $this->sortDirection = 'asc';

        // Initialize items array
        $this->items = [];
        $this->itemsPerPage = 20;

        // Load initial items
        $this->loadMore($this->getBaseQuery());
    }

    public function render()
    {
        $this->totalCount = $this->getTotal();

        return view('livewire.admin.sales.customers-without-sales', [
            'customers' => $this->items,
            'counties' => $this->getCounties(),
            'states' => $this->getStates(),
            'summary' => $this->getSummaryData(),
        ])->layout('layouts.app');
    }

    /**
     * Get the base query for customers without sales with all filters applied
     */
    public function getBaseQuery()
    {
        // Start with all customers
        return Customer::query()
            ->whereDoesntHave('sales') // Key filter - only customers with no sales
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $subQuery) {
                    $subQuery->where('entity_id', 'like', "%{$this->search}%")
                        ->orWhere('company_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('license_number', 'like', "%{$this->search}%")
                        ->orWhere('county', 'like', "%{$this->search}%")
                        ->orWhere('home_state', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filters['county'], function (Builder $query, $county) {
                $query->where('county', $county);
            })
            ->when($this->filters['home_state'], function (Builder $query, $state) {
                $query->where('home_state', $state);
            })
            ->when($this->filters['date_range']['start'], function (Builder $query, $startDate) {
                // Filter by last_sync_at date if provided
                $query->whereDate('last_sync_at', '>=', Carbon::parse($startDate));
            })
            ->when($this->filters['date_range']['end'], function (Builder $query, $endDate) {
                $query->whereDate('last_sync_at', '<=', Carbon::parse($endDate));
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    /**
     * Load more customers when scrolling
     */
    public function loadMoreCustomers()
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
        return $this->getBaseQuery()->count();
    }

    public function getCounties()
    {
        return Customer::select('county')
            ->whereDoesntHave('sales')
            ->whereNotNull('county')
            ->where('county', '!=', '')
            ->distinct()
            ->orderBy('county')
            ->pluck('county')
            ->toArray();
    }

    public function getStates()
    {
        return Customer::select('home_state')
            ->whereDoesntHave('sales')
            ->whereNotNull('home_state')
            ->where('home_state', '!=', '')
            ->distinct()
            ->orderBy('home_state')
            ->pluck('home_state')
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

    /**
     * Get summary data for display at the top of the report
     */
    public function getSummaryData()
    {
        // Get the total number of customers
        $totalCustomers = Customer::count();

        // Get the number of customers without sales (from our filter)
        $customersWithoutSales = $this->getBaseQuery()->count();

        // Get the number of customers with sales
        $customersWithSales = $totalCustomers - $customersWithoutSales;

        // Get total sales data
        $totalSalesCount = \App\Models\Sale::count();

        // Return the summary data
        return [
            'total_customers' => $totalCustomers,
            'customers_without_sales' => $customersWithoutSales,
            'customers_with_sales' => $customersWithSales,
            'total_sales_count' => $totalSalesCount,
            'percentage_without_sales' => $totalCustomers > 0
                ? round(($customersWithoutSales / $totalCustomers) * 100, 1)
                : 0,
        ];
    }
}
