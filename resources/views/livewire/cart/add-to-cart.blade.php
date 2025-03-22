<div>
    @auth
        @can('add to cart')
            <div class="mt-2">
                @if($isInCart)
                    <div class="space-y-2">
                        @if($showQuantity)
                            <div class="flex items-center justify-center">
                                <label for="quantity" class="text-xs text-gray-700 mr-2">Qty:</label>
                                <div class="custom-number-input h-8 w-32">
                                    <div class="flex flex-row h-8 w-full rounded-lg relative bg-transparent">
                                        <button 
                                            type="button"
                                            wire:click="decrementQuantity"
                                            class="bg-gray-200 text-gray-600 hover:text-gray-700 hover:bg-gray-300 h-full w-20 rounded-l cursor-pointer"
                                        >
                                            <span class="m-auto text-lg font-thin">−</span>
                                        </button>
                                        @if($quantityInputType === 'stepper')
                                            <input 
                                                type="number" 
                                                wire:model.live="quantity" 
                                                min="1"
                                                max="{{ $maxQuantity }}"
                                                readonly
                                                class="outline-none focus:outline-none text-center w-full bg-gray-100 font-semibold text-md hover:text-black focus:text-black md:text-base cursor-default flex items-center text-gray-700"
                                                name="quantity" 
                                            >
                                        @else
                                            <input 
                                                type="number" 
                                                wire:model.blur="quantity" 
                                                min="1"
                                                max="{{ $maxQuantity }}"
                                                class="outline-none focus:outline-none text-center w-full bg-white border border-gray-300 font-semibold text-md hover:text-black focus:text-black md:text-base flex items-center text-gray-700"
                                                name="quantity" 
                                            >
                                        @endif
                                        <button
                                            type="button"
                                            wire:click="incrementQuantity"
                                            class="bg-gray-200 text-gray-600 hover:text-gray-700 hover:bg-gray-300 h-full w-20 rounded-r cursor-pointer"
                                        >
                                            <span class="m-auto text-lg font-thin">+</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <button 
                            type="button"
                            wire:click="addToCart"
                            wire:loading.attr="disabled"
                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center justify-center text-sm font-medium transition-colors duration-200"
                        >
                            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span wire:loading.remove>Update Cart</span>
                            <span wire:loading>Updating...</span>
                        </button>
                    </div>
                @else
                    <div class="space-y-2">
                        @if($showQuantity)
                            <div class="flex items-center justify-center">
                                <label for="quantity" class="text-xs text-gray-700 mr-2">Qty:</label>
                                <div class="custom-number-input h-8 w-32">
                                    <div class="flex flex-row h-8 w-full rounded-lg relative bg-transparent">
                                        <button 
                                            type="button"
                                            wire:click="decrementQuantity"
                                            class="bg-gray-200 text-gray-600 hover:text-gray-700 hover:bg-gray-300 h-full w-20 rounded-l cursor-pointer"
                                        >
                                            <span class="m-auto text-lg font-thin">−</span>
                                        </button>
                                        @if($quantityInputType === 'stepper')
                                            <input 
                                                type="number" 
                                                wire:model.live="quantity" 
                                                min="1"
                                                max="{{ $maxQuantity }}"
                                                readonly
                                                class="outline-none focus:outline-none text-center w-full bg-gray-100 font-semibold text-md hover:text-black focus:text-black md:text-base cursor-default flex items-center text-gray-700"
                                                name="quantity" 
                                            >
                                        @else
                                            <input 
                                                type="number" 
                                                wire:model.blur="quantity" 
                                                min="1"
                                                max="{{ $maxQuantity }}"
                                                class="outline-none focus:outline-none text-center w-full bg-white border border-gray-300 font-semibold text-md hover:text-black focus:text-black md:text-base flex items-center text-gray-700"
                                                name="quantity" 
                                            >
                                        @endif
                                        <button
                                            type="button"
                                            wire:click="incrementQuantity"
                                            class="bg-gray-200 text-gray-600 hover:text-gray-700 hover:bg-gray-300 h-full w-20 rounded-r cursor-pointer"
                                        >
                                            <span class="m-auto text-lg font-thin">+</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <button 
                            type="button"
                            wire:click="addToCart"
                            wire:loading.attr="disabled"
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded flex items-center justify-center text-sm font-medium transition-colors duration-200"
                        >
                            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span wire:loading.remove>Add to Cart</span>
                            <span wire:loading>Adding...</span>
                        </button>
                    </div>
                @endif
            </div>
        @else
            <!-- Display message for users without cart permission -->
            <div class="mt-2 text-xs text-gray-500 text-center">
                Contact support for information on placing orders
            </div>
        @endcan
    @else
        <!-- Display login button for guests -->
        <div class="mt-2">
            <a 
                href="{{ route('login') }}" 
                class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded flex items-center justify-center text-sm font-medium transition-colors duration-200"
            >
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Log In to Order
            </a>
        </div>
    @endauth
</div>