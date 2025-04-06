<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\AdminComponent;
use App\Models\User;
use App\Services\UserSyncService;

class UserSync extends AdminComponent
{
    // For user sync management
    public $syncRunning = false;

    public $syncResults = null;

    public $lastSyncTime = null;

    public $lastSyncStats = null;

    /**
     * Define the permissions required for this component
     */
    public function getRequiredPermissions(): array
    {
        return ['access admin dashboard', 'view users'];
    }

    public function mount()
    {
        parent::mount();
        $this->loadLastSyncInfo();
    }

    public function loadLastSyncInfo()
    {
        // Get the most recently refreshed user to determine last sync time
        $lastRefreshedUser = User::whereNotNull('last_refreshed_at')
            ->orderBy('last_refreshed_at', 'desc')
            ->first();

        if ($lastRefreshedUser && $lastRefreshedUser->last_refreshed_at) {
            $this->lastSyncTime = $lastRefreshedUser->last_refreshed_at->format('Y-m-d H:i:s');

            // Calculate time since last sync
            $timeSinceSync = $lastRefreshedUser->last_refreshed_at->diffForHumans();

            // Get basic counts about users
            $totalUsers = User::count() ?: 0;
            $adminUsers = User::role('admin')->count() ?: 0;
            $staffUsers = User::role('staff')->count() ?: 0;
            $floridaCustomers = User::role('florida customer')->count() ?: 0;
            $georgiaCustomers = User::role('georgia customer')->count() ?: 0;

            // Compile all stats
            $this->lastSyncStats = [
                'total' => $totalUsers,
                'admin_users' => $adminUsers,
                'staff_users' => $staffUsers,
                'florida_customers' => $floridaCustomers,
                'georgia_customers' => $georgiaCustomers,
                'time_since_sync' => $timeSinceSync,
            ];
        } else {
            // Handle case where no sync has occurred yet
            $this->lastSyncTime = null;
            $this->lastSyncStats = null;
        }
    }

    public function render()
    {
        return view('livewire.admin.users.user-sync');
    }

    public function runUserSync(UserSyncService $syncService)
    {
        // Use the central method to authorize this action with specific permission
        $this->authorizeAction('view users');

        if ($this->syncRunning) {
            return;
        }

        // Increase PHP execution time limit to 5 minutes
        set_time_limit(300);

        $this->syncRunning = true;
        $this->syncResults = null;
        $this->dispatch('message', 'Starting user data refresh...');

        try {
            $this->syncResults = $syncService->syncUsers();
            $this->lastSyncTime = now()->format('Y-m-d H:i:s');

            // Refresh the last sync stats
            $this->loadLastSyncInfo();

            if (isset($this->syncResults['error'])) {
                $this->dispatch('error', 'Sync failed: '.$this->syncResults['error']);
            } else {
                $this->dispatch('message', 'User data refresh completed successfully in '.$this->syncResults['duration']);

                // Notify other components that the user data has been refreshed
                $this->dispatch('users-refreshed');

                // Switch tab to user list after successful sync
                $this->dispatch('switch-to-user-list');
            }
        } catch (\Exception $e) {
            $this->dispatch('error', 'Error during sync: '.$e->getMessage());
            $this->syncResults = [
                'error' => $e->getMessage(),
                'duration' => 'N/A',
                'total' => 0,
                'refreshed' => 0,
                'failed' => 0,
            ];
        } finally {
            $this->syncRunning = false;
        }
    }
}
