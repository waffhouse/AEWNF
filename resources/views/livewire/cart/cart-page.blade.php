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
                    <livewire:cart.cart-items :cartItems="$cartItems" />
                @else
                    <!-- Regular layout - full width for both sections -->
                    <div class="w-full">
                        <livewire:cart.cart-items :cartItems="$cartItems" />
                    </div>
                    
                    <!-- Hidden order summary - only needed for form submission -->
                    <div class="hidden">
                        <livewire:cart.order-summary 
                            :cart="$cart" 
                            :total="$total" 
                            :itemCount="$itemCount" 
                        />
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Order Confirmation Modal -->
    <livewire:cart.order-confirmation />
    
    <!-- Fixed Action Button (visible on all screen sizes) -->
    @if(count($cartItems) > 0)
        <div class="fixed bottom-0 left-0 right-0 bg-white p-4 border-t border-gray-200 shadow-lg z-10">
            <div class="container mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="text-sm">
                        <div class="font-bold">Total: ${{ number_format($total, 2) }}</div>
                        <div class="text-xs text-gray-600">{{ $itemCount }} item(s)</div>
                    </div>
                    <button 
                        id="checkout-button"
                        type="button"
                        wire:click="checkout"
                        wire:loading.attr="disabled"
                        wire:confirm="Are you sure you want to place this order?"
                        class="bg-red-600 hover:bg-red-700 text-white py-2 px-6 rounded-md font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center justify-center"
                    >
                        <span wire:loading.remove wire:target="checkout">Place Order</span>
                        <span wire:loading wire:target="checkout">Processing...</span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Add bottom padding to avoid content being hidden behind fixed bar -->
        <div class="h-20"></div>
    @endif
</div>