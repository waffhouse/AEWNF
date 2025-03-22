<?php

namespace App\Livewire\Admin\Inventory;

use App\Livewire\Admin\AdminComponent;
use App\Models\Inventory;
use App\Services\InventorySyncService;

class InventorySync extends AdminComponent
{
    // For inventory sync management
    public $syncRunning = false;
    public $syncResults = null;
    public $lastSyncTime = null;
    public $lastSyncStats = null;
    public int $perPage = 10;
    
    /**
     * Define the permissions required for this component
     */
    public function getRequiredPermissions(): array
    {
        return ['sync inventory'];
    }
    
    public function mount()
    {
        parent::mount();
        $this->loadLastSyncInfo();
    }
    
    public function loadLastSyncInfo()
    {
        // Get the most recently synced inventory item to determine last sync time
        $lastSyncedItem = Inventory::orderBy('last_synced_at', 'desc')->first();
        
        if ($lastSyncedItem && $lastSyncedItem->last_synced_at) {
            $this->lastSyncTime = $lastSyncedItem->last_synced_at->format('Y-m-d H:i:s');
            
            // Calculate time since last sync
            $timeSinceSync = $lastSyncedItem->last_synced_at->diffForHumans();
            
            // Get basic counts about the inventory
            $totalItems = Inventory::count() ?: 0;
            $floridaItems = Inventory::availableInFlorida()->count() ?: 0;
            $georgiaItems = Inventory::availableInGeorgia()->count() ?: 0;
            
            // Get stock status counts
            $inStockItems = Inventory::where('quantity', '>', 0)->count() ?: 0;
            $outOfStockItems = Inventory::where(function($query) {
                $query->where('quantity', '<=', 0)->orWhereNull('quantity');
            })->count() ?: 0;
            
            // Compile all stats
            $this->lastSyncStats = [
                'total' => $totalItems,
                'florida_items' => $floridaItems,
                'georgia_items' => $georgiaItems,
                'time_since_sync' => $timeSinceSync,
                'in_stock_items' => $inStockItems,
                'out_of_stock_items' => $outOfStockItems
            ];
        } else {
            // Handle case where no sync has occurred yet
            $this->lastSyncTime = null;
            $this->lastSyncStats = null;
        }
    }
    
    public function render()
    {
        return view('livewire.admin.inventory.inventory-sync');
    }
    
    public function runInventorySync(InventorySyncService $syncService)
    {
        // Use the central method to authorize this action with specific permission
        $this->authorizeAction('sync inventory');
        
        if ($this->syncRunning) {
            return;
        }
        
        $this->syncRunning = true;
        $this->syncResults = null;
        $this->dispatch('message', 'Starting inventory sync process...');
        
        try {
            $this->syncResults = $syncService->syncInventory();
            $this->lastSyncTime = now()->format('Y-m-d H:i:s');
            
            // Refresh the last sync stats
            $this->loadLastSyncInfo();
            
            if (isset($this->syncResults['error'])) {
                $this->dispatch('error', 'Sync failed: ' . $this->syncResults['error']);
            } else {
                $this->dispatch('message', 'Inventory sync completed successfully in ' . $this->syncResults['duration']);
            }
        } catch (\Exception $e) {
            $this->dispatch('error', 'Error during sync: ' . $e->getMessage());
            $this->syncResults = [
                'error' => $e->getMessage(),
                'duration' => 'N/A',
                'total' => 0,
                'created' => 0,
                'updated' => 0,
                'failed' => 0,
                'deleted' => 0
            ];
        } finally {
            $this->syncRunning = false;
        }
    }
}