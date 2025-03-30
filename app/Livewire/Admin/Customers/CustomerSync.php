<?php

namespace App\Livewire\Admin\Customers;

use App\Livewire\Admin\AdminComponent;
use App\Models\Customer;
use App\Services\CustomerSyncService;

class CustomerSync extends AdminComponent
{
    // For customer sync management
    public $syncRunning = false;
    public $syncResults = null;
    public $lastSyncTime = null;
    public $lastSyncStats = null;
    
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
        $this->loadLastSyncInfo();
    }
    
    public function loadLastSyncInfo()
    {
        // Get the most recently synced customer to determine last sync time
        $lastSyncedCustomer = Customer::whereNotNull('last_sync_at')
            ->orderBy('last_sync_at', 'desc')
            ->first();
        
        if ($lastSyncedCustomer && $lastSyncedCustomer->last_sync_at) {
            $this->lastSyncTime = $lastSyncedCustomer->last_sync_at->format('Y-m-d H:i:s');
            
            // Calculate time since last sync
            $timeSinceSync = $lastSyncedCustomer->last_sync_at->diffForHumans();
            
            // Get basic counts about customers
            $totalCustomers = Customer::count() ?: 0;
            $floridaCustomers = Customer::where('home_state', 'Florida')->count() ?: 0;
            $georgiaCustomers = Customer::where('home_state', 'Georgia')->count() ?: 0;
            
            // Get license type distribution
            $licenseTypes = Customer::selectRaw('license_type, COUNT(*) as count')
                ->whereNotNull('license_type')
                ->groupBy('license_type')
                ->pluck('count', 'license_type')
                ->toArray();
            
            // Compile all stats
            $this->lastSyncStats = [
                'total' => $totalCustomers,
                'florida_customers' => $floridaCustomers,
                'georgia_customers' => $georgiaCustomers,
                'license_types' => $licenseTypes,
                'time_since_sync' => $timeSinceSync
            ];
        } else {
            // Handle case where no sync has occurred yet
            $this->lastSyncTime = null;
            $this->lastSyncStats = null;
        }
    }
    
    public function render()
    {
        return view('livewire.admin.customers.customer-sync');
    }
    
    public function runCustomerSync(CustomerSyncService $syncService)
    {
        // Use the central method to authorize this action with specific permission
        $this->authorizeAction('view customers');
        
        if ($this->syncRunning) {
            return;
        }
        
        // Increase PHP execution time limit to 5 minutes
        set_time_limit(300);
        
        $this->syncRunning = true;
        $this->syncResults = null;
        $this->dispatch('message', 'Starting customer data sync...');
        
        try {
            $this->syncResults = $syncService->syncAllCustomers();
            $this->lastSyncTime = now()->format('Y-m-d H:i:s');
            
            // Refresh the last sync stats
            $this->loadLastSyncInfo();
            
            if (!empty($this->syncResults['errors'])) {
                $this->dispatch('error', 'Sync completed with errors: ' . count($this->syncResults['errors']) . ' error(s)');
            } else {
                $this->dispatch('message', 'Customer data sync completed successfully!');
                
                // Notify other components that the customer data has been refreshed
                $this->dispatch('customers-refreshed');
            }
        } catch (\Exception $e) {
            $this->dispatch('error', 'Error during sync: ' . $e->getMessage());
            $this->syncResults = [
                'errors' => [$e->getMessage()],
                'total' => 0,
                'created' => 0,
                'updated' => 0,
                'failed' => 0,
                'skipped' => 0
            ];
        } finally {
            $this->syncRunning = false;
        }
    }
}
