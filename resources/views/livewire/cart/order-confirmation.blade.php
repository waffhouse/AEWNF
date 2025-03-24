<div>
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
                                    <p><span class="font-medium">Delivery:</span> 
                                        @if($selectedOrder->delivery_type === \App\Models\Order::DELIVERY_TYPE_PICKUP)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Pickup
                                            </span>
                                        @elseif($selectedOrder->delivery_type === \App\Models\Order::DELIVERY_TYPE_DELIVERY)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                Delivery
                                            </span>
                                        @endif
                                    </p>
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
                                <table class="min-w-full divide-y divide-gray-200 table-fixed">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-1/2">Product</th>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-1/6">SKU</th>
                                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-1/12">Price</th>
                                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-1/12">Qty</th>
                                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-1/6">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($selectedOrder->items as $item)
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-gray-900">
                                                    <div class="truncate pr-4" title="{{ $item->product_name }}">{{ $item->product_name }}</div>
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-500 whitespace-nowrap overflow-hidden text-ellipsis">
                                                    {{ $item->product_sku }}
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-500 text-right whitespace-nowrap">
                                                    ${{ number_format($item->price, 2) }}
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-500 text-right whitespace-nowrap">
                                                    {{ $item->quantity }}
                                                </td>
                                                <td class="px-4 py-2 text-sm font-medium text-gray-900 text-right whitespace-nowrap">
                                                    ${{ number_format($item->price * $item->quantity, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="4" class="px-4 py-2 text-right text-sm font-medium text-gray-900">Order Total:</td>
                                            <td class="px-4 py-2 text-right text-sm font-bold text-gray-900">${{ number_format($selectedOrder->total, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <!-- Mobile Card View (shown only on small screens) -->
                            <div class="md:hidden space-y-3">
                                @foreach($selectedOrder->items as $item)
                                    <div class="p-3 border rounded">
                                        <div class="mb-1">
                                            <span class="text-sm font-medium block mb-1">{{ $item->product_name }}</span>
                                            <div class="flex justify-between border-t border-gray-100 pt-1">
                                                <div class="text-xs text-gray-500">
                                                    <div>SKU: <span class="font-medium">{{ $item->product_sku }}</span></div>
                                                    <div>Price: <span class="font-medium">${{ number_format($item->price, 2) }}</span></div>
                                                    <div>Qty: <span class="font-medium">{{ $item->quantity }}</span></div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-xs text-gray-500">Subtotal</div>
                                                    <div class="text-sm font-bold text-gray-900">${{ number_format($item->price * $item->quantity, 2) }}</div>
                                                </div>
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