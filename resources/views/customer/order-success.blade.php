<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order Confirmation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-center mb-6">
                        <div class="bg-green-100 rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    
                    <h1 class="text-2xl font-bold text-center mb-2">Thank You for Your Order!</h1>
                    <p class="text-lg text-center mb-6">Your order has been successfully placed.</p>
                    
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Order Details</h3>
                                <p class="font-medium">Order #: <span class="font-normal">{{ $order->id }}</span></p>
                                <p class="font-medium">Date: <span class="font-normal">@formatdate($order->created_at)</span></p>
                                <p class="font-medium">Status: <span class="font-normal">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </span></p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Customer Information</h3>
                                <p class="font-medium">Name: <span class="font-normal">{{ auth()->user()->name }}</span></p>
                                <p class="font-medium">Email: <span class="font-normal">{{ auth()->user()->email }}</span></p>
                                @if(auth()->user()->customer_number)
                                <p class="font-medium">Customer #: <span class="font-normal">{{ auth()->user()->customer_number }}</span></p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto rounded-lg border border-gray-200 mb-6">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->product_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->product_sku }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($item->price, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($item->price * $item->quantity, 2) }}</td>
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
                    
                    @if($order->notes)
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 mb-6">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Order Notes</h3>
                        <p class="text-gray-700">{{ $order->notes }}</p>
                    </div>
                    @endif
                    
                    <div class="bg-blue-50 p-6 rounded-lg border border-blue-200 mb-6">
                        <h3 class="font-semibold text-blue-800 mb-2">What's Next?</h3>
                        <p class="text-blue-700 mb-2">Your order has been received and is currently being processed. A member of our team will contact you shortly to confirm availability and arrange delivery or pickup.</p>
                        <p class="text-blue-700">If you have any questions about your order, please contact our customer service team.</p>
                    </div>
                    
                    <div class="flex justify-between">
                        <a href="{{ route('inventory.catalog') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Continue Shopping
                        </a>
                        <a href="{{ route('customer.orders') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            View Your Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>