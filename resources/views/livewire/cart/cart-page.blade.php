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

    <!-- Order Details Modal -->
    @if($viewingOrderDetails && $selectedOrder)
        <div
            x-data="{}"
            x-init="$nextTick(() => { document.body.classList.add('overflow-hidden'); })"
            x-on:keydown.escape.window="$wire.closeOrderDetails()"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Order #{{ $selectedOrder->id }} Details
                                </h3>
                                <div class="ml-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Order Placed Successfully
                                    </span>
                                </div>
                            </div>
                            <button 
                                wire:click="closeOrderDetails" 
                                class="text-gray-400 hover:text-gray-500"
                            >
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Order Information -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Order Information</h4>
                                <div class="space-y-2">
                                    <p><span class="font-medium">Order ID:</span> #{{ $selectedOrder->id }}</p>
                                    <p><span class="font-medium">Date:</span> @formatdate($selectedOrder->created_at)</p>
                                    <p><span class="font-medium">Total:</span> ${{ number_format($selectedOrder->total, 2) }}</p>
                                    <p><span class="font-medium">Items:</span> {{ $selectedOrder->getTotalItems() }}</p>
                                    <p><span class="font-medium">Status:</span> 
                                        @if($selectedOrder->status === \App\Models\Order::STATUS_PENDING)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @elseif($selectedOrder->status === \App\Models\Order::STATUS_COMPLETED)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        @elseif($selectedOrder->status === \App\Models\Order::STATUS_CANCELLED)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Cancelled
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Customer Information -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Customer Information</h4>
                                <div class="space-y-2">
                                    <p><span class="font-medium">Name:</span> {{ Auth::user()->name }}</p>
                                    <p><span class="font-medium">Email:</span> {{ Auth::user()->email }}</p>
                                    @if(Auth::user()->customer_number)
                                        <p><span class="font-medium">Customer #:</span> {{ Auth::user()->customer_number }}</p>
                                    @endif
                                    <p><span class="font-medium">State:</span> {{ Auth::user()->state ?? 'Not specified' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        @if($selectedOrder->notes)
                            <div class="mt-4 bg-blue-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-blue-800 mb-2">Order Notes</h4>
                                <p class="text-blue-700">{{ $selectedOrder->notes }}</p>
                            </div>
                        @endif
                        
                        <!-- Order Items (Responsive) -->
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Order Items</h4>
                            
                            <!-- Desktop Table (hidden on mobile) -->
                            <div class="hidden md:block overflow-x-auto border border-gray-200 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($selectedOrder->items as $item)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $item->product_name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $item->product_sku }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    ${{ number_format($item->price, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $item->quantity }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    ${{ number_format($item->price * $item->quantity, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Order Total:</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">${{ number_format($selectedOrder->total, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <!-- Mobile Card View (shown only on small screens) -->
                            <div class="md:hidden space-y-3">
                                @foreach($selectedOrder->items as $item)
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-900">{{ $item->product_name }}</span>
                                            <span class="font-bold text-gray-900">${{ number_format($item->price * $item->quantity, 2) }}</span>
                                        </div>
                                        <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                                            <div>
                                                <span class="text-gray-500">SKU:</span>
                                                <span class="text-gray-700">{{ $item->product_sku }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Price:</span>
                                                <span class="text-gray-700">${{ number_format($item->price, 2) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Quantity:</span>
                                                <span class="text-gray-700">{{ $item->quantity }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                
                                <!-- Order Total -->
                                <div class="bg-white border border-gray-200 rounded-lg p-4 flex justify-between items-center">
                                    <span class="font-medium text-gray-900">Order Total:</span>
                                    <span class="font-bold text-lg text-gray-900">${{ number_format($selectedOrder->total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            wire:click="closeOrderDetails" 
                            type="button" 
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Close
                        </button>
                        <a 
                            href="{{ route('customer.orders') }}" 
                            class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-800 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm"
                        >
                            View All Orders
                        </a>
                        <a 
                            href="{{ route('inventory.catalog') }}" 
                            class="mt-3 sm:mt-0 mr-3 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm"
                        >
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>