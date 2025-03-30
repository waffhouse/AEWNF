@props([
    'product', 
    'variant' => 'default',  // Options: 'default', 'compact', 'list'
    'showDetails' => true,
    'showQuantity' => true,
    'showPrice' => true,
    'itemKey' => null
])

@php
    // Determine if product is an object or array and extract properties accordingly
    $isArray = is_array($product);
    
    // Get ID in a way that works for both arrays and objects
    $productId = $isArray ? $product['id'] : $product->id;
    
    // Calculate unique key for this instance
    $uniqueKey = $itemKey ?? ('product-' . $productId . '-' . uniqid());
    
    // Extract common product information
    $description = $isArray ? $product['description'] : $product->description;
    $sku = $isArray ? ($product['sku'] ?? '') : ($product->sku ?? '');
    $brand = $isArray ? ($product['brand'] ?? '') : ($product->brand ?? '');
    // Handle NULL quantities properly by treating them as zero
    $rawQuantity = $isArray ? ($product['quantity'] ?? null) : ($product->quantity ?? null);
    $quantity = is_null($rawQuantity) ? 0 : $rawQuantity;
    
    // Price calculation
    $primaryPrice = null;
    $priceLabel = '';
    
    if(Auth::check()) {
        if(Auth::user()->canViewFloridaItems()) {
            $fl_price = $isArray ? ($product['fl_price'] ?? null) : ($product->fl_price ?? null);
            if($fl_price) {
                $primaryPrice = $fl_price;
                $priceLabel = 'FL';
            }
        } elseif(Auth::user()->canViewGeorgiaItems()) {
            $ga_price = $isArray ? ($product['ga_price'] ?? null) : ($product->ga_price ?? null);
            if($ga_price) {
                $primaryPrice = $ga_price;
                $priceLabel = 'GA';
            }
        }
    }
    
    // Cart information
    $isInCart = false;
    $cartQuantity = 0;
    $userCart = auth()->check() ? auth()->user()->cart : null;
    if ($userCart && $productId) {
        $cartItem = $userCart->items()->where('inventory_id', $productId)->first();
        if ($cartItem) {
            $isInCart = true;
            $cartQuantity = $cartItem->quantity;
        }
    }
    
    // Set modal ID
    $modalId = 'product-detail-' . $productId;
@endphp

{{-- Compact variant (used in dashboard and lists) --}}
@if($variant === 'compact')
    <div class="flex flex-col sm:flex-row" wire:key="{{ $uniqueKey }}">
        <div class="flex-grow pr-2 mb-2 sm:mb-0">
            <h5 class="text-xs font-medium text-gray-800 truncate max-w-[180px] sm:max-w-full">{{ $description }}</h5>
            @if($showPrice && $primaryPrice)
                <span class="text-xs text-gray-900">${{ number_format($primaryPrice, 2) }}</span>
            @endif
        </div>
        <div class="flex items-center space-x-2 self-start sm:self-center">
            @if($showQuantity && Auth::check() && Auth::user()->can('add to cart') && $primaryPrice)
                <div class="flex items-center space-x-1" @click.stop>
                    <livewire:cart.add-to-cart 
                        :inventory-id="$productId" 
                        :wire:key="'compact-add-to-cart-'.$productId.'-'.uniqid()"
                        quantity-input-type="stepper"
                        variant="compact"
                        show-quantity="true"
                        class="flex-1"
                    />
                </div>
            @endif
            
            @if($showDetails)
                <button
                    type="button"
                    x-data
                    @click="$dispatch('open-modal', '{{ $modalId }}')"
                    class="inline-flex items-center px-2 py-1 bg-red-50 border border-red-200 rounded text-xs font-medium text-red-700 hover:bg-red-100 whitespace-nowrap"
                >
                    View Details
                </button>
                {{-- Include the modal component --}}
                <x-product-detail-modal :product="$product" :modalId="$modalId" />
            @endif
        </div>
    </div>

