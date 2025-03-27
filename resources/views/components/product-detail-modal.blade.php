@props(['product', 'modalId' => null])

<x-modal name="{{ $modalId ?? 'product-detail-'.$product['id'] }}" maxWidth="lg"
>
    <div class="p-6">
        <!-- Header with close button -->
        <div class="flex justify-between items-start mb-4">
            <h2 class="text-xl font-bold text-gray-900 pr-8">{{ $product['description'] }}</h2>
            <button 
                type="button" 
                @click="$dispatch('close-modal', '{{ $modalId ?? 'product-detail-'.$product['id'] }}')"
                class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors"
                aria-label="Close product details"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <!-- Extract product information variables -->
        @php
            $state = $product['state'] ?? '';
            $quantity = $product['quantity'] ?? 0;
            $flPrice = $product['fl_price'] ?? null;
            $gaPrice = $product['ga_price'] ?? null;
            $bulkPrice = $product['bulk_price'] ?? null;
            $isUnrestricted = empty($state);
            $isAvailableInFlorida = $isUnrestricted || $state === 'Florida';
            $isAvailableInGeorgia = $isUnrestricted || $state === 'Georgia';
            $hasFlPrice = isset($flPrice) && $flPrice !== null && $flPrice !== '' && $flPrice > 0;
            $hasGaPrice = isset($gaPrice) && $gaPrice !== null && $gaPrice !== '' && $gaPrice > 0;
        @endphp
        
        <!-- Product information grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left column: Product details -->
            <div>
                <div class="rounded-lg border border-gray-200 p-4 mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase">Product Details</h3>
                    
                    <div class="space-y-2">
                        <div class="flex items-start">
                            <span class="text-xs font-semibold text-gray-500 w-24">Item #:</span>
                            <span class="text-sm font-medium text-gray-800">{{ $product['sku'] }}</span>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="text-xs font-semibold text-gray-500 w-24">Brand:</span>
                            <span class="text-sm font-medium text-gray-800">{{ $product['brand'] }}</span>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="text-xs font-semibold text-gray-500 w-24">Category:</span>
                            <span class="text-sm text-gray-700">{{ $product['class'] }}</span>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="text-xs font-semibold text-gray-500 w-24">Availability:</span>
                            <span class="text-sm text-gray-700">
                                @if($quantity > 0)
                                    <span class="font-medium text-green-700">
                                        In Stock
                                    </span>
                                @else
                                    <span class="font-medium text-red-700">
                                        Out of Stock
                                    </span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right column: Pricing -->
            <div>
                <div class="rounded-lg border border-gray-200 p-4 mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase">Pricing</h3>
                    
                    @if(auth()->check() && (auth()->user()->canViewFloridaItems() || auth()->user()->canViewGeorgiaItems() || auth()->user()->canViewUnrestrictedItems()))
                        <div class="space-y-3">
                            <!-- Florida Price -->
                            @if(auth()->check() && auth()->user()->canViewFloridaItems())
                                <div class="flex justify-between items-center py-1 border-b border-gray-100">
                                    <div class="text-sm font-semibold">Florida Price</div>
                                    @if($isAvailableInFlorida && $hasFlPrice)
                                        <div class="text-lg font-bold text-red-600">${{ number_format($flPrice, 2) }}</div>
                                    @elseif($isAvailableInFlorida)
                                        <div class="text-gray-400 text-sm">Not Available</div>
                                    @else
                                        <div class="text-red-500 text-sm uppercase font-semibold">Restricted</div>
                                    @endif
                                </div>
                            @endif
                            
                            <!-- Georgia Price -->
                            @if(auth()->check() && auth()->user()->canViewGeorgiaItems())
                                <div class="flex justify-between items-center py-1 border-b border-gray-100">
                                    <div class="text-sm font-semibold">Georgia Price</div>
                                    @if($isAvailableInGeorgia && $hasGaPrice)
                                        <div class="text-lg font-bold text-red-600">${{ number_format($gaPrice, 2) }}</div>
                                    @elseif($isAvailableInGeorgia)
                                        <div class="text-gray-400 text-sm">Not Available</div>
                                    @else
                                        <div class="text-red-500 text-sm uppercase font-semibold">Restricted</div>
                                    @endif
                                </div>
                            @endif
                            
                            <!-- Bulk Discount -->
                            @if($bulkPrice)
                                <div class="flex justify-between items-center py-1 border-b border-gray-100">
                                    <div class="text-sm font-semibold">Bulk Discount</div>
                                    <div class="text-lg font-bold text-red-600">${{ number_format($bulkPrice, 2) }}</div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- No Add to Cart in modal - handled on the main card -->
                    @else
                        <!-- Message for guests or users without pricing permission -->
                        <div class="py-4 text-center">
                            <p class="text-sm text-gray-500 mb-3">Login to see pricing information</p>
                            <a href="{{ route('login') }}" class="inline-block px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors">Log In</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-modal>