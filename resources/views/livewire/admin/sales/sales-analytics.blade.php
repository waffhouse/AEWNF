<div>
    <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-200">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-red-600 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Sales Analytics by Class and Brand
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Analyze sales performance by item class and brand. View key metrics and trends to identify top performers.
            </p>
        </div>
        
        <!-- Filters Section -->
        <div class="mb-6 bg-gray-50 p-4 rounded-md">
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
        
        <!-- Sales Chart Section -->
        <div class="mb-6">
            <h4 class="font-medium text-gray-700 mb-3">Top {{ $groupBy === 'class' ? 'Classes' : 'Brands' }} by Sales</h4>
            <div class="bg-white rounded-lg shadow overflow-hidden p-4 border border-gray-200" wire:key="sales-chart-{{ time() }}">
                <div class="text-center p-8 bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p class="mt-2 text-lg font-medium text-gray-600">Charts temporarily disabled</p>
                    <p class="text-gray-500">Please refer to the data tables below for sales information</p>
                </div>
            </div>
        </div>
        
        <!-- Top Sales Data -->
        <div class="mb-6">
            <h4 class="font-medium text-gray-700 mb-3">Top 10 {{ $groupBy === 'class' ? 'Classes' : 'Brands' }} by Sales Volume ({{ $dateRangeTitle }})</h4>
            
            <!-- Data Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
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
                            @php
                                $totalSalesAmount = $topSales->sum('total_amount');
                                $totalSalesQuantity = $topSales->sum('total_quantity');
                            @endphp
                            
                            @forelse($topSales as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->{$groupBy} }}
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
        <div>
            <h4 class="font-medium text-gray-700 mb-3">Detailed Breakdown by {{ $groupBy === 'class' ? 'Class' : 'Brand' }}</h4>
            <div class="bg-white rounded-lg shadow overflow-hidden">
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
        </div>
        
        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 p-4 rounded-md border border-blue-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h5 class="text-sm font-medium text-blue-800">About This Report</h5>
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