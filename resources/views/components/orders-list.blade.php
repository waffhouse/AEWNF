<div>

    @if(empty($orders) || (is_countable($orders) && count($orders) === 0))
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No orders found</h3>
            @if($isAdmin)
                <p class="mt-2 text-base text-gray-500 max-w-md mx-auto">
                    @if(!empty($search ?? ''))
                        Try adjusting your search criteria.
                    @else
                        No orders have been placed yet.
                    @endif
                </p>
                @if(!empty($search ?? ''))
                    <div class="mt-6">
                        <button
                            onclick="Livewire.dispatch('clearSearch')"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Clear Search
                        </button>
                    </div>
                @endif
            @else
                <p class="mt-2 text-base text-gray-500 max-w-md mx-auto">You haven't placed any orders yet. Browse our products and add items to your cart.</p>
                <div class="mt-6">
                    <a href="{{ route('inventory.catalog') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Browse Products
                    </a>
                </div>
            @endif
        </div>
    @else
        <!-- Responsive Layout: Cards for Mobile/Small Screens, Table for Larger Screens -->
        <div>
            <!-- Mobile Cards View (SM and below) -->
            <div class="md:hidden space-y-4">
                @foreach($orders as $order)
                    @php $orderId = is_array($order) ? $order['id'] : $order->id; @endphp
                    <div wire:key="order-item-mobile-{{ $orderId }}" class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-900">Order #{{ $orderId }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">${{ number_format(is_array($order) ? $order['total'] : $order->total, 2) }}</span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div>
                                <p class="text-xs text-gray-500">Date</p>
                                <p class="text-sm">@formatdate(is_array($order) ? $order['created_at'] : $order->created_at)</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Status</p>
                                <div class="mt-1">
                                    @if((is_array($order) ? $order['status'] : $order->status) === \App\Models\Order::STATUS_PENDING)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            Pending
                                        </span>
                                    @elseif((is_array($order) ? $order['status'] : $order->status) === \App\Models\Order::STATUS_COMPLETED)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Completed
                                        </span>
                                    @elseif((is_array($order) ? $order['status'] : $order->status) === \App\Models\Order::STATUS_CANCELLED)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            Cancelled
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        @if($isAdmin)
                        <div class="mb-3">
                            <p class="text-xs text-gray-500">Customer</p>
                            <p class="text-sm font-medium">
                                {{ is_array($order) 
                                    ? (isset($order['user']) ? (is_array($order['user']) ? ($order['user']['name'] ?? 'Unknown') : $order['user']->name) : 'Unknown') 
                                    : (isset($order->user) ? $order->user->name : 'Unknown') }}
                            </p>
                            @if(is_array($order) 
                                ? (isset($order['user']) && (is_array($order['user']) 
                                    ? isset($order['user']['customer_number']) && $order['user']['customer_number'] 
                                    : isset($order['user']->customer_number) && $order['user']->customer_number)) 
                                : (isset($order->user) && isset($order->user->customer_number) && $order->user->customer_number))
                                <p class="text-xs text-gray-500">
                                    Customer #: {{ is_array($order) 
                                        ? (isset($order['user']) 
                                            ? (is_array($order['user']) 
                                                ? ($order['user']['customer_number'] ?? 'N/A') 
                                                : ($order['user']->customer_number ?? 'N/A')) 
                                            : 'N/A') 
                                        : ($order->user->customer_number ?? 'N/A') }}
                                </p>
                            @endif
                        </div>
                        @endif
                        
                        <div class="flex flex-wrap gap-2">
                            <!-- View Details Button -->
                            <button 
                                onclick="Livewire.dispatch('showOrderDetail', [{{ $orderId }}])" 
                                class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Details
                            </button>
                            
                            <!-- Pick Ticket Button (Admin Only) -->
                            @if($isAdmin)
                                @can('manage orders')
                                    <a 
                                        href="{{ route('orders.pick-ticket', $orderId) }}" 
                                        target="_blank"
                                        class="inline-flex items-center px-3 py-1.5 border border-indigo-300 text-xs font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                        Ticket
                                    </a>
                                @endcan
                            @endif
                            
                            <!-- Admin Action Buttons -->
                            @if($isAdmin && (is_array($order) ? $order['status'] : $order->status) === \App\Models\Order::STATUS_PENDING)
                                @can('manage orders')
                                    <button 
                                        onclick="if(confirm('Are you sure you want to mark this order as completed?')) { 
                                            Livewire.dispatch('updateStatus', { 
                                                orderId: {{ $orderId }}, 
                                                status: '{{ \App\Models\Order::STATUS_COMPLETED }}' 
                                            });
                                        }" 
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Complete
                                    </button>
                                    
                                    <button 
                                        onclick="if(confirm('Are you sure you want to cancel this order?')) { 
                                            Livewire.dispatch('updateStatus', { 
                                                orderId: {{ $orderId }}, 
                                                status: '{{ \App\Models\Order::STATUS_CANCELLED }}' 
                                            });
                                        }" 
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Cancel
                                    </button>
                                @endcan
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Desktop Table View (MD and up) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order #
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            @if($isAdmin)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer
                            </th>
                            @endif
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders as $order)
                            @php $orderId = is_array($order) ? $order['id'] : $order->id; @endphp
                            <tr wire:key="order-item-{{ $orderId }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900">{{ $orderId }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-500">@formatdate(is_array($order) ? $order['created_at'] : $order->created_at)</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if((is_array($order) ? $order['status'] : $order->status) === \App\Models\Order::STATUS_PENDING)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            Pending
                                        </span>
                                    @elseif((is_array($order) ? $order['status'] : $order->status) === \App\Models\Order::STATUS_COMPLETED)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Completed
                                        </span>
                                    @elseif((is_array($order) ? $order['status'] : $order->status) === \App\Models\Order::STATUS_CANCELLED)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            Cancelled
                                        </span>
                                    @endif
                                </td>
                                @if($isAdmin)
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ is_array($order) 
                                            ? (isset($order['user']) ? (is_array($order['user']) ? ($order['user']['name'] ?? 'Unknown') : $order['user']->name) : 'Unknown') 
                                            : (isset($order->user) ? $order->user->name : 'Unknown') }}
                                    </div>
                                    @if(is_array($order) 
                                        ? (isset($order['user']) && (is_array($order['user']) 
                                            ? isset($order['user']['customer_number']) && $order['user']['customer_number'] 
                                            : isset($order['user']->customer_number) && $order['user']->customer_number)) 
                                        : (isset($order->user) && isset($order->user->customer_number) && $order->user->customer_number))
                                        <div class="text-xs text-gray-500">
                                            Customer #: {{ is_array($order) 
                                                ? (isset($order['user']) 
                                                    ? (is_array($order['user']) 
                                                        ? ($order['user']['customer_number'] ?? 'N/A') 
                                                        : ($order['user']->customer_number ?? 'N/A')) 
                                                    : 'N/A') 
                                                : ($order->user->customer_number ?? 'N/A') }}
                                        </div>
                                    @endif
                                </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">${{ number_format(is_array($order) ? $order['total'] : $order->total, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <!-- View Details Button -->
                                        <button 
                                            onclick="Livewire.dispatch('showOrderDetail', [{{ $orderId }}])" 
                                            class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Details
                                        </button>
                                        
                                        <!-- Pick Ticket Button (Admin Only) -->
                                        @if($isAdmin)
                                            @can('manage orders')
                                                <a 
                                                    href="{{ route('orders.pick-ticket', $orderId) }}" 
                                                    target="_blank"
                                                    class="inline-flex items-center px-3 py-1.5 border border-indigo-300 text-xs font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                    </svg>
                                                    Ticket
                                                </a>
                                            @endcan
                                        @endif
                                        
                                        <!-- Admin Action Buttons -->
                                        @if($isAdmin && (is_array($order) ? $order['status'] : $order->status) === \App\Models\Order::STATUS_PENDING)
                                            @can('manage orders')
                                                <button 
                                                    onclick="if(confirm('Are you sure you want to mark this order as completed?')) { 
                                                        Livewire.dispatch('updateStatus', { 
                                                            orderId: {{ $orderId }}, 
                                                            status: '{{ \App\Models\Order::STATUS_COMPLETED }}' 
                                                        });
                                                    }" 
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Complete
                                                </button>
                                                
                                                <button 
                                                    onclick="if(confirm('Are you sure you want to cancel this order?')) { 
                                                        Livewire.dispatch('updateStatus', { 
                                                            orderId: {{ $orderId }}, 
                                                            status: '{{ \App\Models\Order::STATUS_CANCELLED }}' 
                                                        });
                                                    }" 
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Cancel
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($hasMorePages)
        <!-- Infinite Scroll Controls -->
        <div 
            class="mt-8 text-center" 
            x-data="{ 
                observer: null,
                init() {
                    // Initialize the observer
                    this.setupObserver();
                    
                    // Ensure it's reconnected after filter changes
                    Livewire.on('resetOrders', () => {
                        // Small delay to ensure DOM is updated
                        setTimeout(() => this.setupObserver(), 50);
                    });
                },
                setupObserver() {
                    // Clean up any existing observer
                    if (this.observer) {
                        this.observer.disconnect();
                    }
                    
                    this.observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                Livewire.dispatch('loadMore');
                            }
                        });
                    }, { rootMargin: '200px' });
                    
                    this.observer.observe(this.$el);
                },
                // Ensure proper cleanup
                destroy() {
                    if (this.observer) {
                        this.observer.disconnect();
                    }
                }
            }"
        >
            <!-- Loading Indicator -->
            <div wire:loading wire:target="loadMore" class="py-4">
                <svg class="animate-spin h-6 w-6 text-blue-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-sm text-gray-600">Loading more orders...</span>
            </div>

            <!-- End of Results Message -->
            <div x-show="!@js($hasMorePages)" class="py-4 text-sm text-gray-600">
                @if($totalCount === 0)
                    No orders found
                @elseif($loadedCount === 1)
                    Showing 1 order
                @else
                    Showing all {{ $loadedCount }} orders
                @endif
            </div>
            
            <!-- Manual Load More Button (as fallback) -->
            <div x-show="@js($hasMorePages) && !@js($isLoading)" class="py-4">
                <button 
                    onclick="Livewire.dispatch('loadMore')"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                >
                    Load More Orders
                </button>
            </div>
        </div>
    @endif
</div>