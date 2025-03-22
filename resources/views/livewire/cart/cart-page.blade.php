<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h2 class="text-xl font-semibold">Your Cart</h2>
                        <p class="text-sm text-gray-600 mt-1">Review the items in your cart before checkout.</p>
                    </div>

                    @if(count($cartItems) > 0)
                    <div class="flex space-x-2 mt-4 sm:mt-0">
                        <button 
                            wire:click="clearCart"
                            wire:confirm="Are you sure you want to clear your entire cart?"
                            type="button" 
                            class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 flex items-center"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Clear Cart
                        </button>
                    </div>
                    @endif
                </div>

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
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Cart Items -->
                        <div class="w-full md:w-2/3">
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
                        </div>
                        
                        <!-- Order Summary -->
                        <div class="w-full md:w-1/3">
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 sticky top-20">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h3>
                                
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm text-gray-600">Items ({{ $itemCount }})</span>
                                        <span class="text-sm font-medium text-gray-900">${{ number_format($total, 2) }}</span>
                                    </div>
                                    
                                    <div class="mb-2 text-xs text-gray-500 italic">
                                        Tax exempt - Resale transactions
                                    </div>
                                    
                                    <div class="border-t border-gray-200 my-4"></div>
                                    
                                    <div class="flex justify-between mb-4">
                                        <span class="text-base font-medium text-gray-900">Total</span>
                                        <span class="text-base font-medium text-gray-900">${{ number_format($total, 2) }}</span>
                                    </div>
                                    
                                    @can('place orders')
                                        <div class="mb-4">
                                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Order Notes (Optional)</label>
                                            <textarea 
                                                id="notes" 
                                                wire:model="notes" 
                                                rows="3" 
                                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                                placeholder="Special instructions for your order"
                                            ></textarea>
                                        </div>
                                        
                                        <button 
                                            type="button"
                                            wire:click="checkout"
                                            wire:loading.attr="disabled"
                                            wire:confirm="Are you sure you want to place this order?"
                                            class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center justify-center"
                                        >
                                            <span wire:loading.remove wire:target="checkout">Place Order</span>
                                            <span wire:loading wire:target="checkout">Processing...</span>
                                        </button>
                                    @else
                                        <div class="text-center p-4 bg-yellow-50 rounded-md border border-yellow-200">
                                            <p class="text-sm text-yellow-800">
                                                You do not have permission to place orders. Please contact support.
                                            </p>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Order success redirect is now handled directly in the CartPage component -->
</div>