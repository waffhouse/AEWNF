<div>
    <!-- Main Filter Section (visible on medium and larger screens) -->
    <div class="mb-6 border border-gray-200 rounded-lg p-4 bg-gray-50 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                
                <!-- Desktop Search (hidden on small screens) -->
                <div class="relative">
                    <input 
                        type="search" 
                        id="search" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search products..." 
                        class="block w-full pl-8 py-2 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-gray-400"
                    >
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <!-- Brand Filter -->
            <div>
                <label for="brand-filter" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                <select 
                    id="brand-filter" 
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
                <label for="class-filter" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select 
                    id="class-filter" 
                    wire:model.live="class"
                    class="block w-full py-2 text-sm border-gray-300 rounded-md focus:ring-0 focus:border-gray-400"
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
        </div>
        
        <!-- Redesigned Filter Actions with Status Display -->
        <div class="mt-4 flex flex-col sm:flex-row gap-2 items-center justify-between">
            <!-- Filter Status Indicator -->
            @php
                $activeFilterCount = 0;
                if (!empty($search)) $activeFilterCount++;
                if (!empty($brand)) $activeFilterCount++;
                if (!empty($class)) $activeFilterCount++;
            @endphp
            <div class="text-sm text-gray-500 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 {{ $activeFilterCount > 0 ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                @if($activeFilterCount > 0)
                    <span>{{ $activeFilterCount }} filter{{ $activeFilterCount !== 1 ? 's' : '' }} currently applied</span>
                @else
                    <span>No filters currently applied</span>
                @endif
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-2">
                @if($activeFilterCount > 0)
                    <button 
                        type="button"
                        wire:click="clearFilters"
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

    <!-- Active Filters Section -->
    @if(!empty($search) || !empty($brand) || !empty($class))
    <div class="mb-4 flex flex-wrap gap-2">
        <span class="text-sm font-medium text-gray-700">Active Filters:</span>
        @if(!empty($search))
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                Search: {{ $search }}
                <button type="button" wire:click="removeFilter('search')" class="ml-1 text-blue-500 hover:text-blue-600">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </span>
        @endif
        
        @if(!empty($brand))
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Brand: {{ $brand }}
                <button type="button" wire:click="removeFilter('brand')" class="ml-1 text-green-500 hover:text-green-600">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </span>
        @endif
        
        @if(!empty($class))
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                Category: {{ $class }}
                <button type="button" wire:click="removeFilter('class')" class="ml-1 text-purple-500 hover:text-purple-600">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </span>
        @endif
    </div>
    @endif
</div>