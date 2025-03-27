<div
    x-data="{
        observer: null,
        setupObserver() {
            // Clean up any existing observer
            if (this.observer) {
                this.observer.disconnect();
            }
            
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        @this.loadMoreSales()
                    }
                })
            }, {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            });
            
            // Find the trigger element and observe it if it exists
            const trigger = this.$el.querySelector('#infinite-scroll-trigger');
            if (trigger) {
                this.observer.observe(trigger);
            }
        }
    }"
    x-init="setupObserver()"
    x-on:refreshed.window="setTimeout(() => setupObserver(), 100)"
>
    <div class="px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Sales Transactions</h1>
            <p class="mt-2 text-sm text-gray-700">A list of all sales transactions from NetSuite</p>
        </div>
        
        <!-- Collapsible Filters -->
        <div class="mt-4 bg-white shadow-sm rounded-lg border border-gray-200" 
             x-data="{ 
                showFilters: true,
                hasFilters: @js($filters['type'] || $filters['date_range']['start'] || $filters['date_range']['end'] || $search),
                init() {
                    this.showFilters = !this.hasFilters;
                    
                    // Listen for Livewire model updates to collapse filters when values change
                    this.$watch('$wire.search', value => {
                        if (value) this.showFilters = false;
                    });
                    
                    this.$watch('$wire.filters.type', value => {
                        if (value) this.showFilters = false;
                    });
                    
                    this.$watch('$wire.filters.date_range.start', value => {
                        if (value) this.showFilters = false;
                    });
                    
                    this.$watch('$wire.filters.date_range.end', value => {
                        if (value) this.showFilters = false;
                    });
                }
             }">
            <!-- Filter Header with Toggle -->
            <div class="p-3 flex justify-between items-center border-b border-gray-200 cursor-pointer" @click="showFilters = !showFilters">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span class="font-medium text-sm">Filters</span>
                    <span x-show="hasFilters" class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Active
                    </span>
                </div>
                <div>
                    <svg x-show="!showFilters" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    <svg x-show="showFilters" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                </div>
            </div>
            
            <!-- Filter Inputs (Collapsible) - includes simple Reset button at bottom -->
            <div class="p-4" x-show="showFilters" x-collapse>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <!-- Search Input -->
                    <div class="col-span-2 md:col-span-1">
                        <label for="sales-search" class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <input 
                                type="text"
                                id="sales-search"
                                x-ref="searchInput"
                                wire:model.live.debounce.500ms="search"
                                @input="hasFilters = $event.target.value || $refs.typeSelect.value || $refs.dateStart.value || $refs.dateEnd.value"
                                placeholder="ID or customer..."
                                class="block w-full pl-8 py-1.5 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-red-500">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Transaction Type Filter -->
                    <div>
                        <label for="sales-type-filter" class="block text-xs font-medium text-gray-700 mb-1">Transaction Type</label>
                        <select 
                            id="sales-type-filter"
                            x-ref="typeSelect"
                            wire:model.live="filters.type"
                            @change="hasFilters = $event.target.value || $refs.searchInput?.value || $refs.dateStart.value || $refs.dateEnd.value"
                            class="block w-full py-1.5 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-red-500">
                            <option value="">All Types</option>
                            @foreach($transactionTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Start Date -->
                    <div>
                        <label for="sales-date-start" class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
                        <input
                            id="sales-date-start"
                            x-ref="dateStart"
                            type="date"
                            wire:model.live="filters.date_range.start"
                            @change="hasFilters = $event.target.value || $refs.searchInput?.value || $refs.typeSelect.value || $refs.dateEnd.value"
                            class="block w-full py-1.5 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-red-500">
                    </div>
                    
                    <!-- End Date -->
                    <div>
                        <label for="sales-date-end" class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
                        <input
                            id="sales-date-end"
                            x-ref="dateEnd"
                            type="date"
                            wire:model.live="filters.date_range.end"
                            @change="hasFilters = $event.target.value || $refs.searchInput?.value || $refs.typeSelect.value || $refs.dateStart.value"
                            class="block w-full py-1.5 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-red-500">
                    </div>
                </div>
                
                <!-- Simple Reset button within filter panel -->
                @if($filters['type'] || $filters['date_range']['start'] || $filters['date_range']['end'] || $search)
                <div class="mt-3 flex justify-end">
                    <button
                        wire:click="resetFilters"
                        @click="showFilters = true; hasFilters = false;"
                        type="button"
                        class="inline-flex items-center px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset Filters
                    </button>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($this->getSummaryData() as $summary)
                <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ $summary->type }}</h3>
                    <div class="flex justify-between mt-2">
                        <div>
                            <p class="text-sm text-gray-500">Count</p>
                            <p class="text-lg font-semibold">{{ $summary->count }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total</p>
                            <p class="text-lg font-semibold">${{ number_format($summary->total_amount, 2) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Sales Table -->
        <div class="mt-6">
            <div class="overflow-x-auto bg-white shadow-sm rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('tran_id')">
                                <div class="flex items-center">
                                    Transaction ID
                                    @if($sortField === 'tran_id')
                                        <span class="ml-1">
                                            @if($sortDirection === 'asc')
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('type')">
                                <div class="flex items-center">
                                    Type
                                    @if($sortField === 'type')
                                        <span class="ml-1">
                                            @if($sortDirection === 'asc')
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('date')">
                                <div class="flex items-center">
                                    Date
                                    @if($sortField === 'date')
                                        <span class="ml-1">
                                            @if($sortDirection === 'asc')
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('customer_name')">
                                <div class="flex items-center">
                                    Customer
                                    @if($sortField === 'customer_name')
                                        <span class="ml-1">
                                            @if($sortDirection === 'asc')
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('total_amount')">
                                <div class="flex items-center">
                                    Amount
                                    @if($sortField === 'total_amount')
                                        <span class="ml-1">
                                            @if($sortDirection === 'asc')
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if(!count($sales))
                            <tr>
                                <td colspan="6" class="py-6 text-center text-gray-500">
                                    No sales data found. Try clearing your filters or syncing with NetSuite.
                                </td>
                            </tr>
                        @else
                            @foreach($sales as $sale)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">{{ $sale->tran_id }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded 
                                            {{ $sale->type === 'Invoice' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $sale->type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $sale->date->format('m/d/Y') }}</td>
                                    <td class="px-4 py-3">
                                        {{ $sale->customer_name }}
                                        <div class="text-xs text-gray-500">ID: {{ $sale->entity_id }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="{{ $sale->total_amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            ${{ number_format(abs($sale->total_amount), 2) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end space-x-2">
                                            <button 
                                                wire:click="viewSaleDetails({{ $sale->id }})"
                                                class="text-blue-600 hover:text-blue-900">
                                                View Details
                                            </button>
                                            
                                            <span class="text-gray-400">|</span>
                                            
                                            <a 
                                                href="{{ route('sales.invoice', $sale->id) }}"
                                                target="_blank"
                                                class="text-red-600 hover:text-red-900">
                                                Invoice
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 flex flex-col justify-center items-center">
                @if($hasMorePages)
                    <!-- Visible load more button -->
                    <button 
                        wire:click="loadMoreSales"
                        wire:loading.attr="disabled"
                        wire:target="loadMoreSales"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 mb-4">
                        <span wire:loading.remove wire:target="loadMoreSales">Load More</span>
                        <span wire:loading wire:target="loadMoreSales" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Loading...
                        </span>
                    </button>
                    
                    <!-- Invisible trigger for infinite scroll -->
                    <div id="infinite-scroll-trigger" class="h-4 w-full"></div>
                @else
                    <p class="text-sm text-gray-500">
                        Showing {{ $loadedCount }} of {{ $totalCount }} sales
                    </p>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Sale Detail Modal -->
    @if($viewingSale)
    <div class="fixed inset-0 z-50 flex items-start justify-center bg-gray-500 bg-opacity-75 overflow-y-auto pt-4 pb-6">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4 md:mx-auto max-h-[95vh] flex flex-col">
            <!-- Fixed Header - stays in place when scrolling -->
            <div class="p-4 sm:p-5 border-b border-gray-200">
                <!-- Modal Header -->
                <div class="flex justify-between items-center">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 truncate max-w-[80%]">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium mr-2 
                            {{ $viewingSale->type === 'Invoice' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $viewingSale->type }}
                        </span>
                        #{{ $viewingSale->tran_id }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Basic Transaction Information - also fixed -->
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div>
                        <h3 class="text-xs font-medium text-gray-500">Customer</h3>
                        <p class="text-sm sm:text-base font-medium truncate">{{ $viewingSale->customer_name }}</p>
                        <p class="text-xs text-gray-500">ID: {{ $viewingSale->entity_id }}</p>
                    </div>
                    <div class="text-right">
                        <h3 class="text-xs font-medium text-gray-500">Date</h3>
                        <p class="text-sm sm:text-base">{{ $viewingSale->date->format('m/d/Y') }}</p>
                        <h3 class="text-xs font-medium text-gray-500 mt-1.5">Total</h3>
                        <p class="text-sm sm:text-base font-medium {{ $viewingSale->total_amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ${{ number_format(abs($viewingSale->total_amount), 2) }}
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Scrollable Content -->
            <div class="p-4 sm:p-5 flex-1 overflow-y-auto">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Line Items</h3>
                
                <!-- Mobile-friendly list (visible on small screens) -->
                <div class="sm:hidden space-y-3">
                    @foreach($viewingSale->items as $item)
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex justify-between items-start mb-2">
                                <div class="font-medium text-sm truncate mr-2">{{ $item->sku }}</div>
                                <div class="whitespace-nowrap text-sm {{ $item->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ${{ number_format(abs($item->amount), 2) }}
                                </div>
                            </div>
                            <p class="text-sm text-gray-700 mb-2">{{ $item->item_description }}</p>
                            <div class="text-xs text-gray-500">
                                Quantity: <span class="font-medium">{{ number_format($item->quantity, 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Table (visible on larger screens) -->
                <div class="hidden sm:block bg-white overflow-hidden border border-gray-200 shadow-sm rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SKU
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Description
                                </th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($viewingSale->items as $item)
                                <tr>
                                    <td class="px-3 py-2 text-xs">{{ $item->sku }}</td>
                                    <td class="px-3 py-2 text-xs">{{ $item->item_description }}</td>
                                    <td class="px-3 py-2 text-xs text-right">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="px-3 py-2 text-xs text-right">
                                        <span class="{{ $item->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            ${{ number_format(abs($item->amount), 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Modal Footer -->
                <div class="mt-5 flex flex-col-reverse sm:flex-row sm:justify-between sm:space-x-2 space-y-2 space-y-reverse sm:space-y-0">
                    <a 
                        href="{{ route('sales.invoice', $viewingSale->id) }}"
                        target="_blank"
                        class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Generate Invoice
                    </a>
                    
                    <button 
                        wire:click="closeModal"
                        class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-300 focus:outline-none focus:border-gray-300 focus:ring ring-gray-200 disabled:opacity-25 transition ease-in-out duration-150">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>