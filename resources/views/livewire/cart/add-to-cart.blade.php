<div data-inventory-id="{{ $inventoryId }}">
    <style>
        /* Hide browser's native number input spinner buttons */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
    @auth
        @can('add to cart')
            <div class="mt-2">
                @if($isInCart)
                    <div class="flex flex-col space-y-2">
                        <div class="flex rounded-md overflow-hidden border border-gray-300">
                            <button 
                                type="button"
                                wire:click.stop="decrementQuantity" 
                                wire:loading.attr="disabled" 
                                aria-label="Decrease quantity"
                                class="w-8 px-2 py-1 bg-gray-100 text-gray-600 hover:bg-gray-200 flex items-center justify-center relative"
                            >
                                <span class="font-bold text-sm">−</span>
                                <div wire:loading.flex wire:target="decrementQuantity" class="absolute inset-0 bg-gray-200 bg-opacity-50 flex items-center justify-center">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </button>
                            <input 
                                type="number" 
                                wire:model.blur="quantity" 
                                wire:change="addToCart" 
                                min="0"
                                max="{{ $maxQuantity }}"
                                class="w-14 text-center bg-white py-1 outline-none border-x border-gray-200"
                                name="quantity" 
                                id="quantity-{{ $inventoryId }}"
                            >
                            <button
                                type="button"
                                wire:click.stop="incrementQuantity" 
                                wire:loading.attr="disabled" 
                                aria-label="Increase quantity"
                                class="w-8 px-2 py-1 bg-gray-100 text-gray-600 hover:bg-gray-200 flex items-center justify-center relative"
                            >
                                <span class="font-bold text-sm">+</span>
                                <div wire:loading.flex wire:target="incrementQuantity" class="absolute inset-0 bg-gray-200 bg-opacity-50 flex items-center justify-center">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col space-y-2">
                        <div class="flex rounded-md overflow-hidden border border-gray-300">
                            <button 
                                type="button"
                                wire:click.stop="decrementQuantity" 
                                wire:loading.attr="disabled" 
                                aria-label="Decrease quantity"
                                class="w-8 px-2 py-1 bg-gray-100 text-gray-600 hover:bg-gray-200 flex items-center justify-center relative"
                            >
                                <span class="font-bold text-sm">−</span>
                                <div wire:loading.flex wire:target="decrementQuantity" class="absolute inset-0 bg-gray-200 bg-opacity-50 flex items-center justify-center">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </button>
                            <input 
                                type="number" 
                                wire:model.blur="quantity" 
                                wire:change="addToCart" 
                                min="0"
                                max="{{ $maxQuantity }}"
                                class="w-14 text-center bg-white py-1 outline-none border-x border-gray-200"
                                name="quantity" 
                                id="quantity-{{ $inventoryId }}"
                            >
                            <button
                                type="button"
                                wire:click.stop="incrementQuantity" 
                                wire:loading.attr="disabled" 
                                aria-label="Increase quantity"
                                class="w-8 px-2 py-1 bg-gray-100 text-gray-600 hover:bg-gray-200 flex items-center justify-center relative"
                            >
                                <span class="font-bold text-sm">+</span>
                                <div wire:loading.flex wire:target="incrementQuantity" class="absolute inset-0 bg-gray-200 bg-opacity-50 flex items-center justify-center">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </button>
                        </div>
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
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md flex items-center justify-center"
            >
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Log In to Order
            </a>
        </div>
    @endauth
</div>