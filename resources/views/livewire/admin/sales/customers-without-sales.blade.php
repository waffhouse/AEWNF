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
                        @this.loadMoreCustomers()
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
            <h1 class="text-2xl font-semibold text-gray-900">Customers Without Sales</h1>
            <p class="mt-2 text-sm text-gray-700">Customers in your database who have no recorded sales transactions</p>
        </div>
        
        <!-- Summary Cards -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-200">
                <h3 class="text-sm font-medium text-gray-500">Total Customers</h3>
                <div class="mt-2">
                    <div class="text-2xl font-bold">{{ number_format($summary['total_customers']) }}</div>
                </div>
            </div>
            
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-200">
                <h3 class="text-sm font-medium text-gray-500">Customers Without Sales</h3>
                <div class="mt-2">
                    <div class="text-2xl font-bold text-amber-600">{{ number_format($summary['customers_without_sales']) }}</div>
                    <div class="text-sm text-gray-500">{{ $summary['percentage_without_sales'] }}% of total</div>
                </div>
            </div>
            
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-200">
                <h3 class="text-sm font-medium text-gray-500">Customers With Sales</h3>
                <div class="mt-2">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($summary['customers_with_sales']) }}</div>
                    <div class="text-sm text-gray-500">{{ 100 - $summary['percentage_without_sales'] }}% of total</div>
                </div>
            </div>
            
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-200">
                <h3 class="text-sm font-medium text-gray-500">Total Sales Transactions</h3>
                <div class="mt-2">
                    <div class="text-2xl font-bold">{{ number_format($summary['total_sales_count']) }}</div>
                </div>
            </div>
        </div>
        
        <!-- Collapsible Filters -->
        <div class="mt-4 bg-white shadow-sm rounded-lg border border-gray-200" 
             x-data="{ 
                showFilters: true,
                hasFilters: @js($filters['county'] || $filters['home_state'] || $filters['date_range']['start'] || $filters['date_range']['end'] || $search),
                init() {
                    this.showFilters = !this.hasFilters;
                    
                    // Listen for Livewire model updates to collapse filters when values change
                    this.$watch('$wire.search', value => {
                        if (value) this.showFilters = false;
                    });
                    
                    this.$watch('$wire.filters.county', value => {
                        if (value) this.showFilters = false;
                    });
                    
                    this.$watch('$wire.filters.home_state', value => {
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
                        <label for="customers-search" class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <input 
                                type="text"
                                id="customers-search"
                                x-ref="searchInput"
                                wire:model.live.debounce.500ms="search"
                                @input="hasFilters = $event.target.value || $refs.countySelect.value || $refs.stateSelect.value || $refs.dateStart.value || $refs.dateEnd.value"
                                placeholder="Name, ID, email, county, state..."
                                class="block w-full pl-8 py-1.5 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-red-500">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- County Filter -->
                    <div>
                        <label for="county-filter" class="block text-xs font-medium text-gray-700 mb-1">County</label>
                        <select 
                            id="county-filter"
                            x-ref="countySelect"
                            wire:model.live="filters.county"
                            @change="hasFilters = $event.target.value || $refs.searchInput?.value || $refs.stateSelect.value || $refs.dateStart.value || $refs.dateEnd.value"
                            class="block w-full py-1.5 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-red-500">
                            <option value="">All Counties</option>
                            @foreach($counties as $county)
                                <option value="{{ $county }}">{{ $county }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- State Filter -->
                    <div>
                        <label for="state-filter" class="block text-xs font-medium text-gray-700 mb-1">State</label>
                        <select 
                            id="state-filter"
                            x-ref="stateSelect"
                            wire:model.live="filters.home_state"
                            @change="hasFilters = $event.target.value || $refs.searchInput?.value || $refs.countySelect.value || $refs.dateStart.value || $refs.dateEnd.value"
                            class="block w-full py-1.5 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-red-500">
                            <option value="">All States</option>
                            @foreach($states as $state)
                                <option value="{{ $state }}">{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Last Synced Date Filter (only if needed) -->
                    <div>
                        <label for="sync-date-filter" class="block text-xs font-medium text-gray-700 mb-1">Last Synced Since</label>
                        <input
                            id="sync-date-filter"
                            x-ref="dateStart"
                            type="date"
                            wire:model.live="filters.date_range.start"
                            @change="hasFilters = $event.target.value || $refs.searchInput?.value || $refs.countySelect.value || $refs.stateSelect.value"
                            class="block w-full py-1.5 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-red-500">
                    </div>
                </div>
                
                <!-- Simple Reset button within filter panel -->
                @if($filters['county'] || $filters['home_state'] || $filters['date_range']['start'] || $filters['date_range']['end'] || $search)
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
        
        <!-- Customers Table -->
        <div class="mt-6">
            <!-- Desktop Table (Hidden on small screens) -->
            <div class="hidden lg:block overflow-x-auto bg-white shadow-sm rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('entity_id')">
                                <div class="flex items-center">
                                    Customer ID
                                    @if($sortField === 'entity_id')
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('company_name')">
                                <div class="flex items-center">
                                    Company Name
                                    @if($sortField === 'company_name')
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('email')">
                                <div class="flex items-center">
                                    Contact Info
                                    @if($sortField === 'email')
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('license_number')">
                                <div class="flex items-center">
                                    License Info
                                    @if($sortField === 'license_number')
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('county')">
                                <div class="flex items-center">
                                    Location
                                    @if($sortField === 'county')
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('last_sync_at')">
                                <div class="flex items-center">
                                    Last Synced
                                    @if($sortField === 'last_sync_at')
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
                                <div class="flex items-center">
                                    Shipping Address
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if(!count($customers))
                            <tr>
                                <td colspan="7" class="py-6 text-center text-gray-500">
                                    No customers without sales found. Try clearing your filters.
                                </td>
                            </tr>
                        @else
                            @foreach($customers as $customer)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="font-medium">{{ $customer->entity_id }}</div>
                                        <div class="text-xs text-gray-500">NS ID: {{ $customer->netsuite_id ?: '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $customer->company_name }}</td>
                                    <td class="px-4 py-3 text-gray-500">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ $customer->email ?: '-' }}</span>
                                        </div>
                                        <div class="flex items-center mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <span>{{ $customer->phone ?: '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="bg-gray-50 p-2 rounded">
                                            <div class="flex flex-wrap items-center gap-1">
                                                <span class="text-xs text-gray-500">Number:</span>
                                                <span class="text-sm">{{ $customer->license_number ?: '-' }}</span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-1 mt-1">
                                                <span class="text-xs text-gray-500">Type:</span>
                                                <span class="text-sm">{{ $customer->license_type ?: '-' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <div>
                                                <div class="font-medium">{{ $customer->county ?: '-' }}</div>
                                                <div class="text-xs text-gray-500">{{ $customer->home_state ?: '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                        @if($customer->last_sync_at)
                                            <div>{{ $customer->last_sync_at->format('m/d/Y') }}</div>
                                            <div class="text-xs">{{ $customer->last_sync_at->format('g:i A') }}</div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 max-w-xs">
                                        <div class="text-sm line-clamp-2">{{ $customer->shipping_address ?: '-' }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Tablet/Mobile Card View (Visible on smaller screens) -->
            <div class="lg:hidden space-y-4">
                @if(!count($customers))
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center text-gray-500">
                        No customers without sales found. Try clearing your filters.
                    </div>
                @else
                    @foreach($customers as $customer)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                            <div class="flex justify-between items-start">
                                <div class="font-medium text-gray-900">{{ $customer->company_name }}</div>
                                <div class="text-xs text-gray-500">ID: {{ $customer->entity_id }}</div>
                            </div>
                            
                            <div class="mt-3 text-gray-500 text-sm">
                                <!-- Contact Information -->
                                <div class="flex flex-wrap gap-3 mb-3">
                                    @if($customer->email)
                                        <div class="flex items-center text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            {{ $customer->email }}
                                        </div>
                                    @endif
                                    
                                    @if($customer->phone)
                                        <div class="flex items-center text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            {{ $customer->phone }}
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- License Information -->
                                @if($customer->license_number || $customer->license_type)
                                    <div class="bg-gray-50 p-2 rounded mb-3">
                                        <div class="text-xs font-medium text-gray-600 mb-1">License Information</div>
                                        @if($customer->license_number)
                                            <div class="flex flex-wrap items-center gap-1">
                                                <span class="text-xs text-gray-500">Number:</span>
                                                <span class="text-sm">{{ $customer->license_number }}</span>
                                            </div>
                                        @endif
                                        @if($customer->license_type)
                                            <div class="flex flex-wrap items-center gap-1">
                                                <span class="text-xs text-gray-500">Type:</span>
                                                <span class="text-sm">{{ $customer->license_type }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                
                                <!-- Location Information -->
                                <div class="grid grid-cols-2 gap-2 mb-3">
                                    <div>
                                        <span class="text-xs text-gray-500">County:</span>
                                        <div>{{ $customer->county ?: '-' }}</div>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500">State:</span>
                                        <div>{{ $customer->home_state ?: '-' }}</div>
                                    </div>
                                </div>
                                
                                <!-- Shipping Address if available -->
                                @if($customer->shipping_address)
                                    <div class="mb-3">
                                        <span class="text-xs text-gray-500">Shipping Address:</span>
                                        <div class="text-sm whitespace-pre-line">{{ $customer->shipping_address }}</div>
                                    </div>
                                @endif
                                
                                <!-- Last Synced Information -->
                                <div class="mt-2 text-xs">
                                    <span class="text-gray-500">Last Synced:</span>
                                    <span class="text-gray-700">{{ $customer->last_sync_at ? $customer->last_sync_at->format('m/d/Y g:i A') : '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            
            <div class="mt-4 flex flex-col justify-center items-center">
                @if($hasMorePages)
                    <!-- Visible load more button -->
                    <button 
                        wire:click="loadMoreCustomers"
                        wire:loading.attr="disabled"
                        wire:target="loadMoreCustomers"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 mb-4">
                        <span wire:loading.remove wire:target="loadMoreCustomers">Load More</span>
                        <span wire:loading wire:target="loadMoreCustomers" class="inline-flex items-center">
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
                        Showing {{ $loadedCount }} of {{ $totalCount }} customers
                    </p>
                @endif
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
                        <p>This report shows customers in your database who have no recorded sales transactions. You can use this to:</p>
                        <ul class="list-disc pl-5 space-y-1 mt-2">
                            <li>Identify potential customers who haven't made purchases yet</li>
                            <li>Target marketing or outreach efforts to inactive customers</li>
                            <li>Filter by county or state to identify geographic areas for targeted campaigns</li>
                            <li>Review customer data that may need cleanup or verification</li>
                            <li>Export data for further analysis or campaign planning</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>