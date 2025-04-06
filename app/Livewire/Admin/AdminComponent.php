<?php

namespace App\Livewire\Admin;

use App\Traits\AdminAuthorization;
use Livewire\Component;

abstract class AdminComponent extends Component
{
    use AdminAuthorization;

    // Common properties for all admin components
    protected int $perPage = 10;

    public string $searchQuery = '';

    public function mount()
    {
        // Basic check - only users with proper permissions can access admin components
        $this->authorizeAdminAccess();

        // Check if user has at least one of the required permissions for this component
        $this->checkRequiredPermissions();

        $this->mountComponent();
    }

    /**
     * Check if the current user can access data for a specific customer
     * Used for customer-specific data filtering
     *
     * @param  string  $customerEntityId  The customer ID to check against
     */
    protected function userCanAccessCustomerData(string $customerEntityId): bool
    {
        $user = auth()->user();

        // Admin/staff with permission to view all customer data
        if ($user->hasPermissionTo('view netsuite sales data')) {
            return true;
        }

        // Check if the user's customer_number matches the requested entity ID
        return $user->customer_number && $user->customer_number === $customerEntityId;
    }

    /**
     * Apply customer restrictions to a database query based on user permissions
     * This ensures customer users only see their own data
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $customerIdColumn  The column name that contains the customer ID
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyCustomerRestrictions($query, string $customerIdColumn = 'entity_id')
    {
        $user = auth()->user();

        // Skip restrictions for admin/staff with appropriate permissions
        if ($user->hasPermissionTo('view netsuite sales data')) {
            return $query;
        }

        // If user has a customer number, restrict to only that customer's data
        if ($user->customer_number) {
            return $query->where($customerIdColumn, $user->customer_number);
        }

        // If user doesn't have a customer number, return no results
        // This is safer than returning all data if a user somehow has
        // customer permissions but no assigned customer
        return $query->whereRaw('1 = 0'); // This ensures no results are returned
    }

    /**
     * Check if the user has at least one of the required permissions for this component
     */
    protected function checkRequiredPermissions(): void
    {
        $requiredPermissions = $this->getRequiredPermissions();
        $user = auth()->user();

        if (empty($requiredPermissions)) {
            // No specific permissions required, proceed
            return;
        }

        // Check if user has any of the required permissions
        foreach ($requiredPermissions as $permission) {
            if ($user->hasPermissionTo($permission)) {
                return;
            }
        }

        // If we get here, user has none of the required permissions
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'You do not have any of the required permissions to access this component.'
        );
    }

    /**
     * Flash a success message
     */
    protected function flashSuccess(string $message): void
    {
        $this->dispatch('message', $message);
    }

    /**
     * Flash an error message
     */
    protected function flashError(string $message): void
    {
        $this->dispatch('error', $message);
    }

    /**
     * Handle search query changes
     */
    public function updatedSearchQuery(): void
    {
        // Child components should implement their own search reset logic
    }

    /**
     * Each child component must define its required permissions
     */
    abstract public function getRequiredPermissions(): array;

    /**
     * Child components can override this for additional setup
     */
    protected function mountComponent(): void
    {
        // Implement in child components if needed
    }
}
