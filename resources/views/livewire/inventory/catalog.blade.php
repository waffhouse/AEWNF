<div 
    x-data="{ 
        showStickyFilter: true, // Always show on small screens, controlled by media queries on larger screens
        showMobileFilterModal: false,
        init() {
            // Only use scroll behavior on medium and larger screens
            const mediaQuery = window.matchMedia('(min-width: 768px)');
            
            const handleScroll = () => {
                if (mediaQuery.matches) {
                    // On desktop: Show sticky filter only when we've scrolled past the main filter section
                    this.showStickyFilter = window.scrollY > 300;
                    
                    // Add bottom padding when filter is shown to prevent content being hidden
                    if (this.showStickyFilter) {
                        document.body.classList.add('pb-sticky-filter');
                    } else {
                        document.body.classList.remove('pb-sticky-filter');
                    }
                } else {
                    // On mobile: Always show
                    this.showStickyFilter = true;
                    document.body.classList.add('pb-sticky-filter');
                }
            };
            
            // Initial check
            handleScroll();
            
            // Add scroll listener
            window.addEventListener('scroll', handleScroll);
            
            // Also check when window is resized
            window.addEventListener('resize', handleScroll);

            // Listen for the mobile filters open event
            window.addEventListener('open-mobile-filters', () => {
                this.showMobileFilterModal = true;
            });
            
            // Close mobile filters when a filter is applied or when explicitly requested
            Livewire.on('filter-changed', () => {
                this.showMobileFilterModal = false;
            });
            Livewire.on('closeFilterModal', () => {
                this.showMobileFilterModal = false;
            });
        }
    }" 
    class="py-6"
