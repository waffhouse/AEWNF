<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\AdminComponent;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class UserList extends AdminComponent
{
    use WithPagination;
    
    // For sorting
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    // For filtering
    public $userSearch = '';
    public $roleFilter = '';
    
    // For deletion
    public $deleteUserId = null;
    
    // UserService instance
    protected UserService $userService;
    
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
        $this->userService = new UserService();
    }
    
    /**
     * Sort items by field
     */
    public function sortBy(string $field)
    {
        // Toggle sort direction if clicking the same field
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        
        // Reset pagination to first page
        $this->resetPage();
    }
    
    /**
     * Search users with form submission
     */
    public function searchUsers()
    {
        $this->resetPage();
    }
    
    /**
     * Reset search to show all users
     */
    public function resetSearch()
    {
        $this->userSearch = '';
        $this->resetPage();
    }
    
    /**
     * Filter by role
     */
    public function filterByRole(string $role)
    {
        $this->roleFilter = $role;
        $this->resetPage();
    }
    
    /**
     * Reset role filter
     */
    public function resetRoleFilter()
    {
        $this->roleFilter = '';
        $this->resetPage();
    }
    
    /**
     * Reset all filters
     */
    public function resetAllFilters()
    {
        $this->userSearch = '';
        $this->roleFilter = '';
        $this->resetPage();
    }
    
    /**
     * Get active filters for badges
     */
    public function getActiveFilters(): array
    {
        $filters = [];
        
        if (!empty($this->userSearch)) {
            $filters[] = [
                'label' => 'Search',
                'value' => $this->userSearch,
                'active' => true,
                'removeEvent' => 'resetSearch'
            ];
        }
        
        if (!empty($this->roleFilter)) {
            // Format role name nicely (e.g., "florida customer" -> "Florida Customer")
            $formattedRole = ucwords(str_replace('_', ' ', $this->roleFilter));
            
            $filters[] = [
                'label' => 'Role',
                'value' => $formattedRole,
                'active' => true,
                'removeEvent' => 'resetRoleFilter'
            ];
        }
        
        return $filters;
    }
    
    /**
     * Scalable database verification function
     * Shows summary statistics and a limited sample of users
     */
    public function verifyDatabaseConsistency()
    {
        // Authorize this action to admin users only
        $this->authorizeAction('manage roles');
        
        try {
            // 1. Get total user count
            $totalUsers = \DB::table('users')->count();
            
            // 2. Get role counts
            $roleCounts = \DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->select('roles.name')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('roles.name')
                ->pluck('count', 'name')
                ->toArray();
            
            // 3. Get the 10 most recently created users for sampling
            $recentUsers = \DB::table('users')
                ->select('id', 'name', 'email', 'customer_number', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // 4. Get roles for these sample users
            $userRoles = [];
            foreach($recentUsers as $user) {
                $role = \DB::table('model_has_roles')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->where('model_has_roles.model_id', $user->id)
                    ->where('model_has_roles.model_type', 'App\\Models\\User')
                    ->select('roles.name')
                    ->first();
                
                $userRoles[$user->id] = $role ? $role->name : 'No role';
            }
            
            // 5. Format sample users for display
            $formattedUsers = [];
            foreach($recentUsers as $user) {
                $formattedUsers[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $userRoles[$user->id],
                    'customer_number' => $user->customer_number,
                    // Format created_at as a human-readable date
                    'created_at' => date('Y-m-d H:i', strtotime($user->created_at))
                ];
            }
            
            // 6. Prepare summary statistics
            $summary = [
                'total_users' => $totalUsers,
                'role_counts' => $roleCounts,
                'users_without_roles' => $totalUsers - array_sum($roleCounts),
                'sample_size' => count($formattedUsers),
                'sample_type' => 'Most recently created users'
            ];
            
            // Show simple success message
            $this->flashSuccess('Database check: Found ' . $totalUsers . ' total users.');
            
            // Open the modal and send data
            $this->dispatch('open-modal', 'database-verification-modal');
            
            $this->js("
                setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('database-verification-data', {
                        detail: {
                            users: " . json_encode($formattedUsers) . ",
                            summary: " . json_encode($summary) . "
                        }
                    }));
                }, 500);
            ");
            
        } catch (\Exception $e) {
            $this->flashError('Error verifying database consistency: ' . $e->getMessage());
        }
    }
    
    /**
     * Open edit form for a user
     */
    public function openUserEdit($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('edit users');
        
        // Dispatch event to the form component
        $this->dispatch('open-user-edit', $id)->to('admin.users.user-form');
    }
    
    /**
     * Confirm user deletion
     */
    public function confirmDeleteUser($id)
    {
        // Use the central method to authorize this action
        $this->authorizeAction('delete users');
        
        $user = auth()->user();
        Log::info('User ' . $user->name . ' confirming deletion of user ID: ' . $id);
        
        // Store the ID and open the confirmation modal
        $this->deleteUserId = $id;
        $this->dispatch('open-modal', 'delete-user-confirmation');
    }
    
    /**
     * Delete a user
     */
    public function deleteUser()
    {
        // Use the central method to authorize this action
        $this->authorizeAction('delete users');
        
        $user = auth()->user();
        Log::info('User ' . $user->name . ' deleting user ID: ' . $this->deleteUserId);
        
        if (!$this->deleteUserId) {
            return;
        }
        
        try {
            // Use the UserService to delete the user
            $this->userService->deleteUser($this->deleteUserId);
            
            $this->flashSuccess('User deleted successfully!');
            $this->dispatch('close-modal', 'delete-user-confirmation');
        } catch (\Exception $e) {
            Log::error('User deletion failed: ' . $e->getMessage());
            $this->flashError('User deletion failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Listen for user created/updated events
     * No need to manually update the list - just let the pagination refresh
     */
    #[On('user-created')]
    #[On('user-updated')]
    #[On('users-refreshed')]
    public function refreshUserList()
    {
        // The list will refresh automatically on the next render
        // This empty method is just to catch the events
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        // Get all available roles for the dropdown
        $roles = $this->userService->getAllRoles();
        
        // Build the filters array
        $filters = ['search' => $this->userSearch];
        if (!empty($this->roleFilter)) {
            $filters['role'] = $this->roleFilter;
        }
        
        $users = $this->userService->getPaginatedUsers(
            10, // per page
            $filters,
            [
                'with' => ['roles'],
                'orderBy' => $this->sortField,
                'direction' => $this->sortDirection
            ]
        );
        
        return view('livewire.admin.users.user-list', [
            'users' => $users,
            'roles' => $roles,
            'activeFilters' => $this->getActiveFilters(),
        ]);
    }
}