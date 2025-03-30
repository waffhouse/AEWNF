<div>
    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Sales Analytics</h1>
            <p class="mt-2 text-sm text-gray-600">
                Analyze sales performance by product class and brand. View key metrics and identify trends.
            </p>
        </div>
        
        <!-- Filters Section -->
        <div class="bg-white shadow-sm rounded-lg mb-6 p-4 border border-gray-200">
            <div class="mb-2">
                <h3 class="text-lg font-semibold text-gray-700">Filters</h3>
                <p class="text-sm text-gray-500">Refine analytics data by date range and grouping method</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Date Range Picker -->
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" 
                        id="startDate" 
                        wire:model.live="startDate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                </div>
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" 
                        id="endDate" 
                        wire:model.live="endDate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                </div>
                <!-- Group By Filter -->
                <div>
                    <label for="groupBy" class="block text-sm font-medium text-gray-700">Group By</label>
                    <select id="groupBy" 
                        wire:model.live="groupBy"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                        <option value="class">Class</option>
                        <option value="brand">Brand</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Sales Trend Chart removed -->
        
        
        <!-- Top Sales Data with Pie Chart -->
        <div class="bg-white shadow-sm rounded-lg mb-6 border border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700">
                    Top 10 {{ $groupBy === 'class' ? 'Classes' : 'Brands' }} by Sales Volume ({{ $dateRangeTitle }})
                </h3>
                <p class="text-sm text-gray-500">
                    Highest performing product {{ $groupBy }}es by total sales amount
                </p>
            </div>
            
            <!-- Grid layout for Pie Chart and Table -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Pie Chart -->
                <div class="p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Distribution by {{ ucfirst($groupBy) }}</h4>
                    
                    <!-- SVG Pie Chart -->
                    <div class="h-[300px] relative">
                        @php
                            $totalSalesAmount = $topSales->sum('total_amount');
                            $totalSalesQuantity = $topSales->sum('total_quantity');
                            
                            // Prepare data for pie chart
                            $pieData = [];
                            $centerX = 150;
                            $centerY = 150;
                            $radius = 120;
                            $startAngle = 0;
                            
                            // Define colors for pie slices
                            $colors = [
                                '#dc2626', '#ea580c', '#d97706', '#ca8a04', '#65a30d', 
                                '#16a34a', '#0891b2', '#2563eb', '#7c3aed', '#c026d3'
                            ];
                            
                            // Calculate percentages and angles
                            foreach ($topSales as $index => $item) {
                                if ($totalSalesAmount != 0) {
                                    $percentage = ($item->total_amount / $totalSalesAmount) * 100;
                                    $angle = ($percentage / 100) * 360;
                                    
                                    $pieData[] = [
                                        'name' => $item->{$groupBy},
                                        'value' => abs($item->total_amount),
                                        'percentage' => $percentage,
                                        'startAngle' => $startAngle,
                                        'endAngle' => $startAngle + $angle,
                                        'color' => $colors[$index % count($colors)]
                                    ];
                                    
                                    $startAngle += $angle;
                                }
                            }
                            
                            // Function to calculate SVG arc path
                            function calculateArc($centerX, $centerY, $radius, $startAngle, $endAngle) {
                                $startAngleRad = deg2rad($startAngle);
                                $endAngleRad = deg2rad($endAngle);
                                
                                $startX = $centerX + $radius * cos($startAngleRad);
                                $startY = $centerY + $radius * sin($startAngleRad);
                                $endX = $centerX + $radius * cos($endAngleRad);
                                $endY = $centerY + $radius * sin($endAngleRad);
                                
                                // Check if the angle is almost a full circle
                                $largeArcFlag = ($endAngle - $startAngle <= 180) ? 0 : 1;
                                
                                return "M {$centerX} {$centerY} L {$startX} {$startY} A {$radius} {$radius} 0 {$largeArcFlag} 1 {$endX} {$endY} Z";
                            }
                        @endphp
                        
                        <svg viewBox="0 0 300 300" class="w-full h-full">
                            <!-- Render pie slices -->
                            @forelse ($pieData as $slice)
                                @php 
                                    $path = calculateArc($centerX, $centerY, $radius, $slice['startAngle'], $slice['endAngle']);
                                    
                                    // Calculate position for label
                                    $midAngle = ($slice['startAngle'] + $slice['endAngle']) / 2;
                                    $midAngleRad = deg2rad($midAngle);
                                    
                                    // Position the label outside the pie
                                    $labelRadius = $radius * 0.85;
                                    $labelX = $centerX + $labelRadius * cos($midAngleRad);
                                    $labelY = $centerY + $labelRadius * sin($midAngleRad);
                                    
                                    // Only show labels for slices with percentage >= 5%
                                    $showLabel = $slice['percentage'] >= 5;
                                @endphp
                                
                                <g class="pie-slice" data-name="{{ $slice['name'] }}" data-value="{{ $slice['value'] }}" data-percentage="{{ round($slice['percentage'], 1) }}%">
                                    <path d="{{ $path }}" fill="{{ $slice['color'] }}" stroke="white" stroke-width="1"
                                          class="cursor-pointer hover:opacity-90 transition-opacity"
                                          data-tippy-content="{{ $slice['name'] }}: ${{ number_format($slice['value'], 2) }} ({{ round($slice['percentage'], 1) }}%)" />
                                          
                                    @if ($showLabel)
                                        <text x="{{ $labelX }}" y="{{ $labelY }}" 
                                              text-anchor="middle" dominant-baseline="middle"
                                              fill="white" font-size="10" font-weight="bold">
                                            {{ round($slice['percentage']) }}%
                                        </text>
                                    @endif
                                </g>
                            @empty
                                <text x="150" y="150" text-anchor="middle" class="text-gray-400">No data available</text>
                            @endforelse
                        </svg>
                    </div>
                    
                    <!-- Legend removed as table now serves as legend -->
                </div>
                
                <!-- Data Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ ucfirst($groupBy) }}</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Qty</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">% of Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($topSales as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color: {{ $colors[$loop->index % count($colors)] }}"></span>
                                            {{ $item->{$groupBy} }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        ${{ number_format(abs($item->total_amount), 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        {{ number_format(abs($item->total_quantity)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                        @if($totalSalesAmount != 0)
                                            {{ round(($item->total_amount / $totalSalesAmount) * 100, 2) }}%
                                        @else
                                            0%
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                        No data available for this time period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Detailed Breakdown -->
        <div class="bg-white shadow-sm rounded-lg mb-6 border border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700">
                    Detailed Breakdown by {{ $groupBy === 'class' ? 'Class' : 'Brand' }}
                </h3>
                <p class="text-sm text-gray-500">
                    Complete breakdown showing invoice amounts, credit amounts, and quantities
                </p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ ucfirst($groupBy) }}</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Amount</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit Amount</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Amount</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Qty</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit Qty</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Qty</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($salesData as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->{$groupBy} }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">
                                    ${{ number_format(abs($item->invoice_amount), 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                                    ${{ number_format(abs($item->credit_amount), 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $item->total_amount >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    ${{ number_format(abs($item->total_amount), 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">
                                    {{ number_format(abs($item->invoice_quantity)) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                                    {{ number_format(abs($item->credit_quantity)) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $item->total_quantity >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    {{ number_format(abs($item->total_quantity)) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($item->transaction_count) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                    No data available for this time period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Top 10 by Quantity -->
        <div class="bg-white shadow-sm rounded-lg mb-6 border border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700">
                    Top 10 {{ $groupBy === 'class' ? 'Classes' : 'Brands' }} by Quantity Sold ({{ $dateRangeTitle }})
                </h3>
                <p class="text-sm text-gray-500">
                    Highest performing product {{ $groupBy }}es by total quantity
                </p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ ucfirst($groupBy) }}</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topQuantities as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->{$groupBy} }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format(abs($item->total_quantity)) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                    No data available for this time period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Info Box -->
        <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">About This Report</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>This report provides insights into sales performance by item class and brand. You can:</p>
                        <ul class="list-disc pl-5 space-y-1 mt-2">
                            <li>Select custom date ranges using the date pickers to analyze specific time periods</li>
                            <li>Switch between class and brand grouping to analyze performance from different angles</li>
                            <li>View detailed tables showing top performers and key metrics</li>
                            <li>Identify top performing classes and brands to inform inventory and marketing decisions</li>
                            <li>View the breakdown of invoices vs. credit memos to understand net sales impact</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>