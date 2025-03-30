<div>
    <div class="p-6 bg-white rounded-lg shadow">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">Customer List</h3>
            
            <div class="flex space-x-2">
                <button 
                    wire:click="resetFilters"
                    class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset Filters
                </button>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search Filter -->
            <div>
                <x-filters.search-input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by ID, Name, or Email..."
                />
            </div>
            
            <!-- State Filter -->
            <div>
                <x-filters.dropdown-filter
                    wire:model.live="stateFilter"
                    :options="[''=>'All States'] + array_combine($states, $states)"
                    placeholder="Filter by State"
                />
            </div>
            
            <!-- License Type Filter -->
            <div>
                <x-filters.dropdown-filter
                    wire:model.live="licenseTypeFilter"
                    :options="[''=>'All License Types'] + array_combine($licenseTypes, $licenseTypes)"
                    placeholder="Filter by License Type"
                />
            </div>
        </div>
        
        <!-- Active Filters -->
        @if($search || $stateFilter || $licenseTypeFilter)
            <div class="mb-4">
                <x-filters.filter-badges>
                    @if($search)
                        <x-slot:badges>
                            <span wire:click="$set('search', '')" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 cursor-pointer">
                                Search: {{ $search }}
                                <svg class="ml-1.5 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </span>
                        </x-slot:badges>
                    @endif
                    
                    @if($stateFilter)
                        <x-slot:badges>
                            <span wire:click="$set('stateFilter', '')" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 cursor-pointer">
                                State: {{ $stateFilter }}
                                <svg class="ml-1.5 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </span>
                        </x-slot:badges>
                    @endif
                    
                    @if($licenseTypeFilter)
                        <x-slot:badges>
                            <span wire:click="$set('licenseTypeFilter', '')" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 cursor-pointer">
                                License Type: {{ $licenseTypeFilter }}
                                <svg class="ml-1.5 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </span>
                        </x-slot:badges>
                    @endif
                </x-components.filters.filter-badges>
            </div>
        @endif
        
        <!-- Customers Table -->
        <div class="overflow-hidden rounded-lg border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">State</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terms</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($customers as $customer)
                            <tr wire:key="customer-{{ $customer->id }}" class="hover:bg-gray-100">
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $customer->entity_id }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $customer->company_name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500">
                                    @if($customer->email)
                                        <div class="flex items-center text-blue-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ $customer->email }}</span>
                                        </div>
                                    @endif
                                    @if($customer->phone)
                                        <div class="flex items-center mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <span>{{ $customer->phone }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $customer->home_state == 'Florida' ? 'bg-blue-100 text-blue-800' : 
                                           ($customer->home_state == 'Georgia' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ $customer->home_state ?? 'N/A' }}
                                    </span>
                                    @if($customer->county)
                                        <div class="text-xs text-gray-500 mt-1">{{ $customer->county }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($customer->license_type)
                                        <div class="font-medium">{{ $customer->license_type }}</div>
                                    @endif
                                    @if($customer->license_number)
                                        <div class="text-xs mt-1">{{ $customer->license_number }}</div>
                                    @else
                                        <span class="text-gray-400">No License</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($customer->terms)
                                        <span>{{ $customer->terms }}</span>
                                    @endif
                                    @if($customer->price_level)
                                        <div class="text-xs text-gray-400 mt-1">
                                            Price Level: {{ $customer->price_level }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    @if($search || $stateFilter || $licenseTypeFilter)
                                        No customers found matching your filters.
                                        <button wire:click="resetFilters" class="text-blue-500 underline">Reset filters</button>
                                    @else
                                        No customers found. Sync customer data from NetSuite to view customers.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Infinite Scroll Controls -->
        <div 
            class="mt-4 text-center" 
            x-data="{ 
                observer: null,
                init() {
                    // Initialize the observer
                    this.setupObserver();
                    
                    // Listen for reset event
                    window.addEventListener('resetInfiniteScroll', () => {
                        this.setupObserver();
                    });
                },
                setupObserver() {
                    // Clean up any existing observer
                    if (this.observer) {
                        this.observer.disconnect();
                    }
                    
                    this.observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                @this.loadMore();
                            }
                        });
                    }, { rootMargin: '100px' });
                    
                    this.observer.observe(this.$el);
                }
            }"
        >
            <!-- Loading Indicator -->
            <div wire:loading wire:target="loadMore, updatedSearch, updatedStateFilter, updatedLicenseTypeFilter, resetFilters" class="py-4">
                <svg class="animate-spin h-5 w-5 text-red-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-sm text-gray-600">Loading customers...</span>
            </div>

            <!-- End of Results Message -->
            <div wire:loading.remove wire:target="loadMore" class="py-2 text-sm text-gray-600">
                @if($totalCount === 0)
                    No customers found
                @elseif($loadedCount === 1)
                    Showing 1 customer
                @elseif($loadedCount === $totalCount)
                    Showing all {{ $loadedCount }} customers
                @else
                    Showing {{ $loadedCount }} of {{ $totalCount }} customers
                @endif
            </div>
        </div>
    </div>
</div>