{{-- List variant (used in search results or similar rows) --}}
@elseif($variant === 'list')
    <div class="border-b border-gray-100 py-3 px-4" wire:key="{{ $uniqueKey }}">
        <div class="flex flex-col sm:flex-row sm:items-center">
            <div class="flex-grow">
                <h5 class="text-sm font-medium text-gray-800">{{ $description }}</h5>
                <div class="flex flex-wrap gap-2 mt-1">
                    @if($sku)
                        <span class="text-xs text-gray-500">{{ $sku }}</span>
                    @endif
                    @if($brand)
                        <span class="text-xs text-gray-500">{{ $brand }}</span>
                    @endif
                    @if($showPrice && $primaryPrice)
                        <span class="text-xs font-medium text-red-600">${{ number_format($primaryPrice, 2) }}</span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3 mt-3 sm:mt-0">
                @if($showQuantity && Auth::check() && Auth::user()->can('add to cart') && $primaryPrice)
                    <div class="flex items-center space-x-1" @click.stop>
                        <livewire:cart.add-to-cart 
                            :inventory-id="$productId" 
                            :wire:key="'list-add-to-cart-'.$productId.'-'.uniqid()"
                            quantity-input-type="stepper"
                            variant="compact"
                            show-quantity="true"
                            class="flex-1"
                        />
                    </div>
                @endif
                
                @if($showDetails)
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-modal', '{{ $modalId }}')"
                        class="inline-flex items-center px-3 py-1.5 bg-red-50 border border-red-200 rounded text-xs font-medium text-red-700 hover:bg-red-100 whitespace-nowrap"
                    >
                        View Details
                    </button>
                    {{-- Include the modal component --}}
                    <x-product-detail-modal :product="$product" :modalId="$modalId" />
                @endif
            </div>
        </div>
    </div>

{{-- Default variant (card style from product-card) --}}
@else
    <div class="h-full" wire:key="{{ $uniqueKey }}">
        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200 hover:shadow-md transition-shadow duration-300 h-full flex flex-col">
            <div class="p-4 flex flex-col h-full">
                <!-- Header: Description and stock status -->
                <div class="mb-2">
                    <h3 class="text-lg font-bold text-gray-900 leading-tight overflow-hidden line-clamp-2">{{ $description }}</h3>
                    <!-- Stock status badge -->
                    <div class="mt-1 flex flex-wrap gap-2">
                        @if($quantity > 0)
                            <span class="inline-block px-1.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">In Stock</span>
                        @else
                            <span class="inline-block px-1.5 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">Out of Stock</span>
                        @endif
                        
                        @if($isInCart)
                            <span class="inline-block px-1.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                <span class="font-semibold">{{ $cartQuantity }}</span> <span class="ml-1">in cart</span>
                            </span>
                        @endif
                    </div>
                </div>
                
                <!-- Product info and price in one row -->
                <div class="border-t border-gray-100 py-2">
                    <div class="flex justify-between items-center">
                        <!-- SKU and brand info -->
                        <div class="flex items-center text-xs font-medium text-gray-600">
                            @if($sku)
                                {{ $sku }}
                                @if($brand)
                                    <span class="mx-1">•</span> 
                                @endif
                            @endif
                            @if($brand)
                                {{ $brand }}
                            @endif
                        </div>
                        
                        <div class="flex items-center">
                            <!-- Primary price for authenticated users -->
                            @if($showPrice && Auth::check() && $primaryPrice)
                                <div class="font-bold text-sm text-red-600">
                                    {{ $priceLabel }}: ${{ number_format($primaryPrice, 2) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Add to cart -->
                    @if($showQuantity)
                        <div class="flex justify-center items-center mt-2">
                            @if(Auth::check() && $primaryPrice)
                                <div class="flex items-center space-x-1" @click.stop>
                                    <livewire:cart.add-to-cart 
                                        :inventory-id="$productId" 
                                        :wire:key="'card-add-to-cart-'.$productId.'-'.uniqid()"
                                        quantity-input-type="stepper"
                                        variant="compact"
                                        show-quantity="true"
                                        class="flex-1"
                                    />
                                </div>
                            @elseif(Auth::check())
                                <button 
                                    type="button"
                                    class="px-2 py-1 text-xs font-medium text-white bg-gray-400 rounded cursor-not-allowed"
                                    disabled
                                >
                                    Not Available
                                </button>
                            @else
                                <a href="{{ route('login') }}" @click.stop class="px-2 py-1 text-xs font-medium text-white bg-gray-600 hover:bg-gray-700 rounded inline-block">Log In</a>
                            @endif
                        </div>
                    @endif
                    
                    <!-- View details link -->
                    @if($showDetails)
                        <div class="text-center mt-2">
                            <button 
                                type="button"
                                class="text-xs text-blue-600 hover:text-blue-800 font-medium cursor-pointer"
                                @click="$dispatch('open-modal', '{{ $modalId }}')"
                            >View Details</button>
                        </div>
                        {{-- Include the modal component --}}
                        <x-product-detail-modal :product="$product" :modalId="$modalId" />
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif