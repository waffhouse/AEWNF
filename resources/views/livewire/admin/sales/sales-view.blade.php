<div
    x-data="{
        observer: null,
        
        setupObserver() {
            // Clean up any existing observer
            if (this.observer) {
                this.observer.disconnect();
            }
            
            // Setup infinite scroll
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        @this.loadMoreSales()
                    }
                })
            }, { rootMargin: '100px' });
            
            // Find the trigger element and observe it
            const trigger = this.$el.querySelector('#infinite-scroll-trigger');
            if (trigger) {
                this.observer.observe(trigger);
            }
        },
        
        // Scroll to top when filters change
        scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }"
    @refresh-data.window="scrollToTop()"
    x-init="setupObserver()"
    x-on:refreshed.window="setTimeout(() => setupObserver(), 100)"
    class="py-12"
>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">All Customer Sales History</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            View all customer transactions, invoices, and purchase history.
                        </p>
                    </div>
                    
                    <div class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Total Transactions: <strong>{{ $totalCount }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters Section -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search Input -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search Transactions</label>
                        <input 
                            type="text"
                            id="search"
                            wire:model.live="search"
                            placeholder="Search by ID, customer..."
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                    </div>
                    
                    <!-- Customer Filter -->
                    <div>
                        <label for="customer" class="block text-sm font-medium text-gray-700">Customer</label>
                        <select 
                            id="customer"
                            wire:model.live="filters.customer" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer['id'] }}">{{ $customer['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Transaction Type Filter -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Transaction Type</label>
                        <select 
                            id="type"
                            wire:model.live="filters.type" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            <option value="">All Types</option>
                            @foreach($transactionTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Date Range Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Transaction Date Range</label>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <div>
                                <input
                                    type="date"
                                    wire:model.live="filters.date_range.start"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="From">
                            </div>
                            <div>
                                <input
                                    type="date"
                                    wire:model.live="filters.date_range.end"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="To">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Active Filters -->
                <div class="mt-4 flex items-center space-x-2">
                    @if($filters['type'] || $filters['date_range']['start'] || $filters['date_range']['end'] || $filters['customer'])
                    <div class="flex flex-wrap gap-2">
                        @if($filters['customer'])
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-gray-100 text-gray-800">
                            Customer: {{ collect($customers)->firstWhere('id', $filters['customer'])['name'] ?? $filters['customer'] }}
                            <button type="button" class="ml-1.5 inline-flex text-gray-400 hover:text-gray-500" wire:click="$set('filters.customer', '')">
                                <span class="sr-only">Remove filter</span>
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </span>
                        @endif
                        
                        @if($filters['type'])
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-gray-100 text-gray-800">
                            Type: {{ $filters['type'] }}
                            <button type="button" class="ml-1.5 inline-flex text-gray-400 hover:text-gray-500" wire:click="$set('filters.type', '')">
                                <span class="sr-only">Remove filter</span>
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </span>
                        @endif
                        
                        @if($filters['date_range']['start'])
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-gray-100 text-gray-800">
                            From: {{ $filters['date_range']['start'] }}
                            <button type="button" class="ml-1.5 inline-flex text-gray-400 hover:text-gray-500" wire:click="$set('filters.date_range.start', '')">
                                <span class="sr-only">Remove filter</span>
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </span>
                        @endif
                        
                        @if($filters['date_range']['end'])
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-gray-100 text-gray-800">
                            To: {{ $filters['date_range']['end'] }}
                            <button type="button" class="ml-1.5 inline-flex text-gray-400 hover:text-gray-500" wire:click="$set('filters.date_range.end', '')">
                                <span class="sr-only">Remove filter</span>
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </span>
                        @endif
                    </div>
                    @endif
                    
                    @if($filters['type'] || $filters['date_range']['start'] || $filters['date_range']['end'] || $search)
                    <button
                        wire:click="resetFilters"
                        type="button"
                        class="text-red-500 hover:text-red-700 text-sm flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset Filters
                    </button>
                    @endif
                </div>
            </div>
        </div>
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            @forelse($summary as $item)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $item->type }}</h3>
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Number of Transactions</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($item->count) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total Amount</p>
                                <p class="text-2xl font-bold {{ $item->total_amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ${{ number_format(abs($item->total_amount), 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">No Transaction Data</h3>
                        <p class="mt-1 text-sm text-gray-500">We couldn't find any sales transactions matching your criteria.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Transactions Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Transaction History</h2>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Sort by:</span>
                        <select 
                            wire:model.live="sortField" 
                            class="text-sm border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500"
                            wire:change="updatedSortField"
                        >
                            <option value="date">Date</option>
                            <option value="tran_id">Transaction ID</option>
                            <option value="type">Type</option>
                            <option value="total_amount">Amount</option>
                            <option value="customer_name">Customer</option>
                        </select>
                        <button 
                            wire:click="toggleSortDirection" 
                            class="p-1 rounded hover:bg-gray-100"
                        >
                            @if($sortDirection === 'asc')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4" />
                                </svg>
                            @endif
                        </button>
                    </div>
                </div>
                
                <!-- Card Grid View -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
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
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('total_amount')">
                                    <div class="flex items-center justify-end">
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
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($sales as $sale)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $sale->tran_id }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded 
                                            {{ $sale->type === 'Invoice' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $sale->type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-gray-900">{{ $sale->date->format('M d, Y') }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <span class="{{ $sale->total_amount >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                            ${{ number_format(abs($sale->total_amount), 2) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex justify-center space-x-3">
                                            <button 
                                                wire:click="viewSaleDetails({{ $sale->id }})"
                                                class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Details
                                            </button>
                                            
                                            <a 
                                                href="{{ route('sales.invoice', $sale->id) }}" 
                                                target="_blank"
                                                class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                                Invoice
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p>No transactions found. Try adjusting your filters.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination/Load More -->
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
                            Showing {{ $loadedCount }} of {{ $totalCount }} transactions
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sale Detail Modal -->
    @if($viewingSale)
    <div class="fixed inset-0 z-50 flex items-start justify-center bg-gray-500 bg-opacity-75 overflow-y-auto pt-10 pb-10">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-auto max-h-[90vh] flex flex-col">
            <!-- Fixed Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center pb-3">
                    <h3 class="text-lg font-medium text-gray-900">
                        Transaction #{{ $viewingSale->tran_id }} - {{ $viewingSale->type }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Transaction Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Customer Information</h3>
                        <p class="mt-1 text-lg font-semibold">{{ $viewingSale->customer_name }}</p>
                        <p class="text-sm text-gray-500">Customer ID: {{ $viewingSale->entity_id }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Transaction Details</h3>
                        <p class="mt-1">Date: {{ $viewingSale->date->format('m/d/Y') }}</p>
                        <p class="mt-1">Total: 
                            <span class="{{ $viewingSale->total_amount >= 0 ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                ${{ number_format(abs($viewingSale->total_amount), 2) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Scrollable Content -->
            <div class="p-6 flex-1 overflow-y-auto border-t border-gray-100">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Line Items</h3>
                <div class="bg-white overflow-hidden border border-gray-200 shadow-sm rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SKU
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Description
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($viewingSale->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm">{{ $item->sku }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $item->item_description }}</td>
                                    <td class="px-4 py-3 text-sm text-right">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
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
                <div class="mt-6 flex justify-between">
                    <a 
                        href="{{ route('sales.invoice', $viewingSale->id) }}"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Download Invoice
                    </a>
                    
                    <button 
                        wire:click="closeModal"
                        class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-400 focus:outline-none focus:border-gray-400 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>