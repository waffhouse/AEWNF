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
    
    public $isLoading = false;
    public $results = null;
    public $error = null;
    
    public $options = [
        'pageIndex' => 0,
        'pageSize' => 1000,
        'date' => null,
    ];
    
    public function mount()
    {
        $this->authorize('sync netsuite sales data');
    }
    
    public function render()
    {
        return view('livewire.admin.sales.sales-sync');
    }
    
    public function syncSales(SalesSyncService $salesSyncService)
    {
        $this->authorize('sync netsuite sales data');
        
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
            $this->options['pageIndex'] = (int) $this->options['pageIndex'];
            $this->options['pageSize'] = (int) $this->options['pageSize'];
            
            $startTime = now();
            $this->results = $salesSyncService->syncSales($this->options);
            $duration = now()->diffInSeconds($startTime);
            
            $this->results['duration'] = "{$duration} seconds";
            
            $this->dispatch('salesSyncCompleted', [
                'success' => true,
                'message' => 'Sales data sync completed successfully.',
            ]);
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
    
    #[On('refreshSalesList')]
    public function refreshList()
    {
        $this->dispatch('refreshSalesList');
    }
}