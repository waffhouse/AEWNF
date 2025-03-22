@props(['product'])

<div 
    class="bg-white rounded-lg shadow overflow-hidden border border-gray-200 hover:shadow-md transition-shadow duration-300"
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