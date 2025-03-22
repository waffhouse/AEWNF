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
            <!-- Header with gradient background -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-4 sm:px-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                            Order #{{ $order->id }} Details
                        </h3>
                        
                        @if($order->status === \App\Models\Order::STATUS_PENDING)
                            <span class="ml-3 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200 shadow-sm">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                Pending
                            </span>
                        @elseif($order->status === \App\Models\Order::STATUS_COMPLETED)
                            <span class="ml-3 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200 shadow-sm">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Completed
                            </span>
                        @elseif($order->status === \App\Models\Order::STATUS_CANCELLED)
                            <span class="ml-3 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200 shadow-sm">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                Cancelled
                            </span>
                        @endif
                    </div>
                    <button 
                        wire:click="closeOrderDetails" 
                        class="text-white hover:text-gray-200 transition-colors duration-150"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <p class="mt-1 text-sm text-blue-100">Placed on @formatdate($order->created_at)</p>
            </div>
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Order Information -->
                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Order Information
                        </h4>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="text-gray-600">Order ID:</span>
                                <span class="font-medium text-gray-900">#{{ $order->id }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="text-gray-600">Date:</span>
                                <span class="font-medium text-gray-900">@formatdate($order->created_at)</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="text-gray-600">Status:</span>
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
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="text-gray-600">Total Items:</span>
                                <span class="font-medium text-gray-900">{{ $order->getTotalItems() }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-gray-600">Total Amount:</span>
                                <span class="font-bold text-lg text-blue-600">${{ number_format($order->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Customer Information
                        </h4>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="text-gray-600">Name:</span>
                                <span class="font-medium text-gray-900">{{ $order->user->name }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-medium text-gray-900">{{ $order->user->email }}</span>
                            </div>
                            @if($order->user->customer_number)
                                <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                    <span class="text-gray-600">Customer #:</span>
                                    <span class="font-medium text-gray-900 font-mono">{{ $order->user->customer_number }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">State:</span>
                                <span class="font-medium text-gray-900">{{ $order->user->state ?? 'Not specified' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($order->notes)
                    <div class="mt-4 bg-blue-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-blue-800 mb-2">Order Notes</h4>
                        <p class="text-blue-700">{{ $order->notes }}</p>
                    </div>
                @endif
                
                <!-- Order Items (Responsive) -->
                <div class="mt-4">
                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Order Items
                    </h4>
                    
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
                                @foreach($order->items as $item)
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">${{ number_format($order->total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Mobile Card View (shown only on small screens) -->
                    <div class="md:hidden space-y-3">
                        @foreach($order->items as $item)
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
                            <span class="font-bold text-lg text-gray-900">${{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Status Update Buttons - Only visible to users with manage orders permission -->
                @can('manage orders')
                    @if($order->status === \App\Models\Order::STATUS_PENDING)
                        <div class="mt-6 flex flex-wrap gap-3">
                            <button 
                                wire:click="updateStatus('{{ \App\Models\Order::STATUS_COMPLETED }}')" 
                                wire:confirm="Are you sure you want to mark this order as completed?"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            >
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Mark as Completed
                            </button>
                            
                            <button 
                                wire:click="updateStatus('{{ \App\Models\Order::STATUS_CANCELLED }}')" 
                                wire:confirm="Are you sure you want to cancel this order?"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            >
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Cancel Order
                            </button>
                        </div>
                    @endif
                @endcan
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button 
                    wire:click="closeOrderDetails" 
                    type="button" 
                    class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                >
                    Close
                </button>
                
                <!-- Continue Shopping Button - Only visible to customers -->
                @if(!auth()->user()->can('manage orders'))
                    <a 
                        href="{{ route('inventory.catalog') }}" 
                        class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm"
                    >
                        Continue Shopping
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>