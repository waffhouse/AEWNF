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
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Cart Items -->
                        <div class="w-full md:w-2/3">
                            <livewire:cart.cart-items :cartItems="$cartItems" />
                        </div>
                        
                        <!-- Order Summary -->
                        <div class="w-full md:w-1/3">
                            <livewire:cart.order-summary 
                                :cart="$cart" 
                                :total="$total" 
                                :itemCount="$itemCount" 
                            />
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Order Confirmation Modal -->
    <livewire:cart.order-confirmation />
</div>