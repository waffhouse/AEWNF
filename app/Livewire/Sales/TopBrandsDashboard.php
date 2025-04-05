<?php

namespace App\Livewire\Sales;

use App\Models\SaleItem;
use App\Traits\AdminAuthorization;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TopBrandsDashboard extends Component
{
    use AdminAuthorization;
    
    public $brandLimit = 10; // Fixed to 10 but keep the property for code consistency
    public $chartData = [];
    public $selectedBrands = [];
    public $availableBrands = [];
    public $startDate;
    public $endDate;
    public $isAllTime = false;
    
    protected $listeners = [
        'salesSyncCompleted' => 'refreshData'
    ];
    
    public function mount()
    {
        // Check if user has permission to view this component
        if (!auth()->user()->hasPermissionTo('manage orders')) {
            abort(403, 'You do not have permission to access this page.');
        }

        // Set default date range to last 7 days
        $now = Carbon::now();
        $this->endDate = $now->format('Y-m-d');
        $this->startDate = $now->copy()->subDays(7)->format('Y-m-d');
        
        // Load brands for filter
        $this->loadAvailableBrands();
        
        // Initialize chart data
        $this->refreshData();
    }
    
    // We're removing the timeframe dropdown, so these methods are no longer needed
    
    public function updatedStartDate()
    {
        $this->refreshData();
        $this->dispatch('brandDataUpdated');
    }
    
    public function updatedEndDate()
    {
        $this->refreshData();
        $this->dispatch('brandDataUpdated');
    }
    
    public function addBrand($brand)
    {
        if (!in_array($brand, $this->selectedBrands)) {
            $this->selectedBrands[] = $brand;
            $this->refreshData();
            $this->dispatch('brandDataUpdated');
        }
    }
    
    public function removeBrand($brand)
    {
        $this->selectedBrands = array_values(array_filter($this->selectedBrands, function($b) use ($brand) {
            return $b !== $brand;
        }));
        $this->refreshData();
        $this->dispatch('brandDataUpdated');
    }
    
    public function updatedSelectedBrands()
    {
        $this->refreshData();
        $this->dispatch('brandDataUpdated');
    }
    
    // Brand limit is now fixed at 10
    
    public function loadAvailableBrands()
    {
        // Get top 50 brands to populate filter
        $this->availableBrands = SaleItem::select('inventories.brand')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('inventories', 'sale_items.sku', '=', 'inventories.sku')
            ->whereNotNull('inventories.brand')
            ->where('inventories.brand', '!=', '')
            ->groupBy('inventories.brand')
            ->orderByRaw('SUM(ABS(sale_items.amount)) DESC')
            ->limit(50)
            ->pluck('brand')
            ->toArray();
    }
    
    public function refreshData()
    {
        // Prepare time series data for top brands
        $this->prepareTopBrandsData();
        
        // Prepare data for comparative brand chart
        $this->prepareComparativeBrandData();
    }
    
    private function prepareTopBrandsData()
    {
        // Handle empty sales data
        $brandCount = \App\Models\Inventory::whereNotNull('brand')
            ->where('brand', '!=', '')
            ->count();
            
        if ($brandCount == 0) {
            $this->chartData['topBrandsOverTime'] = [
                'labels' => [],
                'datasets' => []
            ];
            return;
        }
        
        // Determine if we should group by day or month based on date range
        $useDaily = false;
        $dateFormat = 'Y-m'; // Default to monthly format
        $displayFormat = 'M Y'; // Default display format
        
        if (!$this->isAllTime && $this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate);
            $end = Carbon::parse($this->endDate);
            $daysDiff = $start->diffInDays($end);
            
            // If the date range is 31 days or less, use daily grouping
            if ($daysDiff <= 31) {
                $useDaily = true;
                $dateFormat = 'Y-m-d';
                $displayFormat = 'M d';
            }
        }
        
        $query = SaleItem::select(
                'inventories.brand',
                DB::raw("substr(sales.date, 1, " . ($useDaily ? 10 : 7) . ") as time_period"),
                DB::raw('SUM(ABS(sale_items.amount)) as total_sales')
            )
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('inventories', 'sale_items.sku', '=', 'inventories.sku')
            ->whereNotNull('inventories.brand')
            ->where('inventories.brand', '!=', '');
            
        // Apply date filters if not all time
        if (!$this->isAllTime) {
            if ($this->startDate) {
                $query->where('sales.date', '>=', $this->startDate);
            }
            if ($this->endDate) {
                $query->where('sales.date', '<=', $this->endDate);
            }
        }
        
        // Apply brand filter if any brands are selected
        if (!empty($this->selectedBrands)) {
            $query->whereIn('inventories.brand', $this->selectedBrands);
        }
        
        $query->groupBy('inventories.brand', 'time_period')
            ->orderBy('time_period');
            
        $salesData = $query->get();
        
        // Get top brands by total sales
        $topBrands = $salesData->groupBy('brand')
            ->map(function ($items) {
                return $items->sum('total_sales');
            })
            ->sortDesc()
            ->take($this->brandLimit)
            ->keys()
            ->toArray();
            
        // If we have selected brands, use those instead
        if (!empty($this->selectedBrands)) {
            $topBrands = $this->selectedBrands;
        }
        
        // Group data by time period (day or month) to create time series
        $timeSeriesData = $salesData->groupBy('time_period')->map(function ($items) {
            return $items->groupBy('brand')->map(function ($brandItems) {
                return $brandItems->sum('total_sales');
            });
        })->sortKeys();
        
        // Make sure we have all time periods in range
        if (!$this->isAllTime && $this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate);
            $end = Carbon::parse($this->endDate);
            
            $allPeriods = [];
            $current = $start->copy();
            
            // For daily data, step through each day
            if ($useDaily) {
                while ($current->lte($end)) {
                    $periodKey = $current->format($dateFormat);
                    $allPeriods[$periodKey] = $timeSeriesData[$periodKey] ?? collect();
                    $current->addDay();
                }
            } else {
                // For monthly data, step through each month
                $current = $start->copy()->startOfMonth();
                $end = $end->copy()->startOfMonth();
                
                while ($current->lte($end)) {
                    $periodKey = $current->format($dateFormat);
                    $allPeriods[$periodKey] = $timeSeriesData[$periodKey] ?? collect();
                    $current->addMonth();
                }
            }
            
            $timeSeriesData = collect($allPeriods);
        }
        
        // Format time period labels for display
        $formattedLabels = collect($timeSeriesData->keys())->map(function($period) use ($dateFormat, $displayFormat) {
            // Parse format and convert to display format
            try {
                return Carbon::createFromFormat($dateFormat, $period)->format($displayFormat);
            } catch (\Exception $e) {
                return $period;
            }
        })->toArray();
        
        // Build chart data structure
        $labels = $formattedLabels;
        $datasets = [];
        
        foreach ($topBrands as $brand) {
            $data = $timeSeriesData->map(function ($period) use ($brand) {
                return isset($period[$brand]) ? round($period[$brand], 2) : 0;
            })->values()->toArray();
            
            $datasets[] = [
                'label' => $brand,
                'data' => $data,
            ];
        }
        
        $this->chartData['topBrandsOverTime'] = [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }
    
    private function prepareComparativeBrandData()
    {
        // Handle empty sales data
        $saleCount = \App\Models\Sale::count();
        if ($saleCount == 0) {
            $this->chartData['brandComparison'] = [
                'labels' => [],
                'datasets' => [
                    ['type' => 'bar', 'label' => 'Sales Amount ($)', 'data' => [], 'yAxisID' => 'y'],
                    ['type' => 'line', 'label' => 'Units Sold', 'data' => [], 'yAxisID' => 'y1'],
                ]
            ];
            return;
        }
        
        $query = SaleItem::select(
                'inventories.brand',
                DB::raw('SUM(ABS(sale_items.amount)) as total_sales'),
                DB::raw('SUM(ABS(sale_items.quantity)) as total_units')
            )
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('inventories', 'sale_items.sku', '=', 'inventories.sku')
            ->whereNotNull('inventories.brand')
            ->where('inventories.brand', '!=', '');
            
        // Apply date filters if not all time
        if (!$this->isAllTime) {
            if ($this->startDate) {
                $query->where('sales.date', '>=', $this->startDate);
            }
            if ($this->endDate) {
                $query->where('sales.date', '<=', $this->endDate);
            }
        }
        
        // Apply brand filter if any brands are selected
        if (!empty($this->selectedBrands)) {
            $query->whereIn('inventories.brand', $this->selectedBrands);
        }
        
        $query->groupBy('inventories.brand')
            ->orderByRaw('SUM(ABS(sale_items.amount)) DESC')
            ->limit($this->brandLimit);
            
        $brandData = $query->get();
        
        // Build chart data for comparison
        $this->chartData['brandComparison'] = [
            'labels' => $brandData->pluck('brand')->toArray(),
            'datasets' => [
                [
                    'type' => 'bar',
                    'label' => 'Sales Amount ($)',
                    'data' => $brandData->pluck('total_sales')->map(function($val) { 
                        return round($val, 2); 
                    })->toArray(),
                    'yAxisID' => 'y',
                ],
                [
                    'type' => 'line',
                    'label' => 'Units Sold',
                    'data' => $brandData->pluck('total_units')->map(function($val) { 
                        return round($val, 2); 
                    })->toArray(),
                    'yAxisID' => 'y1',
                ],
            ],
        ];
    }
    
    public function render()
    {
        return view('livewire.sales.top-brands-dashboard')
            ->layout('layouts.app');
    }
}