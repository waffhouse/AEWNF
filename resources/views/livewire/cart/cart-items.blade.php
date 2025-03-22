<div>
    @if(count($cartItems) === 0)
        <div class="bg-gray-50 py-10 px-6 rounded-lg text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Your cart is empty</h3>
            <p class="mt-1 text-sm text-gray-500">Browse our product catalog to add items to your cart.</p>
            <div class="mt-6">
                <a href="{{ route('inventory.catalog') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Continue Shopping
                </a>
            </div>
        </div>
    @else
        <!-- Desktop Table View - Hidden on Small Screens -->
        <div class="hidden sm:block overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Price
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Quantity
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Subtotal
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($cartItems as $item)
                        <tr wire:key="cart-item-desktop-{{ $item->id }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $item->inventory->description ?? 'Unknown Product' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $item->inventory->brand ?? 'Unknown Brand' }}
                                            @if ($item->inventory && $item->inventory->sku)
                                                (SKU: {{ $item->inventory->sku }})
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">${{ number_format($item->price, 2) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <button 
                                        type="button"
                                        wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                        class="border border-gray-300 rounded-l px-3 py-1 bg-gray-50 hover:bg-gray-100 focus:outline-none"
                                    >-</button>
                                    <input 
                                        type="number" 
                                        readonly
                                        value="{{ $item->quantity }}" 
                                        class="border-t border-b border-gray-300 text-center w-12 px-2 py-1 focus:outline-none focus:ring-0"
                                    >
                                    <button 
                                        type="button"
                                        wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                        class="border border-gray-300 rounded-r px-3 py-1 bg-gray-50 hover:bg-gray-100 focus:outline-none"
                                    >+</button>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                ${{ number_format($item->price * $item->quantity, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <button 
                                    type="button"
                                    wire:click="removeItem({{ $item->id }})"
                                    wire:confirm="Are you sure you want to remove this item from your cart?"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Card View - Visible only on Small Screens -->
        <div class="sm:hidden space-y-4">
            @foreach($cartItems as $item)
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm" wire:key="cart-item-mobile-{{ $item->id }}">
                    <div class="p-4">
                        <!-- Product Info -->
                        <div class="mb-3">
                            <h3 class="text-sm font-medium text-gray-900 line-clamp-2">
                                {{ $item->inventory->description ?? 'Unknown Product' }}
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $item->inventory->brand ?? 'Unknown Brand' }}
                                @if ($item->inventory && $item->inventory->sku)
                                    <span class="ml-1">(SKU: {{ $item->inventory->sku }})</span>
                                @endif
                            </p>
                        </div>
                        
                        <!-- Price and Quantity Controls -->
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-sm font-medium text-gray-900">
                                <span class="text-gray-500 mr-1">Price:</span> 
                                ${{ number_format($item->price, 2) }}
                            </div>
                            
                            <button 
                                type="button"
                                wire:click="removeItem({{ $item->id }})"
                                wire:confirm="Are you sure you want to remove this item from your cart?"
                                class="text-red-600 hover:text-red-900 p-1"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <!-- Quantity Controls -->
                            <div class="flex items-center">
                                <button 
                                    type="button"
                                    wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                    class="border border-gray-300 rounded-l px-3 py-1 bg-gray-50 hover:bg-gray-100 focus:outline-none"
                                >-</button>
                                <input 
                                    type="number" 
                                    readonly
                                    value="{{ $item->quantity }}" 
                                    class="border-t border-b border-gray-300 text-center w-12 px-2 py-1 focus:outline-none focus:ring-0"
                                >
                                <button 
                                    type="button"
                                    wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                    class="border border-gray-300 rounded-r px-3 py-1 bg-gray-50 hover:bg-gray-100 focus:outline-none"
                                >+</button>
                            </div>
                            
                            <!-- Subtotal -->
                            <div class="text-sm font-medium text-gray-900">
                                <span class="text-gray-500 mr-1">Subtotal:</span> 
                                <span class="text-red-600">${{ number_format($item->price * $item->quantity, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-6">
            <a href="{{ route('inventory.catalog') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Continue Shopping
            </a>
        </div>
    @endif
</div>