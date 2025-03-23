<div 
    x-data="{ 
        showStickyFilter: true,
        showFilterModal: false,
        isExpanded: false,
        ticking: false,
        scrollListeners: [],
        resizeListeners: [],
        
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
            
            // Event listeners for modal control (legacy support)
            const openMobileFiltersHandler = () => {
                this.showFilterModal = true;
            };
            window.addEventListener('open-mobile-filters', openMobileFiltersHandler);
            
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
                // Close modal on filter change
                Livewire.on('filter-changed', () => {
                    this.showFilterModal = false;
                });
                Livewire.on('closeFilterModal', () => {
                    this.showFilterModal = false;
                });
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
                
                <!-- Main Filters Section (hidden on small screens) -->
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
    
    <!-- Sticky Filter Bar - Collapsible version -->
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
        <!-- Filter toggle bar - Always visible -->
        <div class="w-full bg-gray-50 border-b border-gray-200 px-4 py-2">
            <!-- Top row with label and toggle button -->
            <div class="flex justify-between items-center">
                <div class="flex items-center flex-grow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span class="text-sm font-medium">Product Filters</span>
                    
                    @php
                        $activeFilterCount = 0;
                        if (!empty($search)) $activeFilterCount++;
                        if (!empty($brand)) $activeFilterCount++;
                        if (!empty($class)) $activeFilterCount++;
                    @endphp
                    
                    @if($activeFilterCount > 0)
                        <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">
                            {{ $activeFilterCount }}
                        </span>
                    @endif
                </div>
                
                <div class="flex items-center gap-3">
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
                        class="flex items-center px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors focus:outline-none"
                        aria-label="Return to top"
                    >
                        <span class="mr-1">Top</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                    </button>
                    
                    <!-- Expand/collapse button with text label for clarity -->
                    <button 
                        @click="toggleExpand()" 
                        class="flex items-center px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors focus:outline-none"
                        aria-label="Toggle filter panel"
                    >
                        <span x-text="isExpanded ? 'Hide Filters' : 'Show Filters'" class="mr-1"></span>
                        <svg 
                            x-show="!isExpanded" 
                            xmlns="http://www.w3.org/2000/svg" 
                            class="h-3 w-3 text-gray-500" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        <svg 
                            x-show="isExpanded" 
                            xmlns="http://www.w3.org/2000/svg" 
                            class="h-3 w-3 text-gray-500" 
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
                        class="w-full h-9 pl-8 pr-10 py-2 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-gray-400"
                        aria-label="Quick search products"
                    >
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3 text-red-500 hover:text-red-600">
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
                                wire:click="removeFilter('search')" 
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
                                wire:click="removeFilter('brand')" 
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
                                wire:click="removeFilter('class')" 
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
                                wire:click="clearFilters"
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
    
    <!-- Filter Modal - Enhanced for better organization -->
    <div 
        x-show="showFilterModal" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <!-- Backdrop with click to close -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showFilterModal = false"></div>
            
        <!-- Modal panel -->
        <div 
            class="relative bg-white rounded-t-xl mx-auto mt-16 sm:mt-20 px-4 py-5 shadow-xl max-w-lg sm:rounded-xl"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
        >
            <!-- Modal Header -->
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 class="text-lg font-medium text-gray-900">Filter Products</h3>
                <button 
                    @click="showFilterModal = false" 
                    class="rounded-full p-1 text-gray-400 hover:text-gray-500 hover:bg-gray-100"
                    aria-label="Close modal"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
                
            <!-- Modal Content -->
            <div class="space-y-5">
                <!-- Search -->
                <div>
                    <label for="modal-search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <form class="relative" wire:submit.prevent="submitSearch">
                        <div class="relative">
                            <input 
                                type="search" 
                                id="modal-search" 
                                wire:model="search" 
                                placeholder="Search products..." 
                                class="block w-full pl-8 pr-10 py-2 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-gray-400"
                            >
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                            <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3 text-red-500 hover:text-red-600">
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
                        class="block w-full py-2 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-gray-400"
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
                        class="block w-full py-2 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-gray-400"
                    >
                        <option value="">All Categories</option>
                        @foreach($classes as $classOption)
                            <option value="{{ $classOption }}">{{ $classOption }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Availability Information -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Availability Information</label>
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
                            <!-- Georgia Customer: Show informational text with same blue style as Florida -->
                            <div class="py-2 px-3 bg-blue-50 rounded border border-blue-200">
                                <div class="text-sm font-medium text-blue-800">Georgia Customer</div>
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
                <div class="border-t pt-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Active Filters</h4>
                    <div class="flex flex-wrap gap-2">
                        @if(!empty($search))
                            <button 
                                type="button" 
                                wire:click="removeFilter('search')" 
                                class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors focus:outline-none"
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
                                wire:click="removeFilter('brand')" 
                                class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800 hover:bg-green-200 transition-colors focus:outline-none"
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
                                wire:click="removeFilter('class')" 
                                class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 hover:bg-purple-200 transition-colors focus:outline-none"
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
                </div>
                @endif
                
                <!-- Redesigned Action Buttons -->
                <div class="border-t pt-4 mt-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Reset Filters Button -->
                        <button 
                            type="button"
                            wire:click="clearFilters"
                            @click="showFilterModal = false" 
                            class="sm:col-span-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 flex justify-center items-center"
                            aria-label="Reset all filters to default values"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset
                        </button>
                        
                        <!-- Apply Filters Button -->
                        <button 
                            type="button"
                            @click="showFilterModal = false" 
                            class="sm:col-span-2 px-4 py-2.5 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 flex justify-center items-center"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Done
                        </button>
                    </div>
                    
                    <!-- Indicator of Active Filters -->
                    @php
                        $activeFilterCount = 0;
                        if (!empty($search)) $activeFilterCount++;
                        if (!empty($brand)) $activeFilterCount++;
                        if (!empty($class)) $activeFilterCount++;
                    @endphp
                    <div class="text-center mt-3 text-xs text-gray-500">
                        @if($activeFilterCount > 0)
                            <span class="inline-flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $activeFilterCount }} filter{{ $activeFilterCount != 1 ? 's' : '' }} currently active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                No filters currently active
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>