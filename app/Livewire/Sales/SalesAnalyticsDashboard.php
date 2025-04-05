<?php

namespace App\Livewire\Sales;

use App\Models\Inventory;
use App\Models\SaleItem;
use App\Traits\AdminAuthorization;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SalesAnalyticsDashboard extends Component
{
    use AdminAuthorization;
    
    protected $listeners = [
        'salesSyncCompleted' => 'refreshData'
    ];
    
    public function mount()
    {
        // Check if user has permission to view this component
        if (!auth()->user()->hasPermissionTo('manage orders')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }
    
    public function refreshData()
    {
        // This method will be called when the sales data is synced
        $this->dispatch('refresh');
    }
    
    
    public function render()
    {
        // Extremely simplified version with no queries
        // This makes the dashboard a simple landing page with links
        return view('livewire.sales.sales-analytics-dashboard')
            ->layout('layouts.app');
    }
    
}