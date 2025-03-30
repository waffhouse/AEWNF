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
    
    public $startDate;
    public $endDate;
    public $groupBy = 'class'; // Default grouping
    protected $listeners = [
        'salesSyncCompleted' => 'refreshData'
    ];
    
    public function mount()
    {
        // Check if user has permission to view this component
        if (!auth()->user()->hasPermissionTo('sync netsuite sales data')) {
            abort(403, 'You do not have permission to access this page.');
        }
        
        // Initialize date range to current month
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }
    
    public function refreshData()
    {
        // This method will be called when the sales data is synced
        $this->dispatch('refresh');
    }
    
    
    protected function getDateRangeFilter()
    {
        $startDate = null;
        $endDate = null;
        
        if ($this->startDate && $this->endDate) {
            // Force the timezone to match the application timezone
            // to avoid any timezone-related date shifts
            $tz = config('app.timezone');
            
            // Start date at beginning of day (00:00:00)
            $startDate = Carbon::parse($this->startDate, $tz)->startOfDay();
            
            // End date at end of day (23:59:59)
            $endDate = Carbon::parse($this->endDate, $tz)->endOfDay();
        }
        
        return [
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
    }
    
    public function render()
    {
        $dateRange = $this->getDateRangeFilter();
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];
        
        // Start with the base query
        $query = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('inventories', 'sale_items.sku', '=', 'inventories.sku')
            ->select(
                "inventories.{$this->groupBy}", 
                DB::raw('SUM(CASE WHEN sales.type = "Invoice" THEN sale_items.amount ELSE 0 END) as invoice_amount'),
                DB::raw('SUM(CASE WHEN sales.type = "Credit Memo" THEN sale_items.amount ELSE 0 END) as credit_amount'),
                DB::raw('SUM(sale_items.amount) as total_amount'),
                DB::raw('SUM(CASE WHEN sales.type = "Invoice" THEN sale_items.quantity ELSE 0 END) as invoice_quantity'),
                DB::raw('SUM(CASE WHEN sales.type = "Credit Memo" THEN sale_items.quantity ELSE 0 END) as credit_quantity'),
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('COUNT(DISTINCT sales.id) as transaction_count')
            )
            ->whereNotNull("inventories.{$this->groupBy}")
            ->where("inventories.{$this->groupBy}", '!=', '')
            ->groupBy("inventories.{$this->groupBy}");
        
        // Apply date filtering if needed
        if ($startDate && $endDate) {
            $query->whereBetween('sales.date', [$startDate, $endDate]);
        }
        
        // Get data for tables
        $salesData = $query->get();
        
        // Get the top 10 items by sales
        $topQuery = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('inventories', 'sale_items.sku', '=', 'inventories.sku')
            ->select(
                "inventories.{$this->groupBy}",
                DB::raw('SUM(sale_items.amount) as total_amount'),
                DB::raw('SUM(sale_items.quantity) as total_quantity')
            )
            ->whereNotNull("inventories.{$this->groupBy}")
            ->where("inventories.{$this->groupBy}", '!=', '')
            ->groupBy("inventories.{$this->groupBy}")
            ->orderByDesc('total_amount')
            ->limit(10);
        
        // Apply date filtering if needed
        if ($startDate && $endDate) {
            $topQuery->whereBetween('sales.date', [$startDate, $endDate]);
        }
        
        $topSales = $topQuery->get();
        
        // Get top items by quantity
        $topQuantitiesQuery = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('inventories', 'sale_items.sku', '=', 'inventories.sku')
            ->select(
                "inventories.{$this->groupBy}",
                DB::raw('SUM(sale_items.quantity) as total_quantity')
            )
            ->whereNotNull("inventories.{$this->groupBy}")
            ->where("inventories.{$this->groupBy}", '!=', '')
            ->groupBy("inventories.{$this->groupBy}")
            ->orderByDesc('total_quantity')
            ->limit(10);
            
        if ($startDate && $endDate) {
            $topQuantitiesQuery->whereBetween('sales.date', [$startDate, $endDate]);
        }
        
        $topQuantities = $topQuantitiesQuery->get();
        
        // Get unique classes and brands from inventory
        $classOptions = Inventory::distinct('class')
            ->whereNotNull('class')
            ->where('class', '!=', '')
            ->orderBy('class')
            ->pluck('class')
            ->toArray();
            
        $brandOptions = Inventory::distinct('brand')
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->orderBy('brand')
            ->pluck('brand')
            ->toArray();
        
        // Format date range for display
        $dateRangeTitle = $this->formatDateRangeForTitle($startDate, $endDate);
        
        return view('livewire.sales.sales-analytics-dashboard', [
            'salesData' => $salesData,
            'topSales' => $topSales,
            'topQuantities' => $topQuantities,
            'classOptions' => $classOptions,
            'brandOptions' => $brandOptions,
            'dateRangeTitle' => $dateRangeTitle
        ])->layout('layouts.app');
    }
    
    protected function formatDateRangeForTitle($startDate, $endDate)
    {
        if (!$startDate || !$endDate) {
            return 'No Date Range Selected';
        }
        
        $start = $startDate->format('M j, Y');
        $end = $endDate->format('M j, Y');
        
        if ($start === $end) {
            return $start;
        }
        
        return "{$start} - {$end}";
    }
    
}