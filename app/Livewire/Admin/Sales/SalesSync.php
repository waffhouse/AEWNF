<?php

namespace App\Livewire\Admin\Sales;

use App\Services\SalesSyncService;
use App\Traits\AdminAuthorization;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;

class SalesSync extends Component
{
    use AdminAuthorization;
    
    protected $listeners = ['clearData' => 'directClearData'];
    
    public $isLoading = false;
    public $results = null;
    public $error = null;
    public $lastSyncTime = null;
    public $lastSyncStats = null;
    
    public $options = [
        'pageSize' => 1000,
        'date' => null,
    ];
    
    public function mount()
    {
        $this->authorize('sync netsuite sales data');
        $this->loadLastSyncInfo();
    }
    
    /**
     * Load information about the last sync operation
     */
    public function loadLastSyncInfo()
    {
        // Get the most recently synced sale
        $lastSyncedSale = \App\Models\Sale::orderBy('last_synced_at', 'desc')->first();
        
        if ($lastSyncedSale && $lastSyncedSale->last_synced_at) {
            $this->lastSyncTime = $lastSyncedSale->last_synced_at->format('Y-m-d H:i:s');
            
            // Calculate time since last sync
            $timeSinceSync = $lastSyncedSale->last_synced_at->diffForHumans();
            
            // Get basic counts about the sales data
            $totalSales = \App\Models\Sale::count() ?: 0;
            
            // Get counts by transaction type
            $typeStats = \App\Models\Sale::select('type')
                ->selectRaw('COUNT(*) as count')
                ->selectRaw('SUM(total_amount) as total_amount')
                ->groupBy('type')
                ->get()
                ->keyBy('type')
                ->toArray();
            
            // Get count of items
            $totalItems = \App\Models\SaleItem::count() ?: 0;
            
            // Calculate net transaction total (sum of all transaction amounts)
            $netTotal = \App\Models\Sale::sum('total_amount') ?: 0;
            
            // Compile all stats
            $this->lastSyncStats = [
                'total_sales' => $totalSales,
                'total_items' => $totalItems,
                'net_total' => $netTotal,
                'type_stats' => $typeStats,
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
        return view('livewire.admin.sales.sales-sync');
    }
    
    public function syncSales(SalesSyncService $salesSyncService)
    {
        $this->authorize('sync netsuite sales data');
        
        // Increase PHP execution time limit to 5 minutes
        set_time_limit(300);
        
        $this->isLoading = true;
        $this->results = null;
        $this->error = null;
        
        try {
            // Format date if provided
            if (!empty($this->options['date'])) {
                $this->options['date'] = Carbon::parse($this->options['date'])->format('Y-m-d');
            } else {
                unset($this->options['date']);
            }
            
            // Convert string values to integers
            $this->options['pageSize'] = (int) $this->options['pageSize'];
            
            // Process the results consistently
            $syncResults = $salesSyncService->syncSales($this->options);
            $this->results = $syncResults;
            
            // Refresh the last sync stats
            $this->loadLastSyncInfo();
            
            $this->dispatch('salesSyncCompleted', [
                'success' => true,
                'message' => 'Sales data sync completed successfully.',
            ]);
            
            // No automatic refresh - users will refresh manually
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            
            $this->dispatch('salesSyncCompleted', [
                'success' => false,
                'message' => 'Failed to sync sales data: ' . $e->getMessage(),
            ]);
        } finally {
            $this->isLoading = false;
        }
    }
    
    /**
     * Clear all sales data method
     */
    public function directClearData(SalesSyncService $salesSyncService)
    {
        $this->authorize('sync netsuite sales data');
        
        // Increase PHP execution time limit to 5 minutes 
        set_time_limit(300);
        
        $this->isLoading = true;
        $this->results = null;
        $this->error = null;
        
        \Illuminate\Support\Facades\Log::info('Direct clear method triggered');
        
        try {
            $result = $salesSyncService->clearAllSalesData();
            
            if ($result['success']) {
                $this->results = [
                    'total' => $result['sales_deleted'],
                    'created' => 0,
                    'updated' => 0,
                    'deleted' => $result['sales_deleted'],
                    'items_deleted' => $result['items_deleted'],
                    'duration' => $result['duration']
                ];
                
                // Refresh the last sync stats
                $this->loadLastSyncInfo();
                
                $this->dispatch('salesSyncCompleted', [
                    'success' => true,
                    'message' => 'Sales data cleared successfully.',
                ]);
                
                // No automatic refresh - users will refresh manually
            } else {
                $this->error = $result['error'] ?? 'Unknown error clearing sales data';
                $this->dispatch('salesSyncCompleted', [
                    'success' => false,
                    'message' => $this->error,
                ]);
            }
            
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->dispatch('salesSyncCompleted', [
                'success' => false,
                'message' => 'Failed to clear sales data: ' . $e->getMessage(),
            ]);
        } finally {
            $this->isLoading = false;
        }
    }
    
    
    // This method is no longer needed as we dispatch the event directly
    // The SalesList component will handle the event
}