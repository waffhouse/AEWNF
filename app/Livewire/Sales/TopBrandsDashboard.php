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
    
    public $displayLimit = 10; // Display limit for performance
    public $chartData = [
        'brands' => [
            'timeSeriesData' => [],
            'comparisonData' => []
        ],
        'classes' => [
            'timeSeriesData' => [],
            'comparisonData' => []
        ]
    ];
    public $selectedBrands = [];
    public $availableBrands = [];
    public $selectedClasses = [];
    public $availableClasses = [];
    public $startDate;
    public $endDate;
    public $isAllTime = false;
    public $viewMode = 'brands'; // 'brands' or 'classes'
    
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
        
        // Initialize filters data
        $this->loadAvailableBrands();
        $this->loadAvailableClasses();
        
        // Initialize chart data for both modes
        $this->prepareTopBrandsData();
        $this->prepareComparativeBrandData();
        $this->prepareTopClassesData();
        $this->prepareComparativeClassData();
    }
    
    public function updatedViewMode()
    {
        // Make sure we load the appropriate data based on view mode
        if ($this->viewMode === 'classes' && empty($this->availableClasses)) {
            $this->loadAvailableClasses();
        } else if ($this->viewMode === 'brands' && empty($this->availableBrands)) {
            $this->loadAvailableBrands();
        }
        
        // The chartData will be refreshed after the mode change
        $this->refreshData();
        
        // Notify the front-end that the view mode changed
        $this->dispatch('viewModeChanged', ['mode' => $this->viewMode]);
    }
    
    // We're removing the timeframe dropdown, so these methods are no longer needed
    
    public function updatedStartDate()
    {
        $this->refreshData();
        $this->dispatch('viewModeChanged', ['mode' => $this->viewMode]);
    }
    
    public function updatedEndDate()
    {
        $this->refreshData();
        $this->dispatch('viewModeChanged', ['mode' => $this->viewMode]);
    }
    
    public function addBrand($brand)
    {
        if (!in_array($brand, $this->selectedBrands)) {
            $this->selectedBrands[] = $brand;
            $this->refreshData();
            $this->dispatch('viewModeChanged', ['mode' => $this->viewMode]);
        }
    }
    
    public function removeBrand($brand)
    {
        $this->selectedBrands = array_values(array_filter($this->selectedBrands, function($b) use ($brand) {
            return $b !== $brand;
        }));
        $this->refreshData();
        $this->dispatch('viewModeChanged', ['mode' => $this->viewMode]);
    }
    
    public function updatedSelectedBrands()
    {
        $this->refreshData();
        $this->dispatch('viewModeChanged', ['mode' => $this->viewMode]);
    }
    
    public function addClass($class)
    {
        if (!in_array($class, $this->selectedClasses)) {
            $this->selectedClasses[] = $class;
            $this->refreshData();
            $this->dispatch('viewModeChanged', ['mode' => $this->viewMode]);
        }
    }
    
    public function removeClass($class)
    {
        $this->selectedClasses = array_values(array_filter($this->selectedClasses, function($c) use ($class) {
            return $c !== $class;
        }));
        $this->refreshData();
        $this->dispatch('viewModeChanged', ['mode' => $this->viewMode]);
    }
    
    public function updatedSelectedClasses()
    {
        $this->refreshData();
        $this->dispatch('viewModeChanged', ['mode' => $this->viewMode]);
    }
    
    public function loadAvailableBrands()
    {
        // Get all brands to populate filter
        $this->availableBrands = SaleItem::select('inventories.brand')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('inventories', 'sale_items.sku', '=', 'inventories.sku')
            ->whereNotNull('inventories.brand')
            ->where('inventories.brand', '!=', '')
            ->groupBy('inventories.brand')
            ->orderByRaw('SUM(ABS(sale_items.amount)) DESC')
            ->pluck('brand')
            ->toArray();
    }
    
    public function loadAvailableClasses()
    {
        // Get all classes to populate filter - using the raw query to debug
        $classesQuery = SaleItem::select('inventories.class')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('inventories', 'sale_items.sku', '=', 'inventories.sku')
            ->whereNotNull('inventories.class')
            ->where('inventories.class', '!=', '')
            ->groupBy('inventories.class')
            ->orderByRaw('SUM(ABS(sale_items.amount)) DESC');
            
        // Log the generated SQL for debugging
        \Log::info('Classes query: ' . $classesQuery->toSql());
        
        // Get the actual values
        $this->availableClasses = $classesQuery->pluck('class')->toArray();
        
        // If we couldn't find any classes via sales, try getting them directly from inventory
        if (empty($this->availableClasses)) {
            $this->availableClasses = \App\Models\Inventory::whereNotNull('class')
                ->where('class', '!=', '')
                ->groupBy('class')
                ->orderBy('class')
                ->pluck('class')
                ->toArray();
        }
        
        // Log the results
        \Log::info('Found ' . count($this->availableClasses) . ' classes');
    }
    
    public function refreshData()
    {
        if ($this->viewMode === 'brands') {
            // Prepare time series data for top brands
            $this->prepareTopBrandsData();
            
            // Prepare data for comparative brand chart
            $this->prepareComparativeBrandData();
        } else {
            // Make sure we have classes data available
            if (empty($this->availableClasses)) {
                $this->loadAvailableClasses();
            }
            
            // Prepare time series data for top classes
            $this->prepareTopClassesData();
            
            // Prepare data for comparative class chart
            $this->prepareComparativeClassData();
        }
    }
    
    private function prepareTopClassesData()
    {
        // Handle empty sales data
        $classCount = \App\Models\Inventory::whereNotNull('class')
            ->where('class', '!=', '')
            ->count();
            
        if ($classCount == 0) {
            $this->chartData['classes']['timeSeriesData'] = [
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
                'inventories.class',
                DB::raw("substr(sales.date, 1, " . ($useDaily ? 10 : 7) . ") as time_period"),
                DB::raw('SUM(ABS(sale_items.amount)) as total_sales')
            )
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('inventories', 'sale_items.sku', '=', 'inventories.sku')
            ->whereNotNull('inventories.class')
            ->where('inventories.class', '!=', '');
            
        // Apply date filters if not all time
        if (!$this->isAllTime) {
            if ($this->startDate) {
                $query->where('sales.date', '>=', $this->startDate);
            }
            if ($this->endDate) {
                $query->where('sales.date', '<=', $this->endDate);
            }
        }
        
        // Apply class filter if any classes are selected
        if (!empty($this->selectedClasses)) {
            $query->whereIn('inventories.class', $this->selectedClasses);
        }
        
        $query->groupBy('inventories.class', 'time_period')
            ->orderBy('time_period');
            
        $salesData = $query->get();
        
        // Get classes to display - either selected classes or top by sales
        $classesToDisplay = [];
        
        if (!empty($this->selectedClasses)) {
            $classesToDisplay = $this->selectedClasses;
        } else {
            $classesToDisplay = $salesData->groupBy('class')
                ->map(function ($items) {
                    return $items->sum('total_sales');
                })
                ->sortDesc()
                ->take($this->displayLimit)
                ->keys()
                ->toArray();
        }
        
        // Group data by time period (day or month) to create time series
        $timeSeriesData = $salesData->groupBy('time_period')->map(function ($items) {
            return $items->groupBy('class')->map(function ($classItems) {
                return $classItems->sum('total_sales');
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
        
        foreach ($classesToDisplay as $class) {
            $data = $timeSeriesData->map(function ($period) use ($class) {
                return isset($period[$class]) ? round($period[$class], 2) : 0;
            })->values()->toArray();
            
            $datasets[] = [
                'label' => $class,
                'data' => $data,
            ];
        }
        
        $this->chartData['classes']['timeSeriesData'] = [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }
    
    private function prepareTopBrandsData()
    {
        // Handle empty sales data
        $brandCount = \App\Models\Inventory::whereNotNull('brand')
            ->where('brand', '!=', '')
            ->count();
            
        if ($brandCount == 0) {
            $this->chartData['brands']['timeSeriesData'] = [
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
        
        // Get brands to display - either selected brands or top by sales
        $brandsToDisplay = [];
        
        if (!empty($this->selectedBrands)) {
            $brandsToDisplay = $this->selectedBrands;
        } else {
            $brandsToDisplay = $salesData->groupBy('brand')
                ->map(function ($items) {
                    return $items->sum('total_sales');
                })
                ->sortDesc()
                ->take($this->displayLimit)
                ->keys()
                ->toArray();
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
        
        foreach ($brandsToDisplay as $brand) {
            $data = $timeSeriesData->map(function ($period) use ($brand) {
                return isset($period[$brand]) ? round($period[$brand], 2) : 0;
            })->values()->toArray();
            
            $datasets[] = [
                'label' => $brand,
                'data' => $data,
            ];
        }
        
        $this->chartData['brands']['timeSeriesData'] = [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }
    
    private function prepareComparativeBrandData()
    {
        // Handle empty sales data
        $saleCount = \App\Models\Sale::count();
        if ($saleCount == 0) {
            $this->chartData['brands']['comparisonData'] = [
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
        
        if (empty($this->selectedBrands)) {
            $query->limit($this->displayLimit);
        }
        
        $query->groupBy('inventories.brand')
            ->orderByRaw('SUM(ABS(sale_items.amount)) DESC');
            
        $brandData = $query->get();
        
        // Build chart data for comparison
        $this->chartData['brands']['comparisonData'] = [
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
    
    private function prepareComparativeClassData()
    {
        // Handle empty sales data
        $saleCount = \App\Models\Sale::count();
        if ($saleCount == 0) {
            $this->chartData['classes']['comparisonData'] = [
                'labels' => [],
                'datasets' => [
                    ['type' => 'bar', 'label' => 'Sales Amount ($)', 'data' => [], 'yAxisID' => 'y'],
                    ['type' => 'line', 'label' => 'Units Sold', 'data' => [], 'yAxisID' => 'y1'],
                ]
            ];
            return;
        }
        
        $query = SaleItem::select(
                'inventories.class',
                DB::raw('SUM(ABS(sale_items.amount)) as total_sales'),
                DB::raw('SUM(ABS(sale_items.quantity)) as total_units')
            )
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('inventories', 'sale_items.sku', '=', 'inventories.sku')
            ->whereNotNull('inventories.class')
            ->where('inventories.class', '!=', '');
            
        // Apply date filters if not all time
        if (!$this->isAllTime) {
            if ($this->startDate) {
                $query->where('sales.date', '>=', $this->startDate);
            }
            if ($this->endDate) {
                $query->where('sales.date', '<=', $this->endDate);
            }
        }
        
        // Apply class filter if any classes are selected
        if (!empty($this->selectedClasses)) {
            $query->whereIn('inventories.class', $this->selectedClasses);
        }
        
        if (empty($this->selectedClasses)) {
            $query->limit($this->displayLimit);
        }
        
        $query->groupBy('inventories.class')
            ->orderByRaw('SUM(ABS(sale_items.amount)) DESC');
            
        $classData = $query->get();
        
        // Build chart data for comparison
        $this->chartData['classes']['comparisonData'] = [
            'labels' => $classData->pluck('class')->toArray(),
            'datasets' => [
                [
                    'type' => 'bar',
                    'label' => 'Sales Amount ($)',
                    'data' => $classData->pluck('total_sales')->map(function($val) { 
                        return round($val, 2); 
                    })->toArray(),
                    'yAxisID' => 'y',
                ],
                [
                    'type' => 'line',
                    'label' => 'Units Sold',
                    'data' => $classData->pluck('total_units')->map(function($val) { 
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