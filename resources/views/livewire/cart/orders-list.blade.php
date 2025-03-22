<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Order History</h3>
                
                @if($orders->isEmpty())
                    <div class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No orders found</h3>
                        <p class="mt-1 text-sm text-gray-500">You haven't placed any orders yet.</p>
                        <div class="mt-6">
                            <a href="{{ route('inventory.catalog') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Browse Products
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Desktop Table View - Hidden on Small Screens -->
                    <div class="hidden sm:block overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($orders as $order)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        #{{ $order->id }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        @formatdate($order->created_at)
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        ${{ number_format($order->total, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        @if($order->status === \App\Models\Order::STATUS_PENDING)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @elseif($order->status === \App\Models\Order::STATUS_COMPLETED)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        @elseif($order->status === \App\Models\Order::STATUS_CANCELLED)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Cancelled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $order->getTotalItems() }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <button 
                                            wire:click="viewOrderDetails({{ $order->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Mobile Card View - Visible only on Small Screens -->
                    <div class="sm:hidden space-y-4">
                        @foreach($orders as $order)
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-900">
                                                Order #{{ $order->id }}
                                            </h3>
                                            <p class="text-xs text-gray-500 mt-1">
                                                @formatdate($order->created_at)
                                            </p>
                                        </div>
                                        @if($order->status === \App\Models\Order::STATUS_PENDING)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @elseif($order->status === \App\Models\Order::STATUS_COMPLETED)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        @elseif($order->status === \App\Models\Order::STATUS_CANCELLED)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Cancelled
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                                        <div>
                                            <span class="text-gray-500">Total:</span> 
                                            <span class="font-medium">${{ number_format($order->total, 2) }}</span>
                                        </div>
                                        
                                        <div>
                                            <span class="text-gray-500">Items:</span> 
                                            <span class="font-medium">{{ $order->getTotalItems() }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-end">
                                        <button 
                                            wire:click="viewOrderDetails({{ $order->id }})" 
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
                                @if($selectedOrder->status === \App\Models\Order::STATUS_PENDING)
                                    <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @elseif($selectedOrder->status === \App\Models\Order::STATUS_COMPLETED)
                                    <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Completed
                                    </span>
                                @elseif($selectedOrder->status === \App\Models\Order::STATUS_CANCELLED)
                                    <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Cancelled
                                    </span>
                                @endif
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
                            href="{{ route('inventory.catalog') }}" 
                            class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm"
                        >
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>