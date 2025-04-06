<div 
    x-data="{ 
        showStickyFilter: true,
        isExpanded: false,
        ticking: false,
        scrollListeners: [],
        resizeListeners: [],
        
        initializeEvents() {
            // Handle responsive behavior based on screen size - throttled with requestAnimationFrame
            const handleScreenSize = () => {
                // Always show sticky filter regardless of screen size or scroll position
                this.showStickyFilter = true;
                
                this.ticking = false;
            };
            
            // Do initial check only once since we always show the filter
            handleScreenSize();
            
            // Clean up any existing listeners 
            this.cleanupListeners();
            
            // We only need resize listener, no scroll listener needed
            const handleResize = () => {
                handleScreenSize();
            };
            window.addEventListener('resize', handleResize);
            
            // Store reference for cleanup (only resize now)
            this.resizeListeners.push(handleResize);
            
            // Mobile filter modal has been removed - no need for these event listeners
            
            // Listen for filter area events
            const expandFilterAreaHandler = () => {
                this.isExpanded = true;
            };
            const collapseFilterAreaHandler = () => {
                this.isExpanded = false;
            };
            
            window.addEventListener('expand-filter-area', expandFilterAreaHandler);
            window.addEventListener('collapse-filter-area', collapseFilterAreaHandler);
            
            // Setup Livewire event listeners if Livewire is available
            if (window.Livewire) {
                // Modal-related events have been removed as the modal is no longer used
            }
            
            // Add cleanup function to remove all event listeners
            this.$cleanup = () => {
                this.cleanupListeners();
            };
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
        
        init() {
            this.initializeEvents();
            
            // Handle navigation events for proper reinitialization
            window.addEventListener('alpine-reinit', () => {
                console.log('Alpine reinit detected in catalog, reinitializing events');
                this.initializeEvents();
            });
            
            // Listen for scroll-to-top events
            if (window.Livewire) {
                window.Livewire.on('scroll-to-top', () => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
                
                // Listen for delayed scroll-to-top events (used for clearing filters)
                window.Livewire.on('scroll-to-top-delayed', () => {
                    // Use a timeout to ensure the DOM has updated before scrolling
                    setTimeout(() => {
                        console.log('Executing delayed scroll to top');
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }, 50);
                });
            }
        },
        
        toggleExpand() {
            this.isExpanded = !this.isExpanded;
        }
    }" 
    class="py-6"
>
    <!-- Removing the default scroll to top button as we'll add a custom one in the sticky filter -->
    <!-- <x-scroll-to-top /> -->
    
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Hero Banner with Red Gradient Background -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6 sm:rounded-t-lg shadow-md">
            <div class="flex flex-col">
                <h2 class="text-2xl font-bold">Product Catalog</h2>
                <p class="text-sm text-red-100 mt-1">Browse our inventory with real-time pricing and availability information.</p>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-b-lg">
            <div class="p-6 text-gray-900">
                <!-- Main filters are now handled by the sticky filter bar at the bottom -->
                
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
    
    <!-- Sticky Filter Bar - Collapsible version -->
    <div 
        x-show="showStickyFilter" 
        x-cloak
        class="fixed bottom-0 inset-x-0 z-30 bg-gradient-to-r from-gray-900 to-black border-t border-red-600 shadow-lg"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform translate-y-full"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-full"
    >
        <!-- Filter toggle bar - Always visible -->
        <div class="w-full bg-gradient-to-r from-red-600 to-red-700 text-white border-b border-red-800 px-4 py-2">
            <!-- Top row with label and toggle button -->
            <div class="flex justify-between items-center">
                <div class="flex items-center flex-grow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span class="text-sm font-medium text-white">Product Filters</span>
                    
                    @php
                        $activeFilterCount = 0;
                        if (!empty($search)) $activeFilterCount++;
                        if (!empty($brand)) $activeFilterCount++;
                        if (!empty($class)) $activeFilterCount++;
                    @endphp
                    
                    @if($activeFilterCount > 0)
                        <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium rounded-full bg-white text-red-800">
                            {{ $activeFilterCount }}
                        </span>
                    @endif
                    
                    <!-- Loading Indicators integrated into filter header -->
                    <!-- For initial product loading or filter changes -->
                    <div wire:loading.flex wire:target="loadProducts, resetProducts" class="hidden items-center ml-3">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-2 text-xs font-medium text-white">Loading products...</span>
                    </div>
                    
                    <!-- For infinite scroll loading -->
                    <div wire:loading.flex wire:target="loadMore" class="hidden items-center ml-3">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-2 text-xs font-medium text-white">Loading more...</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Clear Cart Button (only shown for authenticated users with items in cart) -->
                    @auth
                        @can('add to cart')
                            @if($hasCartItems)
                                <button
                                    wire:click="clearCart"
                                    wire:confirm="Are you sure you want to clear your cart? This will remove all items."
                                    class="flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-white hover:bg-red-50 border border-red-200 rounded-md transition-colors focus:outline-none shadow-sm"
                                    aria-label="Clear shopping cart"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Clear Cart
                                </button>
                            @endif
                        @endcan
                    @endauth
                    
                    <!-- Scroll to top button integrated into filter bar with same styling as filter toggle -->
                    <button
                        x-data="{
                            show: false,
                            init() {
                                window.addEventListener('scroll', () => {
                                    this.show = window.scrollY > 500;
                                });
                            }
                        }"
                        x-show="show"
                        @click="window.scrollTo({top: 0, behavior: 'smooth'})"
                        class="flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-white hover:bg-red-50 rounded-md transition-colors focus:outline-none shadow-sm border border-red-200"
                        aria-label="Return to top"
                    >
                        <span class="mr-1">Top</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                    </button>
                    
                    <!-- Expand/collapse button with text label for clarity -->
                    <button 
                        @click="toggleExpand()" 
                        class="flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-white hover:bg-red-50 rounded-md transition-colors focus:outline-none shadow-sm border border-red-200"
                        aria-label="Toggle filter panel"
                    >
                        <span x-text="isExpanded ? 'Hide Filters' : 'Show Filters'" class="mr-1"></span>
                        <svg 
                            x-show="!isExpanded" 
                            xmlns="http://www.w3.org/2000/svg" 
                            class="h-3 w-3 text-red-600" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        <svg 
                            x-show="isExpanded" 
                            xmlns="http://www.w3.org/2000/svg" 
                            class="h-3 w-3 text-red-600" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Quick search input (always visible in sticky filter) -->
            <div class="mt-2">
                <form class="relative" wire:submit.prevent="submitSearch">
                    <input 
                        type="search" 
                        wire:model="search" 
                        placeholder="Quick search..." 
                        class="w-full h-9 px-4 pr-12 py-2 text-sm text-gray-900 border-red-300 rounded-md focus:ring-red-300 focus:border-red-400 bg-white shadow-sm"
                        aria-label="Quick search products"
                    >
                    <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3 text-red-600 hover:text-red-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>
                
                <!-- Pill filters in collapsed state if active -->
                @if(!empty($search) || !empty($brand) || !empty($class))
                    <div class="mt-2 flex flex-wrap gap-2 overflow-x-auto pb-1 max-w-full">
                        @if(!empty($search))
                            <button 
                                type="button" 
                                @click="window.scrollTo({top: 0, behavior: 'smooth'}); $nextTick(() => $wire.removeFilter('search'))" 
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 whitespace-nowrap hover:bg-blue-200 transition-colors focus:outline-none"
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
                        
                        @if(!empty($brand))
                            <button 
                                type="button" 
                                @click="window.scrollTo({top: 0, behavior: 'smooth'}); $nextTick(() => $wire.removeFilter('brand'))" 
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap hover:bg-green-200 transition-colors focus:outline-none"
                                aria-label="Remove brand filter"
                            >
                                Brand: {{ $brand }}
                                <span class="ml-1 text-green-500">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </span>
                            </button>
                        @endif
                        
                        @if(!empty($class))
                            <button 
                                type="button" 
                                @click="window.scrollTo({top: 0, behavior: 'smooth'}); $nextTick(() => $wire.removeFilter('class'))" 
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 whitespace-nowrap hover:bg-purple-200 transition-colors focus:outline-none"
                                aria-label="Remove category filter"
                            >
                                Category: {{ $class }}
                                <span class="ml-1 text-purple-500">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </span>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Expandable filter content -->
        <div 
            x-show="isExpanded"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-4"
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3"
        >
            <div class="flex flex-row gap-4 justify-between">
                <!-- Filter Controls Container -->
                <div class="flex flex-col sm:flex-row gap-4 w-full">
                    <!-- Filter Dropdowns - side by side on desktop -->
                    <div class="flex flex-col sm:flex-row w-full gap-4">
                        <!-- Brand Filter -->
                        <select 
                            wire:model.live="brand" 
                            aria-label="Filter by brand"
                            class="h-10 w-full sm:w-1/2 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-gray-400"
                        >
                            <option value="">All Brands</option>
                            @foreach($brands as $brandOption)
                                <option value="{{ $brandOption }}">{{ $brandOption }}</option>
                            @endforeach
                        </select>
                        
                        <!-- Category Filter -->
                        <select 
                            wire:model.live="class" 
                            aria-label="Filter by category"
                            class="h-10 w-full sm:w-1/2 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-gray-400"
                        >
                            <option value="">All Categories</option>
                            @foreach($classes as $classOption)
                                <option value="{{ $classOption }}">{{ $classOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Availability Information - Compact version for sticky filter -->
                    <div class="w-full sm:w-auto mt-2 sm:mt-0">
                        @if(auth()->check())
                            @if(auth()->user()->canViewFloridaItems() && auth()->user()->canViewGeorgiaItems())
                                <!-- Staff/Admin: More compact badge info -->
                                <div class="text-xs py-1 px-2 bg-green-50 rounded border border-green-200 text-center sm:text-left sm:w-[140px]">
                                    <span class="font-medium text-green-800">Staff/Admin Access</span>
                                </div>
                            @elseif(auth()->user()->canViewFloridaItems() && !auth()->user()->canViewGeorgiaItems())
                                <!-- Florida Customer: More compact badge info -->
                                <div class="text-xs py-1 px-2 bg-blue-50 rounded border border-blue-200 text-center sm:text-left sm:w-[140px]">
                                    <span class="font-medium text-blue-800">Florida Customer</span>
                                </div>
                            @elseif(auth()->user()->canViewGeorgiaItems() && !auth()->user()->canViewFloridaItems())
                                <!-- Georgia Customer: Using same blue style as Florida -->
                                <div class="text-xs py-1 px-2 bg-blue-50 rounded border border-blue-200 text-center sm:text-left sm:w-[140px]">
                                    <span class="font-medium text-blue-800">Georgia Customer</span>
                                </div>
                            @endif
                        @else
                            <!-- Guest users info -->
                            <div class="text-xs py-1 px-2 bg-gray-50 rounded border border-gray-200 text-center sm:text-left sm:w-[140px]">
                                <span class="font-medium text-gray-800">Guest Access</span>
                            </div>
                        @endif
                        
                        <!-- Reset Filters Button (only when filters are applied) -->
                        @if($activeFilterCount > 0)
                            <button 
                                @click="window.scrollTo({top: 0, behavior: 'smooth'}); $nextTick(() => $wire.clearFilters())"
                                type="button" 
                                class="w-full mt-2 py-1 px-2 text-xs font-medium text-gray-600 bg-gray-50 border border-gray-200 rounded hover:bg-gray-100 flex items-center justify-center gap-1"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Reset filters ({{ $activeFilterCount }})
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Active filter badges are already shown in the collapsed header -->
        </div>
    </div>
    
</div>