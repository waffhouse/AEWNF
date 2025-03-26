<div class="py-12"
    x-data="{
        observer: null,
        showStickyFilter: true,
        // isExpanded removed
        ticking: false,
        scrollListeners: [],
        resizeListeners: [],
        
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
        },
        
        initializeEvents() {
            // Handle responsive behavior based on screen size - throttled with requestAnimationFrame
            const handleScreenSize = () => {
                // Only control sticky filter visibility on desktop
                if (window.matchMedia('(min-width: 768px)').matches) {
                    // On desktop: Show sticky filter only when scrolled past main filters
                    this.showStickyFilter = window.scrollY > 300;
                } else {
                    // On mobile: Always show sticky filter
                    this.showStickyFilter = true;
                }
                
                this.ticking = false;
            };
            
            const requestTick = () => {
                if (!this.ticking) {
                    window.requestAnimationFrame(handleScreenSize);
                    this.ticking = true;
                }
            };
            
            // Initial check
            handleScreenSize();
            
            // Clean up any existing listeners 
            this.cleanupListeners();
            
            // Add throttled event listeners
            window.addEventListener('scroll', requestTick);
            window.addEventListener('resize', requestTick);
            
            // Store references for cleanup
            this.scrollListeners.push(requestTick);
            this.resizeListeners.push(requestTick);
        },
        
        cleanupListeners() {
            // Remove scroll listeners
            this.scrollListeners.forEach(listener => {
                window.removeEventListener('scroll', listener);
            });
            this.scrollListeners = [];
            
            // Remove resize listeners
            this.resizeListeners.forEach(listener => {
                window.removeEventListener('resize', listener);
            });
            this.resizeListeners = [];
        },
        
        // Toggle functionality removed
        
        init() {
            this.setupObserver();
            this.initializeEvents();
        }
    }"
    @refresh-data.window="scrollToTop()"
    x-init="init()"
    x-on:refreshed.window="setTimeout(() => setupObserver(), 100)"
>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Your Sales History</h1>
                        <p class="mt-1 text-sm text-gray-600">View your transactions, invoices, and purchase history.</p>
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
        
        <!-- Simple Filters Section -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Search Input -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search Transactions</label>
                        <input 
                            type="text"
                            id="search"
                            wire:model.live="search"
                            placeholder="Search by ID..."
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
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
                    
                    <!-- Date From -->
                    <div>
                        <label for="date-from" class="block text-sm font-medium text-gray-700">From Date</label>
                        <input
                            id="date-from"
                            type="date"
                            wire:model.live="filters.date_range.start"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                    </div>
                    
                    <!-- Date To -->
                    <div>
                        <label for="date-to" class="block text-sm font-medium text-gray-700">To Date</label>
                        <input
                            id="date-to"
                            type="date"
                            wire:model.live="filters.date_range.end"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                    </div>
                </div>
                
                <!-- Reset Button -->
                <div class="mt-4">
                    <button 
                        wire:click="resetFilters" 
                        class="inline-flex items-center px-4 py-2 bg-red-50 border border-red-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest hover:bg-red-100">
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Simple Transaction List -->
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
                
                <!-- Card-based Transaction List -->
                @if(count($sales) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($sales as $sale)
                            <div wire:key="transaction-{{ $sale->id }}" class="bg-white rounded-lg border border-gray-200 shadow p-4 hover:shadow-md transition-shadow duration-300">
                                <div class="flex justify-between items-center mb-2">
                                    <!-- Transaction ID and Type -->
                                    <div>
                                        <span class="text-sm font-bold">#{{ $sale->tran_id }}</span>
                                        <span class="ml-2 px-2 py-1 text-xs font-medium rounded 
                                            {{ $sale->type === 'Invoice' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $sale->type }}
                                        </span>
                                    </div>
                                    
                                    <!-- Date -->
                                    <div class="text-sm text-gray-600">{{ $sale->date->format('M d, Y') }}</div>
                                </div>
                                
                                <!-- Amount -->
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-sm font-medium text-gray-600">Total:</span>
                                    <span class="text-xl font-bold {{ $sale->total_amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        ${{ number_format(abs($sale->total_amount), 2) }}
                                    </span>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex justify-end mt-3 pt-2 border-t border-gray-100">
                                    <button 
                                        wire:click="viewSaleDetails({{ $sale->id }})"
                                        class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 mr-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Details
                                    </button>
                                    
                                    <a 
                                        href="{{ route('sales.invoice', $sale->id) }}" 
                                        target="_blank"
                                        class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        Invoice
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Load More & Status Section -->
                    <div class="mt-8">
                        <!-- Loading Indicator -->
                        <div wire:loading wire:target="loadMoreSales, resetItems, updatedSortField, toggleSortDirection" class="flex justify-center mb-4">
                            <div class="flex items-center space-x-2 text-gray-500">
                                <svg class="animate-spin h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Loading transactions...</span>
                            </div>
                        </div>
                        
                        @if($hasMorePages)
                            <div class="flex justify-center">
                                <button 
                                    wire:click="loadMoreSales"
                                    wire:loading.attr="disabled"
                                    wire:target="loadMoreSales"
                                    class="px-6 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <span wire:loading.remove wire:target="loadMoreSales">Load More Transactions</span>
                                    <span wire:loading wire:target="loadMoreSales">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-700 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Loading...
                                    </span>
                                </button>
                            </div>
                            
                            <!-- Hidden trigger for infinite scroll -->
                            <div id="infinite-scroll-trigger" class="h-20 w-full mb-10 mt-4"></div>
                        @endif
                        
                        <p class="mt-4 text-center text-sm text-gray-500">
                            Showing {{ $loadedCount }} of {{ $totalCount }} transactions
                        </p>
                    </div>
                @else
                    <!-- Empty state -->
                    <div class="py-8 text-center text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">No Transactions Found</h3>
                        <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or check back later.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Sticky Filter Bar - Simplified Version -->
    <div 
        x-show="showStickyFilter" 
        x-cloak
        class="fixed bottom-0 inset-x-0 z-30 bg-white border-t border-gray-200 shadow-lg"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform translate-y-full"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-full"
    >
        <!-- Simplified Filter Bar - Only Search Input -->
        <div class="w-full bg-gray-50 px-4 py-2.5">
            <div class="relative">
                <input 
                    type="search" 
                    wire:model.live="search" 
                    placeholder="Search by transaction ID or customer name..." 
                    class="w-full h-8 pl-7 pr-2 py-1 text-xs rounded-md border-gray-300 focus:ring-0 focus:border-gray-400"
                >
                <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                    <svg class="w-3.5 h-3.5 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Transaction Details Modal -->
    @if($viewingSale)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center pb-3">
                    <h3 class="text-lg font-medium text-gray-900">
                        Transaction #{{ $viewingSale->tran_id }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Customer</h3>
                        <p class="mt-1 text-lg font-semibold">{{ $viewingSale->customer_name }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Transaction Details</h3>
                        <p class="mt-1">Type: {{ $viewingSale->type }}</p>
                        <p class="mt-1">Date: {{ $viewingSale->date->format('m/d/Y') }}</p>
                        <p class="mt-1">Total: 
                            <span class="{{ $viewingSale->total_amount >= 0 ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                ${{ number_format(abs($viewingSale->total_amount), 2) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Line Items -->
            <div class="p-6 flex-1 overflow-y-auto">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Line Items</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
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
                
                <!-- Footer -->
                <div class="mt-6 flex justify-between">
                    <a 
                        href="{{ route('sales.invoice', $viewingSale->id) }}"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                        Download Invoice
                    </a>
                    
                    <button 
                        wire:click="closeModal"
                        class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>