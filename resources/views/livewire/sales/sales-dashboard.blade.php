@php
    $filterLabels = [];
    if (!empty($search)) $filterLabels[] = 'Search: ' . $search;
    if (!empty($filters['type'])) $filterLabels[] = 'Type: ' . $filters['type'];
    if (!empty($filters['customer'])) $filterLabels[] = 'Customer: ' . $filters['customer'];
    if (!empty($filters['date_range']['start']) || !empty($filters['date_range']['end'])) $filterLabels[] = 'Date';
@endphp
<div 
    x-data="{ 
        mobileFilterExpanded: false
    }"
    class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
    <div class="md:flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900">{{ $canViewAllSales ? 'All Sales History' : 'My Sales History' }}</h2>
        <div class="flex items-center space-x-4">
            <!-- Desktop Search -->
            <div class="hidden md:flex relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search transactions..." 
                    class="pl-9 rounded-md border-gray-300 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50 w-full"
                >
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>
            </div>
            
            <!-- Clear button (desktop only) -->
            <div class="hidden md:block">
                <button wire:click="resetFilters" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                    Clear
                </button>
            </div>
            
            <!-- Mobile filter indicator (showing active filters count) -->
            <div class="md:hidden w-full flex justify-end">
                <button 
                    @click="mobileFilterExpanded = true" 
                    class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-200 rounded-md text-sm text-gray-700"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 {{ count($filterLabels) > 0 ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <div class="flex items-center">
                        <span>Filter</span>
                        @if(count($filterLabels) > 0)
                            <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-800 rounded-full text-xs">
                                {{ count($filterLabels) }}
                            </span>
                            @if(!empty($search))
                                <span class="ml-2 overflow-hidden overflow-ellipsis max-w-[80px] whitespace-nowrap text-xs">"{{ $search }}"</span>
                            @endif
                        @endif
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Desktop Filters (hidden on small screens) -->
    <div class="hidden md:block bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="md:flex space-y-4 md:space-y-0 md:space-x-4">
                <!-- Transaction Type Filter -->
                <div class="w-full md:w-1/3">
                    <label for="type-desktop" class="block text-sm font-medium text-gray-700">Transaction Type</label>
                    <select id="type-desktop" wire:model.live="filters.type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md">
                        <option value="">All Types</option>
                        @foreach($transactionTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Customer Filter (Admin only) -->
                @if($canViewAllSales)
                <div class="w-full md:w-1/3">
                    <label for="customer-desktop" class="block text-sm font-medium text-gray-700">Customer</label>
                    <select id="customer-desktop" wire:model.live="filters.customer" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer['id'] }}">{{ $customer['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <!-- Date Range Filter -->
                <div class="w-full md:w-1/3">
                    <label class="block text-sm font-medium text-gray-700">Date Range</label>
                    <div class="flex space-x-2 mt-1">
                        <input 
                            type="date" 
                            id="date_range_start"
                            wire:model.live="filters.date_range.start" 
                            class="block w-full border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md"
                        >
                        <span class="self-center">to</span>
                        <input 
                            type="date" 
                            id="date_range_end"
                            wire:model.live="filters.date_range.end" 
                            class="block w-full border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Active Filter Indicators -->
            <div class="mt-4 flex flex-col sm:flex-row gap-2 items-center justify-between">
                <!-- Filter Status Indicator -->
                @php
                    $activeFilterCount = 0;
                    if (!empty($search)) $activeFilterCount++;
                    if (!empty($filters['type'])) $activeFilterCount++;
                    if (!empty($filters['customer'])) $activeFilterCount++;
                    if (!empty($filters['date_range']['start']) || !empty($filters['date_range']['end'])) $activeFilterCount++;
                    
                    $hasActiveFilters = $activeFilterCount > 0;
                @endphp
                <div class="text-sm text-gray-500 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 {{ $hasActiveFilters ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    @if($hasActiveFilters)
                        <span>{{ $activeFilterCount }} filter{{ $activeFilterCount !== 1 ? 's' : '' }} currently applied</span>
                    @else
                        <span>No filters currently applied</span>
                    @endif
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-2">
                    @if($hasActiveFilters)
                        <button 
                            type="button"
                            wire:click="resetFilters"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-0 flex items-center"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset Filters
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Active Filters Display (Only on desktop when filters are applied) -->
    @if($hasActiveFilters)
    <div class="hidden md:flex flex-wrap gap-2 mb-4">
        <span class="text-sm font-medium text-gray-700">Active Filters:</span>
        @if(!empty($search))
            <button 
                type="button" 
                wire:click="$set('search', '')" 
                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors focus:outline-none"
                aria-label="Remove search filter"
            >
                Search: {{ $search }}
                <span class="ml-1 text-blue-500">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            </button>
        @endif
        
        @if(!empty($filters['type']))
            <button 
                type="button" 
                wire:click="$set('filters.type', '')" 
                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 hover:bg-green-200 transition-colors focus:outline-none"
                aria-label="Remove type filter"
            >
                Type: {{ $filters['type'] }}
                <span class="ml-1 text-green-500">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            </button>
        @endif
        
        @if(!empty($filters['customer']))
            <button 
                type="button" 
                wire:click="$set('filters.customer', '')" 
                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 hover:bg-purple-200 transition-colors focus:outline-none"
                aria-label="Remove customer filter"
            >
                Customer: {{ $filters['customer'] }}
                <span class="ml-1 text-purple-500">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            </button>
        @endif
        
        @if(!empty($filters['date_range']['start']) || !empty($filters['date_range']['end']))
            <button 
                type="button" 
                wire:click="clearDateFilter" 
                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 hover:bg-yellow-200 transition-colors focus:outline-none"
                aria-label="Remove date filter"
            >
                Date: {{ !empty($filters['date_range']['start']) ? \Carbon\Carbon::parse($filters['date_range']['start'])->format('m/d/Y') : 'Any' }} 
                to 
                {{ !empty($filters['date_range']['end']) ? \Carbon\Carbon::parse($filters['date_range']['end'])->format('m/d/Y') : 'Any' }}
                <span class="ml-1 text-yellow-500">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            </button>
        @endif
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($summary as $item)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ $item->type }}</h3>
                    <div class="mt-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Count:</span>
                            <span class="text-lg font-semibold">{{ $item->count }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-sm text-gray-500">Total:</span>
                            <span class="text-lg font-semibold">${{ number_format($item->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg col-span-full">
                <div class="p-4 text-center text-gray-500">
                    No transaction data available.
                </div>
            </div>
        @endforelse
    </div>

    <!-- Sales Table (Desktop) -->
    <div class="hidden md:block bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('tran_id')">
                                Transaction ID
                                @if($sortField == 'tran_id')
                                    <span class="ml-1">
                                        @if($sortDirection == 'asc') ↑ @else ↓ @endif
                                    </span>
                                @endif
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('type')">
                                Type
                                @if($sortField == 'type')
                                    <span class="ml-1">
                                        @if($sortDirection == 'asc') ↑ @else ↓ @endif
                                    </span>
                                @endif
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('date')">
                                Date
                                @if($sortField == 'date')
                                    <span class="ml-1">
                                        @if($sortDirection == 'asc') ↑ @else ↓ @endif
                                    </span>
                                @endif
                            </th>
                            @if($canViewAllSales)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('customer_name')">
                                Customer
                                @if($sortField == 'customer_name')
                                    <span class="ml-1">
                                        @if($sortDirection == 'asc') ↑ @else ↓ @endif
                                    </span>
                                @endif
                            </th>
                            @endif
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('total_amount')">
                                Amount
                                @if($sortField == 'total_amount')
                                    <span class="ml-1">
                                        @if($sortDirection == 'asc') ↑ @else ↓ @endif
                                    </span>
                                @endif
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($sales as $sale)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $sale->tran_id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $sale->type }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $sale->date->format('m/d/Y') }}
                                </td>
                                @if($canViewAllSales)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $sale->customer_name }} ({{ $sale->entity_id }})
                                </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    ${{ number_format($sale->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('sales.invoice', $sale->id) }}" target="_blank" class="text-red-600 hover:text-red-900 inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span class="ml-1">PDF</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canViewAllSales ? 6 : 5 }}" class="px-6 py-4 text-center text-gray-500">
                                    No transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Infinite Scroll Trigger -->
            @if($hasMorePages)
                <div class="text-center py-4" x-data="{}" x-intersect="$wire.loadMoreSales()">
                    <div wire:loading class="inline-block">
                        <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            @endif
            
            <!-- Total Count Display -->
            <div class="mt-4 text-sm text-gray-500 text-right">
                Showing {{ count($sales) }} of {{ $totalCount }} transactions
            </div>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="md:hidden space-y-4">
        @forelse($sales as $sale)
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-3 sm:px-6 flex justify-between items-center bg-gray-50">
                    <h3 class="text-sm leading-6 font-medium text-gray-900">
                        {{ $sale->tran_id }}
                    </h3>
                    <span class="px-2 py-1 text-xs font-medium rounded-md {{ $sale->type == 'Invoice' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                        {{ $sale->type }}
                    </span>
                </div>
                <div class="border-t border-gray-200 px-4 py-3">
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <div class="text-gray-500">Date:</div>
                        <div class="text-gray-900">{{ $sale->date->format('m/d/Y') }}</div>
                        
                        @if($canViewAllSales)
                        <div class="text-gray-500">Customer:</div>
                        <div class="text-gray-900">{{ $sale->customer_name }}</div>
                        
                        <div class="text-gray-500">Customer ID:</div>
                        <div class="text-gray-900">{{ $sale->entity_id }}</div>
                        @endif
                        
                        <div class="text-gray-500">Total:</div>
                        <div class="text-gray-900 font-semibold">${{ number_format($sale->total_amount, 2) }}</div>
                    </div>
                </div>
                <div class="border-t border-gray-200 px-4 py-3 flex justify-center">
                    <a href="{{ route('sales.invoice', $sale->id) }}" target="_blank" class="inline-flex items-center px-3 py-1 border border-red-300 text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Invoice
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-center text-gray-500">
                    No transactions found.
                </div>
            </div>
        @endforelse
        
        <!-- Mobile Infinite Scroll Trigger -->
        @if($hasMorePages)
            <div class="text-center py-4" x-data="{}" x-intersect="$wire.loadMoreSales()">
                <div wire:loading class="inline-block">
                    <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        @endif
        
        <!-- Mobile Total Count Display -->
        <div class="text-sm text-gray-500 text-center">
            Showing {{ count($sales) }} of {{ $totalCount }} transactions
        </div>
    </div>

    <!-- Sale Details Modal -->
    @if($viewingSale)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Transaction Details
                            </h3>
                            <div class="mt-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Transaction ID:</p>
                                        <p class="font-medium">{{ $viewingSale->tran_id }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Type:</p>
                                        <p class="font-medium">{{ $viewingSale->type }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Date:</p>
                                        <p class="font-medium">{{ $viewingSale->date->format('m/d/Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Customer:</p>
                                        <p class="font-medium">{{ $viewingSale->customer_name }} ({{ $viewingSale->entity_id }})</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Total Amount:</p>
                                        <p class="font-medium">${{ number_format($viewingSale->total_amount, 2) }}</p>
                                    </div>
                                </div>
                                
                                <h4 class="text-md font-medium text-gray-900 mt-4 mb-2">Line Items</h4>
                                <div class="bg-white shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($viewingSale->items as $item)
                                                <tr>
                                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $item->sku }}</td>
                                                    <td class="px-4 py-2 text-sm text-gray-500">{{ $item->item_description }}</td>
                                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 text-right">{{ $item->quantity }}</td>
                                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 text-right">${{ number_format($item->amount, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <a href="{{ route('sales.invoice', $viewingSale->id) }}" target="_blank" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        View Invoice
                    </a>
                    <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Scroll to Top is integrated in the sticky mobile bar -->
    
    <!-- Sticky Mobile Filter Bar (visible only on small screens) -->
    <div 
        class="md:hidden fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-gray-200 shadow-lg"
        style="transition: transform 0.3s ease-in-out;"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform translate-y-full"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-full"
    >
        <!-- Always visible search bar at bottom -->
        <div class="px-4 py-2 border-b border-gray-200">
            <form wire:submit.prevent="submitSearch" class="flex space-x-2">
                <div class="relative flex-grow">
                    <input 
                        type="text" 
                        wire:model="search" 
                        placeholder="Search transactions..." 
                        class="pl-9 rounded-md border-gray-300 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50 w-full h-10 text-sm"
                    >
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                </div>
                <button type="submit" class="inline-flex items-center px-3 py-2 h-10 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-300 disabled:opacity-25 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                
                <!-- Scroll to top button -->
                <button 
                    type="button" 
                    @click="window.scrollTo({top: 0, behavior: 'smooth'})" 
                    class="inline-flex items-center h-10 px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-sm text-gray-700"
                    aria-label="Scroll to top"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                </button>
                
                <!-- Filter toggle button -->
                <button 
                    @click="mobileFilterExpanded = !mobileFilterExpanded" 
                    type="button"
                    class="inline-flex items-center h-10 px-3 py-2 bg-gray-100 border border-gray-200 rounded-md text-sm text-gray-700"
                    :aria-expanded="mobileFilterExpanded"
                    aria-controls="mobile-filter-area"
                >
                    <span class="mr-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ count($filterLabels) > 0 ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                    </span>
                    @if(count($filterLabels) > 0)
                        <span class="px-1.5 py-0.5 bg-red-100 text-red-800 rounded-full text-xs">
                            {{ count($filterLabels) }}
                        </span>
                    @endif
                </button>
            </form>
            
            <!-- Active Filters Badges -->
            @if(count($filterLabels) > 0)
                <div class="flex flex-wrap gap-1 mt-2 pb-1 overflow-x-auto">
                    @if(!empty($search))
                        <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 whitespace-nowrap">
                            Search: {{ $search }}
                            <button wire:click="$set('search', '')" class="ml-1 text-blue-500">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif
                    
                    @if(!empty($filters['type']))
                        <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap">
                            Type: {{ $filters['type'] }}
                            <button wire:click="$set('filters.type', '')" class="ml-1 text-green-500">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif
                    
                    @if(!empty($filters['customer']))
                        <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 whitespace-nowrap">
                            Customer: {{ $filters['customer'] }}
                            <button wire:click="$set('filters.customer', '')" class="ml-1 text-purple-500">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif
                    
                    @if(!empty($filters['date_range']['start']) || !empty($filters['date_range']['end']))
                        <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 whitespace-nowrap">
                            Date: 
                            {{ !empty($filters['date_range']['start']) ? \Carbon\Carbon::parse($filters['date_range']['start'])->format('m/d/Y') : 'Any' }} 
                            to 
                            {{ !empty($filters['date_range']['end']) ? \Carbon\Carbon::parse($filters['date_range']['end'])->format('m/d/Y') : 'Any' }}
                            <button wire:click="clearDateFilter" class="ml-1 text-yellow-500">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
        
        <!-- Expandable Filter Area -->
        <div 
            id="mobile-filter-area" 
            x-show="mobileFilterExpanded"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-4"
            class="bg-white border-t border-gray-200 shadow-lg px-4 py-3"
        >
            <div class="space-y-3 max-h-[75vh] overflow-y-auto pb-4">
                <!-- Mobile Transaction Type Filter -->
                <div>
                    <label for="type-mobile" class="block text-sm font-medium text-gray-700">Transaction Type</label>
                    <select id="type-mobile" wire:model.live="filters.type" class="mt-1 block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 rounded-md">
                        <option value="">All Types</option>
                        @foreach($transactionTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Mobile Customer Filter (Admin only) -->
                @if($canViewAllSales)
                <div>
                    <label for="customer-mobile" class="block text-sm font-medium text-gray-700">Customer</label>
                    <select id="customer-mobile" wire:model.live="filters.customer" class="mt-1 block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 rounded-md">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer['id'] }}">{{ $customer['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <!-- Mobile Date Range Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date Range</label>
                    <div class="grid grid-cols-2 gap-2 mt-1">
                        <div>
                            <label for="date_start_mobile" class="block text-xs text-gray-500">Start Date</label>
                            <input 
                                id="date_start_mobile"
                                type="date" 
                                wire:model.live="filters.date_range.start" 
                                class="block w-full border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 text-sm rounded-md"
                            >
                        </div>
                        <div>
                            <label for="date_end_mobile" class="block text-xs text-gray-500">End Date</label>
                            <input 
                                id="date_end_mobile"
                                type="date" 
                                wire:model.live="filters.date_range.end" 
                                class="block w-full border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 text-sm rounded-md"
                            >
                        </div>
                    </div>
                </div>
                
                <!-- Mobile Filter Actions -->
                <div class="flex justify-between pt-2">
                    <button 
                        type="button"
                        @click="mobileFilterExpanded = false"
                        class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-0"
                    >
                        Close
                    </button>
                    
                    @if($hasActiveFilters)
                    <button 
                        type="button"
                        wire:click="resetFilters"
                        @click="window.scrollTo({top: 0, behavior: 'smooth'});"
                        class="px-3 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-0"
                    >
                        Reset All Filters
                    </button>
                    @endif
                </div>
                
                <!-- Mobile Active Filters -->
                @php
                    $hasActiveFilters = !empty($search) || !empty($filters['type']) || !empty($filters['customer']) || !empty($filters['date_range']['start']) || !empty($filters['date_range']['end']);
                @endphp
                @if($hasActiveFilters)
                <div class="pt-2 border-t border-gray-200 mt-2">
                    <div class="text-xs font-medium text-gray-500 mb-2">Active Filters:</div>
                    <div class="flex flex-wrap gap-1">
                        @if(!empty($search))
                            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Search: {{ $search }}
                            </div>
                        @endif
                        
                        @if(!empty($filters['type']))
                            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Type: {{ $filters['type'] }}
                            </div>
                        @endif
                        
                        @if(!empty($filters['customer']))
                            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Customer: {{ $filters['customer'] }}
                            </div>
                        @endif
                        
                        @if(!empty($filters['date_range']['start']) || !empty($filters['date_range']['end']))
                            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Date: 
                                {{ !empty($filters['date_range']['start']) ? \Carbon\Carbon::parse($filters['date_range']['start'])->format('m/d/Y') : 'Any' }} 
                                to 
                                {{ !empty($filters['date_range']['end']) ? \Carbon\Carbon::parse($filters['date_range']['end'])->format('m/d/Y') : 'Any' }}
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>