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
            <!-- Header -->
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">
                        Order #{{ $order->id }} Details
                    </h3>
                    <button 
                        wire:click="closeOrderDetails" 
                        class="text-gray-400 hover:text-gray-500"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="bg-white p-6">
                <!-- Order Summary -->
                <div class="mb-4 pb-4 border-b">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <div class="flex items-center">
                            <p class="text-sm text-gray-600 mr-2">Date: <span class="font-medium">@formatdate($order->created_at)</span></p>
                        </div>
                        <div class="flex items-center">
                            <p class="text-sm text-gray-600 mr-2">Status:</p>
                            @if($order->status === \App\Models\Order::STATUS_PENDING)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @elseif($order->status === \App\Models\Order::STATUS_COMPLETED)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Completed
                                </span>
                            @elseif($order->status === \App\Models\Order::STATUS_CANCELLED)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Cancelled
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Customer: <span class="font-medium">{{ $order->user->name }}</span></p>
                            @if($order->user->customer_number)
                                <p class="text-sm text-gray-600">Customer #: <span class="font-medium font-mono">{{ $order->user->customer_number }}</span></p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Items: <span class="font-medium">{{ $order->getTotalItems() }}</span></p>
                            <p class="font-bold text-gray-900">Total: ${{ number_format($order->total, 2) }}</p>
                        </div>
                    </div>
                </div>
                
                @if($order->notes)
                    <div class="mb-4 p-3 bg-gray-50 border-l-4 border-yellow-400 rounded">
                        <h4 class="text-sm font-medium text-gray-700 mb-1">Order Notes</h4>
                        <p class="text-sm text-gray-600">{{ $order->notes }}</p>
                    </div>
                @endif
                
                <!-- Order Items -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Order Items</h4>
                    
                    <!-- Desktop Table -->
                    <div class="hidden md:block border rounded overflow-hidden">
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
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            <div class="truncate pr-4" title="{{ $item->product_name }}">{{ $item->product_name }}</div>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-500 whitespace-nowrap overflow-hidden text-ellipsis">{{ $item->product_sku }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500 text-right whitespace-nowrap">${{ number_format($item->price, 2) }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500 text-right whitespace-nowrap">{{ $item->quantity }}</td>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900 text-right whitespace-nowrap">${{ number_format($item->price * $item->quantity, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right text-sm font-medium text-gray-900">Order Total:</td>
                                    <td class="px-4 py-2 text-right text-sm font-bold text-gray-900">${{ number_format($order->total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Mobile List -->
                    <div class="md:hidden space-y-2">
                        @foreach($order->items as $item)
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
                        <div class="p-3 border rounded bg-gray-50">
                            <div class="flex justify-between">
                                <span class="font-medium">Order Total:</span>
                                <span class="font-bold">${{ number_format($order->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Note for admins about order management -->
                @can('manage orders')
                    <div class="mt-4 pt-4 border-t text-sm text-gray-500 italic">
                        <p>Status changes can be made through the Order Management dashboard.</p>
                    </div>
                @endcan
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <!-- Generate Pick Ticket Button - Only visible to staff with manage orders permission -->
                @can('manage orders')
                    <a 
                        href="{{ route('orders.pick-ticket', $order->id) }}" 
                        target="_blank"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Generate Pick Ticket
                    </a>
                @endcan
                
                <!-- Continue Shopping Button - Only visible to customers -->
                @if(!auth()->user()->can('manage orders'))
                    <a 
                        href="{{ route('inventory.catalog') }}" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-800 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Continue Shopping
                    </a>
                @endif
                
                <button 
                    wire:click="closeOrderDetails" 
                    type="button" 
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</div>