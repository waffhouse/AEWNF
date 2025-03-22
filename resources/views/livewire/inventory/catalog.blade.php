<div 
    x-data="{ 
        showStickyFilter: false,
        showMobileFilterModal: false,
        init() {
            window.addEventListener('scroll', () => {
                // Show sticky filter only when we've scrolled past the main filter section
                this.showStickyFilter = window.scrollY > 300;
                
                // Add bottom padding when filter is shown to prevent content being hidden
                if (this.showStickyFilter) {
                    document.body.classList.add('pb-sticky-filter');
                } else {
                    document.body.classList.remove('pb-sticky-filter');
                }
            });
        }
    }" 
    class="py-6"
>
    <!-- Scroll to top button -->
    <x-scroll-to-top />
    <!-- Sticky Filter at Bottom (appears when scrolling) -->
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex items-center justify-between space-x-2">
                <!-- Quick Search (visible on all devices) -->
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
                <form class="relative w-full mr-2 flex items-center sm:hidden" wire:submit.prevent="$refresh">
                    <div class="relative w-full flex items-center">
                        <input 
                            type="search" 
                            wire:model="search" 
                            placeholder="Search products..." 
                            class="w-full pr-10 py-1.5 text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        >
                        <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3 text-blue-500 hover:text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </form>
                
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
                    
                    <!-- Quick state filter removed -->
                </div>
                
                <!-- Desktop Filter Badges Removed -->
                <div class="hidden md:flex flex-1 items-center flex-wrap gap-2">
                    <!-- No filter badges shown -->
                </div>
                
                <!-- Filter Badge Counter Removed -->
                <div class="flex md:hidden items-center flex-1">
                    <!-- Removed counter -->
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center space-x-2">
                    <!-- Clear Filters Button (only shows when filters are applied) -->
                    @if($filtersApplied || !empty($search) || !empty($brand) || !empty($class) || ($filtersApplied && $state !== 'all'))
                        <button 
                            wire:click="clearFilters"
                            type="button" 
                            class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 flex items-center"
                            aria-label="Clear all filters"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="hidden md:inline-block md:ml-1">Clear</span>
                        </button>
                    @endif
                    
                    <!-- More Filters Button with Counter -->
                    <button 
                        @click="showMobileFilterModal = true"
                        type="button" 
                        class="px-3 py-1.5 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-md shadow-sm hover:bg-red-50 flex items-center"
                        aria-label="Show more filter options"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span class="ml-1">Filters</span>
                        @php
                            $activeFilterCount = 0;
                            if (!empty($search)) $activeFilterCount++;
                            if (!empty($brand)) $activeFilterCount++;
                            if (!empty($class)) $activeFilterCount++;
                            if ($this->isStateFilterActive()) $activeFilterCount++;
                        @endphp
                        @if($activeFilterCount > 0)
                            <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">
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
                
                <!-- Basic Filter UI -->
                <div class="mb-6 border border-gray-200 rounded-lg p-4 bg-gray-50 shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            
                            <!-- Desktop Search (hidden on small screens) -->
                            <div class="relative hidden sm:block">
                                <input 
                                    type="search" 
                                    id="search" 
                                    wire:model.live.debounce.300ms="search"
                                    placeholder="Search products..." 
                                    class="block w-full pl-8 py-2 text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                >
                                <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Mobile Search Form (visible only on small screens) -->
                            <form class="relative flex items-center sm:hidden" wire:submit.prevent="$refresh">
                                <div class="relative w-full">
                                    <input 
                                        type="search" 
                                        id="search-mobile" 
                                        wire:model="search"
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
                            <label for="brand-filter" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                            <select 
                                id="brand-filter" 
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
                            <label for="class-filter" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select 
                                id="class-filter" 
                                wire:model.live="class"
                                class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">All Categories</option>
                                @foreach($classes as $classOption)
                                    <option value="{{ $classOption }}">{{ $classOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Availability Info - Replaces state filter dropdown -->
                        <div>
                            <label for="availability-info" class="block text-sm font-medium text-gray-700 mb-1">
                                Availability Information
                            </label>
                            
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
                    </div>
                    
                    <!-- Filter Actions -->
                    <div class="mt-4 flex justify-end">
                        @if($filtersApplied || !empty($search) || !empty($brand) || !empty($class) || ($filtersApplied && $state !== 'all'))
                            <button 
                                type="button"
                                wire:click="clearFilters"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Clear Filters
                            </button>
                        @endif
                    </div>
                </div>
                
                <!-- Active Filters Removed -->

                <!-- Products list -->
                <div>
                    @if(empty($products))
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                            <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter options.</p>
                            <div class="mt-6">
                                <button wire:click="clearFilters" type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    @else
                        <!-- Initial Loading Indicator -->
                        <div wire:loading wire:target="loadProducts, resetProducts, clearFilters" class="w-full">
                            <div class="flex justify-center py-12">
                                <svg class="animate-spin h-10 w-10 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Spacer -->
                        <div class="mb-4"></div>
                        
                        <!-- Card Grid View -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach($products as $product)
                                <div 
                                    class="bg-white rounded-lg shadow overflow-hidden border border-gray-200 hover:shadow-md transition-shadow duration-300"
                                    wire:key="product-{{ $product['id'] }}"
                                >
                                    <div class="p-5">
                                        <!-- Header: Description -->
                                        <div class="mb-2">
                                            <h3 class="text-lg font-bold text-gray-900 leading-tight min-h-[3rem] overflow-hidden line-clamp-2">{{ $product['description'] }}</h3>
                                        </div>
                                        
                                        <div class="border-t border-gray-100 pt-1">
                                            <!-- Brand -->
                                            <div class="flex items-center mb-1">
                                                <span class="text-xs font-semibold text-gray-500 w-20">Brand:</span>
                                                <span class="text-sm font-medium text-gray-800">{{ $product['brand'] }}</span>
                                            </div>
                                            
                                            <!-- Category -->
                                            <div class="flex items-center mb-1">
                                                <span class="text-xs font-semibold text-gray-500 w-20">Category:</span>
                                                <span class="text-xs text-gray-700">{{ $product['class'] }}</span>
                                            </div>

                                            <!-- SKU -->
                                            <div class="flex items-center mb-3">
                                                <span class="text-xs font-semibold text-gray-500 w-20">Item #:</span>
                                                <span class="text-xs font-medium text-gray-600">{{ $product['sku'] }}</span>
                                            </div>
                                        </div>

                                        <!-- Inventory Status and Availability -->
                                        <div class="flex flex-wrap justify-end items-center gap-2 mb-3">
                                            <!-- State Availability -->
                                            @php
                                                $state = $product['state'] ?? '';
                                                $quantity = $product['quantity'] ?? 0;
                                                $flPrice = $product['fl_price'] ?? null;
                                                $gaPrice = $product['ga_price'] ?? null;
                                                $bulkPrice = $product['bulk_price'] ?? null;
                                                $isUnrestricted = empty($state);
                                                $isAvailableInFlorida = $isUnrestricted || $state === 'Florida';
                                                $isAvailableInGeorgia = $isUnrestricted || $state === 'Georgia';
                                            @endphp

                                            @if($isUnrestricted)
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    All States
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                                    {{ $state }} Only
                                                </span>
                                            @endif
                                            
                                            <!-- Stock Status -->
                                            @if($quantity > 0)
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    In Stock
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                    Out of Stock
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <!-- Pricing Grid - Conditionally displayed based on auth status/permissions -->
                                        <div class="bg-gray-50 p-3 rounded-md mt-4 border border-gray-200">
                                            <h4 class="text-xs font-semibold uppercase text-gray-500 mb-2 border-b border-gray-200 pb-1">Pricing</h4>
                                            
                                            @if(auth()->check() && (auth()->user()->canViewFloridaItems() || auth()->user()->canViewGeorgiaItems() || auth()->user()->canViewUnrestrictedItems()))
                                            <!-- Pricing shown only to authenticated users with 'view pricing' permission -->
                                            <div class="grid grid-cols-3 gap-2">
                                                <!-- FL Price -->
                                                <div class="text-center">
                                                    <div class="text-xs font-medium text-gray-600 mb-1">Florida</div>
                                                    @if(auth()->check() && auth()->user()->canViewFloridaItems())
                                                        @if($isAvailableInFlorida)
                                                            @php 
                                                                // Check if price exists or is zero/null/empty string
                                                                $hasFlPrice = isset($flPrice) && $flPrice !== null && $flPrice !== '' && $flPrice > 0;
                                                            @endphp
                                                            
                                                            @if($hasFlPrice)
                                                                <div class="font-bold text-sm">${{ number_format($flPrice, 2) }}</div>
                                                            @else
                                                                <div class="text-gray-400 text-xs">N/A</div>
                                                            @endif
                                                        @else
                                                            <div class="text-red-500 text-xs uppercase font-semibold">Not Available</div>
                                                        @endif
                                                    @else
                                                        <div class="text-red-500 text-xs uppercase font-semibold">Restricted</div>
                                                    @endif
                                                </div>
                                                
                                                <!-- GA Price -->
                                                <div class="text-center">
                                                    <div class="text-xs font-medium text-gray-600 mb-1">Georgia</div>
                                                    @if(auth()->check() && auth()->user()->canViewGeorgiaItems())
                                                        @if($isAvailableInGeorgia)
                                                            @php 
                                                                // Check if price exists or is zero/null/empty string
                                                                $hasGaPrice = isset($gaPrice) && $gaPrice !== null && $gaPrice !== '' && $gaPrice > 0;
                                                            @endphp
                                                            
                                                            @if($hasGaPrice)
                                                                <div class="font-bold text-sm">${{ number_format($gaPrice, 2) }}</div>
                                                            @else
                                                                <div class="text-gray-400 text-xs">N/A</div>
                                                            @endif
                                                        @else
                                                            <div class="text-red-500 text-xs uppercase font-semibold">Not Available</div>
                                                        @endif
                                                    @else
                                                        <div class="text-red-500 text-xs uppercase font-semibold">Restricted</div>
                                                    @endif
                                                </div>
                                                
                                                <!-- Bulk Price -->
                                                <div class="text-center">
                                                    <div class="text-xs font-medium text-gray-600 mb-1">Bulk Discount</div>
                                                    @if($bulkPrice)
                                                        <div class="font-bold text-sm">${{ number_format($bulkPrice, 2) }}</div>
                                                    @else
                                                        <div class="text-gray-400 text-xs">N/A</div>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- Add to Cart Button with Quantity Selector -->
                                            <livewire:cart.add-to-cart 
                                                :inventory-id="$product['id']" 
                                                :wire:key="'add-to-cart-'.$product['id']"
                                                quantity-input-type="stepper" 
                                            />
                                            @else
                                            <!-- Message for guests or users without pricing permission -->
                                            <div class="py-3 text-center">
                                                <p class="text-sm text-gray-500">Login to see pricing information</p>
                                                <a href="{{ route('login') }}" class="inline-block mt-2 px-4 py-2 text-xs text-white bg-red-600 rounded-md hover:bg-red-700">Log In</a>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Infinite Scroll Controls -->
                        <div class="mt-8 text-center" 
                             x-data="{ 
                                 observe() {
                                    const observer = new IntersectionObserver((entries) => {
                                       entries.forEach(entry => {
                                           if (entry.isIntersecting) {
                                               @this.loadMore()
                                           }
                                       })
                                    }, { rootMargin: '100px' })
                                   
                                    observer.observe(this.$el)
                                 }
                             }"
                             x-init="observe"
                        >
                            <!-- Loading Indicator -->
                            <div wire:loading wire:target="loadMore" class="py-4">
                                <svg class="animate-spin h-6 w-6 text-gray-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="ml-2 text-sm text-gray-600">Loading more products...</span>
                            </div>

                            <!-- End of Results Message -->
                            <div x-show="!@js($hasMorePages)" class="py-4 text-sm text-gray-600">
                                @if($totalCount === 0)
                                    No products found
                                @elseif($loadedCount === 1)
                                    Showing 1 product
                                @else
                                    Showing all {{ $loadedCount }} products
                                @endif
                            </div>
                            
                        </div>
                    @endif
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
                                wire:model="search" 
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
                @if(!empty($search) || !empty($brand) || !empty($class) || ($filtersApplied && $state !== 'all'))
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
                        
                        @if($this->isStateFilterActive())
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                State: {{ ucfirst($state) }}
                                <button type="button" wire:click="removeFilter('state')" class="ml-1 text-blue-500 hover:text-blue-600">
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
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>