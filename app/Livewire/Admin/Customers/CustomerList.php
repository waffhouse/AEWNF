<?php

namespace App\Livewire\Admin\Customers;

use App\Livewire\Admin\AdminComponent;
use App\Models\Customer;
use App\Traits\Filterable;
use App\Traits\InfiniteScrollable;
use Livewire\Attributes\On;

class CustomerList extends AdminComponent
{
    use Filterable, InfiniteScrollable;

    public $search = '';

    public int $perPage = 25;

    /**
     * Define the permissions required for this component
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'view customers'];
    }

    public function mount()
    {
        parent::mount();
        $this->resetItems();
    }

    /**
     * Define the filters to be applied to the query
     */
    protected function filters(): array
    {
        return [
            'search' => function ($query, $value) {
                $query->where(function ($q) use ($value) {
                    $q->where('entity_id', 'like', "%{$value}%")
                        ->orWhere('company_name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('license_number', 'like', "%{$value}%")
                        ->orWhere('county', 'like', "%{$value}%")
                        ->orWhere('home_state', 'like', "%{$value}%");
                });
            },
        ];
    }

    /**
     * Get the base query for the customers
     */
    protected function baseQuery()
    {
        return Customer::query()->orderBy('company_name');
    }

    /**
     * Reset items when changing search
     */
    public function updatedSearch()
    {
        $this->resetItems();
    }

    /**
     * Reset all filters
     */
    public function resetFilters()
    {
        $this->search = '';
        $this->resetItems();
    }

    /**
     * Implement the abstract resetItems method required by the Filterable trait
     */
    public function resetItems(): void
    {
        // Reset items collection
        $this->items = [];
        $this->loadedCount = 0;
        $this->hasMorePages = true;

        // Load initial items
        $query = $this->getCustomerQuery();
        $this->loadItems($query);
    }

    /**
     * Load items with pagination
     */
    public function loadItems($query)
    {
        $this->isLoading = true;

        try {
            // Clone query to avoid modifying the original
            $countQuery = clone $query;

            // Use a paginator for better performance
            $paginator = $query->simplePaginate(
                $this->perPage,
                ['*'],
                'page',
                ceil($this->loadedCount / $this->perPage) + 1
            );

            // Only count total rows when needed
            if ($this->loadedCount === 0) {
                $this->totalCount = $countQuery->count();
            }

            $newItems = $paginator->items();

            // Check if there are more pages directly from the paginator
            $this->hasMorePages = $paginator->hasMorePages();

            // Append new items to existing collection
            foreach ($newItems as $item) {
                $this->items[] = $item;
            }

            // Update loaded count
            $this->loadedCount += count($newItems);
        } catch (\Exception $e) {
            // Log error but don't break
            logger()->error('Error loading customers: '.$e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Method to reload customers after sync
     */
    #[On('customers-refreshed')]
    public function refreshCustomers()
    {
        $this->resetItems();
    }

    /**
     * Load more items when scrolling (implementation for infinite scroll)
     */
    public function loadMore()
    {
        if ($this->hasMorePages && ! $this->isLoading) {
            $query = $this->getCustomerQuery();
            $this->loadItems($query);
        }
    }

    /**
     * Get the customer query with all filters applied
     */
    private function getCustomerQuery()
    {
        $query = Customer::query()->orderBy('company_name');

        // Apply search filter
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('entity_id', 'like', "%{$this->search}%")
                    ->orWhere('company_name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('license_number', 'like', "%{$this->search}%")
                    ->orWhere('county', 'like', "%{$this->search}%")
                    ->orWhere('home_state', 'like', "%{$this->search}%");
            });
        }

        return $query;
    }

    // States and license types properties have been removed as they are no longer needed

    /**
     * Show customer details in a modal
     */
    public function showCustomerDetails($customerId)
    {
        // Find the customer
        $customer = Customer::findOrFail($customerId);

        // Dispatch event to show modal with customer details
        $this->dispatch('show-customer-details', ['customerId' => $customerId]);

        // For debugging
        // \Log::info("Dispatched show-customer-details with customer ID: $customerId");
    }

    public function render()
    {
        return view('livewire.admin.customers.customer-list', [
            'customers' => $this->items,
            'hasMorePages' => $this->hasMorePages,
            'totalCount' => $this->totalCount,
            'loadedCount' => $this->loadedCount,
        ]);
    }
}