>
    <!-- Scroll to top button -->
    <x-scroll-to-top />
    
    <!-- Sticky Filter at Bottom (always visible on mobile, appears when scrolling on desktop) -->
    <div 
        x-show="showStickyFilter" 
        x-cloak
        class="fixed bottom-0 inset-x-0 z-30 bg-white border-t border-gray-200 shadow-lg md:transition"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform translate-y-full"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-full"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex items-center justify-between space-x-2">
                <!-- Desktop Search (hidden on small screens) -->
                <div class="relative w-full sm:w-64 mr-2 hidden sm:block">
                    <input 
                        type="search" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search products..." 
                        class="w-full pl-8 py-1.5 text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    >
                    <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Mobile Search Form (visible only on small screens) -->
                <div class="relative w-full mr-2 flex items-center sm:hidden">
                    <input 
                        type="search" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search products..." 
                        class="w-full pl-8 py-2 text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    >
                    <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Desktop Quick Filters (hidden on mobile) -->
                <div class="hidden md:flex md:flex-1 md:items-center md:space-x-2">
                    <!-- Quick Brand Filter -->
                    <select 
                        wire:model.live="brand" 
                        class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 py-1.5"
                    >
                        <option value="">All Brands</option>
                        @foreach($brands as $brandOption)
                            <option value="{{ $brandOption }}">{{ $brandOption }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center space-x-2">
                    <!-- Clear Filters Button (only shows when filters are applied) -->
                    @if($filtersApplied || !empty($search) || !empty($brand) || !empty($class))
                        <button 
                            wire:click="clearFilters"
                            type="button" 
                            class="sm:px-3 p-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 flex items-center"
                            aria-label="Clear all filters"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="hidden sm:inline-block sm:ml-1">Clear</span>
                        </button>
                    @endif
                    
                    <!-- More Filters Button with Counter -->
                    <button 
                        @click="showMobileFilterModal = true"
                        type="button" 
                        class="sm:px-3 p-2 relative text-sm font-medium text-red-600 bg-white border border-red-300 rounded-md shadow-sm hover:bg-red-50 flex items-center"
                        aria-label="Show more filter options"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span class="hidden sm:inline-block sm:ml-1">Filters</span>
                        @php
                            $activeFilterCount = 0;
                            if (!empty($search)) $activeFilterCount++;
                            if (!empty($brand)) $activeFilterCount++;
                            if (!empty($class)) $activeFilterCount++;
                        @endphp
                        @if($activeFilterCount > 0)
                            <span class="sm:ml-1.5 absolute sm:static sm:inline-flex top-0 right-0 items-center justify-center w-4 h-4 sm:w-auto sm:h-auto text-xs font-medium rounded-full bg-red-500 sm:bg-red-100 text-white sm:text-red-800 sm:px-2 sm:py-0.5 transform translate-x-1/3 -translate-y-1/3 sm:transform-none">
                                {{ $activeFilterCount }}
                            </span>
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="mb-4">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                        <div>
                            <h2 class="text-xl font-semibold">Product Catalog</h2>
                            <p class="text-sm text-gray-600 mt-1">Browse our inventory with real-time pricing and availability information.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Filters Section (hidden on small screens) -->
                <div class="hidden md:block">
                    @include('livewire.inventory.catalog-filters')
                </div>
                
                <!-- Product Grid Section -->
                <div wire:key="product-grid" wire:loading.class="opacity-50">
                    @include('livewire.inventory.product-grid', [
                        'products' => $products,
                        'hasMorePages' => $hasMorePages,
                        'totalCount' => $totalCount,
                        'loadedCount' => $loadedCount,
                        'isLoading' => $isLoading
                    ])
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile Filter Modal -->
    <div 
        x-show="showMobileFilterModal" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showMobileFilterModal = false"></div>
            
        <!-- Modal panel -->
        <div 
            class="relative bg-white rounded-t-lg max-w-lg mx-auto mt-20 px-4 py-5 shadow-xl"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
        >
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Filter Products</h3>
                <button 
                    @click="showMobileFilterModal = false" 
                    class="text-gray-400 hover:text-gray-500"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
                
            <div class="space-y-4">
                <!-- Search -->
                <div>
                    <label for="modal-search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <form class="relative flex items-center" wire:submit.prevent="$refresh">
                        <div class="relative w-full">
                            <input 
                                type="search" 
                                id="modal-search" 
                                wire:model.live="search" 
                                placeholder="Search products..." 
                                class="block w-full pr-10 py-2 text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            >
                            <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3 text-blue-500 hover:text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Brand Filter -->
                <div>
                    <label for="modal-brand" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                    <select 
                        id="modal-brand" 
                        wire:model.live="brand" 
                        class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">All Brands</option>
                        @foreach($brands as $brandOption)
                            <option value="{{ $brandOption }}">{{ $brandOption }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Category Filter -->
                <div>
                    <label for="modal-class" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select 
                        id="modal-class" 
                        wire:model.live="class" 
                        class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">All Categories</option>
                        @foreach($classes as $classOption)
                            <option value="{{ $classOption }}">{{ $classOption }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Availability Info - Replaces state filter in modal -->
                <div>
                    <label for="modal-availability-info" class="block text-sm font-medium text-gray-700 mb-1">Availability Information</label>
                    @if(auth()->check())
                        @if(auth()->user()->canViewFloridaItems() && auth()->user()->canViewGeorgiaItems())
                            <!-- Staff/Admin: Show all states info -->
                            <div class="py-2 px-3 bg-green-50 rounded border border-green-200">
                                <div class="text-sm font-medium text-green-800">Staff/Admin Access</div>
                                <div class="text-xs text-gray-600 mt-1">You can view products for all states.</div>
                            </div>
                        @elseif(auth()->user()->canViewFloridaItems() && !auth()->user()->canViewGeorgiaItems())
                            <!-- Florida Customer: Show informational text -->
                            <div class="py-2 px-3 bg-blue-50 rounded border border-blue-200">
                                <div class="text-sm font-medium text-blue-800">Florida Customer</div>
                                <div class="text-xs text-gray-600 mt-1">You can only view Florida and unrestricted items.</div>
                            </div>
                        @elseif(auth()->user()->canViewGeorgiaItems() && !auth()->user()->canViewFloridaItems())
                            <!-- Georgia Customer: Show informational text -->
                            <div class="py-2 px-3 bg-yellow-50 rounded border border-yellow-200">
                                <div class="text-sm font-medium text-yellow-800">Georgia Customer</div>
                                <div class="text-xs text-gray-600 mt-1">You can only view Georgia and unrestricted items.</div>
                            </div>
                        @endif
                    @else
                        <!-- Guest users info -->
                        <div class="py-2 px-3 bg-gray-50 rounded border border-gray-200">
                            <div class="text-sm font-medium text-gray-800">Guest Access</div>
                            <div class="text-xs text-gray-600 mt-1">Sign in to see state-specific pricing and availability.</div>
                        </div>
                    @endif
                </div>
                
                <!-- Active Filters Section -->
                @if(!empty($search) || !empty($brand) || !empty($class))
                <div class="mt-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Active Filters:</h4>
                    <div class="flex flex-wrap gap-2">
                        @if(!empty($search))
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Search: {{ $search }}
                                <button type="button" wire:click="removeFilter('search')" class="ml-1 text-blue-500 hover:text-blue-600">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </span>
                        @endif
                        
                        @if(!empty($brand))
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Brand: {{ $brand }}
                                <button type="button" wire:click="removeFilter('brand')" class="ml-1 text-blue-500 hover:text-blue-600">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </span>
                        @endif
                        
                        @if(!empty($class))
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Category: {{ $class }}
                                <button type="button" wire:click="removeFilter('class')" class="ml-1 text-blue-500 hover:text-blue-600">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </span>
                        @endif
                    </div>
                </div>
                @endif
                
                <!-- Action Buttons -->
                <div class="flex space-x-2 pt-4">
                    <button 
                        type="button"
                        wire:click="clearFilters"
                        @click="showMobileFilterModal = false" 
                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 flex justify-center items-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear
                    </button>
                    <button 
                        type="button"
                        @click="showMobileFilterModal = false" 
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 flex justify-center items-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>