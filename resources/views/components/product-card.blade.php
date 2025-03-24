@props(['product'])

<!-- Include the product detail modal -->
<x-product-detail-modal :product="$product" />

<div class="h-full">
    <div 
        class="bg-white rounded-lg shadow overflow-hidden border border-gray-200 hover:shadow-md transition-shadow duration-300 h-full flex flex-col"
    >
        <div class="p-4 flex flex-col h-full">
            <!-- Extract product information variables first -->
            @php
                $state = $product['state'] ?? '';
                $quantity = $product['quantity'] ?? 0;
                $flPrice = $product['fl_price'] ?? null;
                $gaPrice = $product['ga_price'] ?? null;
                $bulkPrice = $product['bulk_price'] ?? null;
                $isUnrestricted = empty($state);
                $isAvailableInFlorida = $isUnrestricted || $state === 'Florida';
                $isAvailableInGeorgia = $isUnrestricted || $state === 'Georgia';
                $productId = $product['id'];
            @endphp
            
            <!-- Header: Description and stock status -->
            <div class="mb-2">
                <h3 class="text-lg font-bold text-gray-900 leading-tight overflow-hidden line-clamp-2">{{ $product['description'] }}</h3>
                <!-- Stock status badge -->
                <div class="mt-1 flex flex-wrap gap-2">
                    @if($quantity > 0)
                        <span class="inline-block px-1.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">In Stock</span>
                    @else
                        <span class="inline-block px-1.5 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">Out of Stock</span>
                    @endif
                    
                    <!-- Cart status badge -->
                    @php
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
                    @endphp
                    
                    @if($isInCart)
                        <span class="inline-block px-1.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            <span class="font-semibold">{{ $cartQuantity }}</span> <span class="ml-1">in cart</span>
                        </span>
                    @endif
                </div>
            </div>
            
            <!-- Calculate if we have a price to show -->
            @php
                $primaryPrice = null;
                $priceLabel = '';
                
                if(auth()->check()) {
                    // Determine which price to show based on availability and permissions
                    if(auth()->user()->canViewFloridaItems() && $isAvailableInFlorida && isset($flPrice) && $flPrice > 0) {
                        $primaryPrice = $flPrice;
                        $priceLabel = 'FL';
                    } elseif(auth()->user()->canViewGeorgiaItems() && $isAvailableInGeorgia && isset($gaPrice) && $gaPrice > 0) {
                        $primaryPrice = $gaPrice;
                        $priceLabel = 'GA';
                    }
                }
            @endphp
            
            <!-- Product info and price in one row -->
            <div class="border-t border-gray-100 py-2">
                <div class="flex justify-between items-center">
                    <!-- SKU and brand info -->
                    <div class="flex items-center text-xs font-medium text-gray-600">
                        {{ $product['sku'] }}
                        <span class="mx-1">•</span> 
                        {{ $product['brand'] }}
                    </div>
                    
                    <div class="flex items-center">
                        
                        <!-- Primary price for authenticated users -->
                        @if(auth()->check() && $primaryPrice)
                            <div class="font-bold text-sm text-red-600">
                                {{ $priceLabel }}: ${{ number_format($primaryPrice, 2) }}
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Quantity selector and add to cart -->
                <div class="flex justify-center items-center mt-2">
                    <!-- Quantity selector and add to cart for authenticated users with pricing permission -->
                    @if(auth()->check() && $primaryPrice)
                        <div class="flex items-center space-x-1" @click.stop>
                            <livewire:cart.add-to-cart 
                                :inventory-id="$product['id']" 
                                :wire:key="'card-add-to-cart-'.$product['id'].'-'.uniqid()"
                                quantity-input-type="stepper"
                                variant="compact"
                                show-quantity="true"
                                class="flex-1"
                            />
                        </div>
                    @elseif(auth()->check())
                        <!-- Not available button for authenticated users without pricing -->
                        <button 
                            type="button"
                            class="px-2 py-1 text-xs font-medium text-white bg-gray-400 rounded cursor-not-allowed"
                            disabled
                        >
                            Not Available
                        </button>
                    @else
                        <!-- Login button for guests -->
                        <a href="{{ route('login') }}" @click.stop class="px-2 py-1 text-xs font-medium text-white bg-gray-600 hover:bg-gray-700 rounded inline-block">Log In</a>
                    @endif
                </div>
                
                <!-- View details link -->
                <div class="text-center mt-2">
                    <button 
                        type="button"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium cursor-pointer"
                        @click="$dispatch('open-modal', 'product-detail-{{ $product['id'] }}')"
                    >View Details</button>
                </div>
            </div>
        </div>
    </div>
</div>