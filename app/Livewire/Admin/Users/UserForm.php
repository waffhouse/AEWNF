<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\AdminComponent;
use App\Services\UserService;
use App\Traits\FormValidatable;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class UserForm extends AdminComponent
{
    use FormValidatable;

    // For user form
    public $name;

    public $email;

    public $password;

    public $userRole;

    public $customer_number;

    public $selected_customer_id;

    public $customer_search = '';

    public $customer_state_filter = '';

    // UserService instance
    protected UserService $userService;

    // Cache for customers data
    protected $cachedCustomers = null;

    /**
     * Define the permissions required for this component
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'view users'];
    }

    /**
     * Constructor to inject dependencies
     */
    public function boot()
    {
        $this->userService = new UserService;
    }

    /**
     * Cache for roles data to avoid repeated queries
     */
    protected $cachedRoles = null;

    /**
     * Get roles with caching to prevent redundant database queries
     */
    protected function getRoles()
    {
        if ($this->cachedRoles === null) {
            $this->cachedRoles = $this->userService->getAllRoles();
        }

        return $this->cachedRoles;
    }

    /**
     * Get customers for customer selector
     */
    protected function getCustomers()
    {
        if ($this->cachedCustomers === null) {
            $this->cachedCustomers = \App\Models\Customer::orderBy('company_name')
                ->select('id', 'entity_id', 'company_name', 'home_state')
                ->get()
                ->map(function ($customer) {
                    $companyName = $customer->company_name ?: 'Unknown';
                    $state = $customer->home_state ?: '';

                    return [
                        'id' => $customer->id,
                        'entity_id' => $customer->entity_id,
                        'company_name' => $companyName,
                        'home_state' => $state,
                        'display_name' => "{$customer->entity_id} - {$companyName}".($state ? " ({$state})" : ''),
                    ];
                });
        }

        // Apply filters to the customers list
        $filteredCustomers = collect($this->cachedCustomers);

        if (! empty($this->customer_search)) {
            $search = strtolower($this->customer_search);
            $filteredCustomers = $filteredCustomers->filter(function ($customer) use ($search) {
                return str_contains(strtolower($customer['entity_id']), $search) ||
                       str_contains(strtolower($customer['company_name']), $search);
            });
        }

        if (! empty($this->customer_state_filter)) {
            $filteredCustomers = $filteredCustomers->filter(function ($customer) {
                return $customer['home_state'] == $this->customer_state_filter;
            });
        }

        return $filteredCustomers;
    }

    /**
     * Get unique states for filtering
     */
    public function getCustomerStates()
    {
        if ($this->cachedCustomers === null) {
            $this->getCustomers(); // Ensure customers are loaded
        }

        return collect($this->cachedCustomers)
            ->pluck('home_state')
            ->filter() // Remove empty values
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.users.user-form', [
            'roles' => $this->getRoles(),
            'customers' => $this->getCustomers(),
            'states' => $this->getCustomerStates(),
        ]);
    }

    /**
     * Define base validation rules that apply to both create and update
     */
    protected function baseRules(): array
    {
        return [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users,email,'.($this->editId ?? ''),
            'userRole' => 'required',
        ];
    }

    /**
     * Define rules that only apply when creating
     */
    protected function createRules(): array
    {
        $customerNumberRule = auth()->user()->hasPermissionTo('create users')
            ? 'nullable|string|max:10|unique:users,customer_number'
            : 'prohibited';

        // Only require customer selection for customer roles
        $selectedCustomerRule = in_array(strtolower($this->userRole), ['customer', 'florida customer', 'georgia customer'])
            ? 'required'  // Customer role requires customer selection
            : 'nullable'; // Non-customer roles don't need customer

        return [
            'password' => 'required|min:8',
            'customer_number' => $customerNumberRule,
            'selected_customer_id' => $selectedCustomerRule,
        ];
    }

    /**
     * Define rules that only apply when updating
     */
    protected function updateRules(): array
    {
        $customerNumberRule = auth()->user()->hasPermissionTo('edit users')
            ? 'nullable|string|max:10|unique:users,customer_number,'.($this->editId ?? '')
            : 'prohibited';

        // Only require customer selection for customer roles
        $selectedCustomerRule = in_array(strtolower($this->userRole), ['customer', 'florida customer', 'georgia customer'])
            ? 'required'  // Customer role requires customer selection
            : 'nullable'; // Non-customer roles don't need customer

        return [
            'password' => 'nullable|min:8',
            'customer_number' => $customerNumberRule,
            'selected_customer_id' => $selectedCustomerRule,
        ];
    }

    /**
     * Define custom validation messages
     */
    protected function customMessages(): array
    {
        return [
            'name.required' => 'Please enter a name for the user.',
            'name.min' => 'The name must be at least 2 characters long.',
            'customer_number.regex' => 'The customer number must be a 4-digit number.',
            'customer_number.unique' => 'This customer number is already in use. Please assign a different number.',
            'selected_customer_id.required' => 'A customer must be selected for customer roles.',
        ];
    }

    /**
     * Open the form modal for creating a user
     * Listens for the open-new-user-form event from parent component
     */
    #[On('open-new-user-form')]
    public function openUserFormModal()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('create users');

        $user = auth()->user();
        Log::info('User '.$user->name.' opening user creation form');

        $this->enterCreateMode();
        $this->dispatch('open-modal', 'user-form-modal');
    }

    /**
     * Create a new user
     */
    public function createUser()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('create users');

        $user = auth()->user();
        Log::info('User '.$user->name.' creating new user');

        // Use the FormValidatable trait for validation
        if (! $this->validateForm()) {
            return;
        }

        // Use the saveWithErrorHandling method for consistent error handling
        $success = $this->saveWithErrorHandling(
            function () {
                $userData = [
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => $this->password,
                ];

                // Only users with create users permission can set customer numbers, and only for customer roles
                if (auth()->user()->hasPermissionTo('create users') &&
                    (strpos($this->userRole, 'customer') !== false)) {
                    $userData['customer_number'] = $this->customer_number;
                }

                // Use the UserService to create the user
                $user = $this->userService->createUser($userData, $this->userRole);

                // Store the user ID to pass with event
                $this->newUserId = $user->id;

                return true;
            },
            'User created successfully!',
            'User creation failed'
        );

        if ($success) {
            $this->resetForm();
            $this->dispatch('close-modal', 'user-form-modal');

            // Dispatch event to user-list component with the new user ID
            $this->dispatch('user-created', ['id' => $this->newUserId])->to('admin.users.user-list');
        }
    }

    /**
     * Listen for the event to open the edit form
     */
    #[On('open-user-edit')]
    public function openUserEdit($userId)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('edit users');

        $user = auth()->user();
        Log::info('User '.$user->name.' opening edit form for user ID: '.$userId);

        $this->enterEditMode($userId);
        $this->dispatch('open-modal', 'user-form-modal');
    }

    /**
     * Load a user record for editing
     */
    protected function loadRecord($id): void
    {
        $user = $this->userService->getUserById($id);
        if (! $user) {
            $this->flashError('User not found');

            return;
        }

        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->customer_number = $user->customer_number;
        $this->userRole = $user->roles->first() ? $user->roles->first()->name : '';

        // If user has customer_number, find the associated customer
        if ($user->customer_number) {
            $customer = \App\Models\Customer::where('entity_id', $user->customer_number)->first();
            if ($customer) {
                $this->selected_customer_id = $customer->id;
            }
        }
    }

    /**
     * Update an existing user
     */
    public function updateUser()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('edit users');

        $user = auth()->user();
        Log::info('User '.$user->name.' updating user ID: '.$this->editId);

        // Use the FormValidatable trait for validation
        if (! $this->validateForm()) {
            return;
        }

        // Use the saveWithErrorHandling method for consistent error handling
        $success = $this->saveWithErrorHandling(
            function () {
                $userData = [
                    'name' => $this->name,
                    'email' => $this->email,
                ];

                // Only include password if provided
                if ($this->password) {
                    $userData['password'] = $this->password;
                }

                // Only users with edit users permission can update customer numbers, and only for customer roles
                if (auth()->user()->hasPermissionTo('edit users') &&
                    (strpos($this->userRole, 'customer') !== false)) {
                    $userData['customer_number'] = $this->customer_number;
                }

                // Use the UserService to update the user
                $user = $this->userService->updateUser($this->editId, $userData, $this->userRole);

                return true;
            },
            'User updated successfully!',
            'User update failed'
        );

        if ($success) {
            $this->resetForm();
            $this->dispatch('close-modal', 'user-form-modal');

            // Dispatch event to user-list component with the updated user ID
            $this->dispatch('user-updated', ['id' => $this->editId])->to('admin.users.user-list');
        }
    }

    /**
     * Reset the form to its default state
     */
    public function resetForm(): void
    {
        $this->reset(['name', 'email', 'password', 'userRole', 'customer_number', 'selected_customer_id', 'customer_search', 'customer_state_filter']);
        $this->formErrors = []; // Reset form errors directly
    }

    /**
     * Handle form cancellation
     * Accept arbitrary number of arguments to handle any way the event might be called
     */
    #[On('user-form-cancelled')]
    public function handleFormCancellation()
    {
        $this->resetForm();
    }

    /**
     * When a customer is selected, set the customer_number field and update role
     */
    public function updatedSelectedCustomerId($value)
    {
        if (empty($value)) {
            $this->customer_number = null;

            return;
        }

        $customer = $this->getCustomers()->firstWhere('id', $value);
        if ($customer) {
            // Set the customer number
            $this->customer_number = $customer['entity_id'];

            // Set appropriate role based on customer state
            if (strtolower($customer['home_state']) === 'florida') {
                $this->userRole = 'florida customer';
            } elseif (strtolower($customer['home_state']) === 'georgia') {
                $this->userRole = 'georgia customer';
            } else {
                // For customers with other or no state, use generic customer role
                $this->userRole = 'customer';
            }
        }
    }

    /**
     * When a role is selected, clear customer if not a customer role
     */
    public function updatedUserRole($value)
    {
        $role = strtolower($value);

        // Dispatch event to Alpine.js to update UI
        $this->dispatch('role-updated', $value ? (string) $value : '');

        // If not a customer role, clear customer selection
        if (! in_array($role, ['customer', 'florida customer', 'georgia customer'])) {
            $this->selected_customer_id = null;
            $this->customer_number = null;

            return;
        }

        // For customer roles, check if state matches when a customer is selected
        if (! $this->selected_customer_id) {
            return;
        }

        // Get the selected customer info
        $customer = $this->getCustomers()->firstWhere('id', $this->selected_customer_id);
        if (! $customer) {
            return;
        }

        // Check if we need to clear the customer selection
        $state = strtolower($customer['home_state'] ?? '');

        if (
            ($role === 'florida customer' && $state !== 'florida') ||
            ($role === 'georgia customer' && $state !== 'georgia')
        ) {
            // Clear customer info if it doesn't match the role
            $this->selected_customer_id = null;
            $this->customer_number = null;
        }
    }
}
