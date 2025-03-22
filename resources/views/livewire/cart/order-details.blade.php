<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session()->has('message'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold">Order #{{ $order->id }}</h2>
                    <div>
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
                </div>
                
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Order Details</h3>
                            <p class="font-medium">Date: <span class="font-normal">@formatdate($order->created_at)</span></p>
                            <p class="font-medium">Total: <span class="font-normal">${{ number_format($order->total, 2) }}</span></p>
                            <p class="font-medium">Items: <span class="font-normal">{{ $order->getTotalItems() }}</span></p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Customer Information</h3>
                            <p class="font-medium">Name: <span class="font-normal">{{ Auth::user()->name }}</span></p>
                            <p class="font-medium">Email: <span class="font-normal">{{ Auth::user()->email }}</span></p>
                            @if(Auth::user()->customer_number)
                            <p class="font-medium">Customer #: <span class="font-normal">{{ Auth::user()->customer_number }}</span></p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Desktop Table View - Hidden on Small Screens -->
                <div class="hidden sm:block overflow-x-auto rounded-lg border border-gray-200 mb-6">
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
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->product_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $item->product_sku }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($item->price, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Order Total:</td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">${{ number_format($order->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Mobile Card View - Visible only on Small Screens -->
                <div class="sm:hidden space-y-4 mb-6">
                    @foreach($order->items as $item)
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                            <div class="p-4">
                                <!-- Product Info -->
                                <div class="mb-3">
                                    <h3 class="text-sm font-medium text-gray-900 line-clamp-2">
                                        {{ $item->product_name }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        SKU: {{ $item->product_sku }}
                                    </p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-500">Price:</span> 
                                        <span class="font-medium">${{ number_format($item->price, 2) }}</span>
                                    </div>
                                    
                                    <div>
                                        <span class="text-gray-500">Quantity:</span> 
                                        <span class="font-medium">{{ $item->quantity }}</span>
                                    </div>
                                    
                                    <div class="col-span-2 pt-2 border-t border-gray-100 mt-2">
                                        <span class="text-gray-500">Subtotal:</span> 
                                        <span class="font-medium text-red-600">${{ number_format($item->price * $item->quantity, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- Order Total for Mobile -->
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                        <div class="flex justify-between items-center font-medium">
                            <span>Order Total:</span>
                            <span class="text-lg text-red-600">${{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
                
                @if($order->notes)
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 mb-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Order Notes</h3>
                    <p class="text-gray-700">{{ $order->notes }}</p>
                </div>
                @endif
                
                <div class="flex flex-col sm:flex-row justify-between space-y-3 sm:space-y-0">
                    <a href="{{ route('customer.orders') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Orders
                    </a>
                    <a href="{{ route('inventory.catalog') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>