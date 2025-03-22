<div>
    <!-- Scroll to top button -->
    <x-scroll-to-top />
    
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Order Management</h3>
        <p class="text-sm text-gray-600">View and manage customer orders. Update status, view details, and track history.</p>
    </div>

    <!-- Search and Orders Summary -->
    <div class="mb-6">
        <!-- Search Bar -->
        <div class="mb-4">
            <label for="search" class="sr-only">Search</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="search" 
                    id="search" 
                    wire:model.live.debounce.300ms="search" 
                    class="block w-full p-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Search by order ID, customer name, email..."
                >
            </div>
        </div>
        
        <!-- Order Stats Summary -->
        <div class="flex flex-wrap gap-3 mb-4">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm text-yellow-800 font-medium">{{ $pendingCount }} Pending</span>
            </div>
            
            <div class="bg-green-50 border border-green-200 rounded-lg px-3 py-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-sm text-green-800 font-medium">{{ $completedCount }} Completed</span>
            </div>
            
            <div class="bg-red-50 border border-red-200 rounded-lg px-3 py-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span class="text-sm text-red-800 font-medium">{{ $cancelledCount }} Cancelled</span>
            </div>
            
            <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="text-sm text-gray-800 font-medium">{{ $pendingCount + $completedCount + $cancelledCount }} Total Orders</span>
            </div>
        </div>
    </div>

    <!-- Orders Display (Card Layout) -->
    <div class="mb-6">
        <!-- Initial Loading Indicator -->
        <div wire:loading wire:target="loadOrders, resetOrders" class="w-full">
            <div class="flex justify-center py-12">
                <svg class="animate-spin h-10 w-10 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @forelse($orders as $order)
                <div wire:key="order-{{ $order->id }}" class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                    <!-- Order Header - ID and Status -->
                    <div class="flex flex-wrap justify-between items-center mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900">Order #{{ $order->id }}</span>
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
                        
                        <!-- Action Buttons -->
                        <div class="flex space-x-2 mt-2 sm:mt-0">
                            <!-- View Details Button -->
                            <button 
                                wire:click="viewOrderDetails({{ $order->id }})" 
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Details
                            </button>
                            
                            @can('manage orders')
                                @if($order->status === \App\Models\Order::STATUS_PENDING)
                                    <!-- Complete Order Button -->
                                    <button 
                                        wire:click="updateStatus({{ $order->id }}, '{{ \App\Models\Order::STATUS_COMPLETED }}')"
                                        wire:confirm="Are you sure you want to mark this order as completed?"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Complete
                                    </button>
                                    
                                    <!-- Cancel Order Button -->
                                    <button 
                                        wire:click="updateStatus({{ $order->id }}, '{{ \App\Models\Order::STATUS_CANCELLED }}')"
                                        wire:confirm="Are you sure you want to cancel this order?"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Cancel
                                    </button>
                                @endif
                            @endcan
                        </div>
                    </div>
                    
                    <!-- Order Details Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Customer</dt>
                            <dd class="font-medium mt-1">{{ $order->user->name }}</dd>
                            <dd class="text-xs text-gray-500">{{ $order->user->email }}</dd>
                            @if($order->user->customer_number)
                                <dd class="text-xs text-gray-500">Customer #: {{ $order->user->customer_number }}</dd>
                            @endif
                        </div>
                        <div>
                            <dt class="text-gray-500">Date</dt>
                            <dd class="mt-1">@formatdateonly($order->created_at)</dd>
                            <dd class="text-xs text-gray-500">@formattime($order->created_at)</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Total</dt>
                            <dd class="font-medium text-gray-900 mt-1">${{ number_format($order->total, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Items</dt>
                            <dd class="mt-1">{{ $order->items()->count() }}</dd>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-lg p-8 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        No orders found
                    </h3>
                    @if(!empty($search))
                        <p class="mt-1 text-sm text-gray-500">Try adjusting your search criteria.</p>
                        <div class="mt-6">
                            <button
                                wire:click="$set('search', '')"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Clear Search
                            </button>
                        </div>
                    @else
                        <p class="mt-1 text-sm text-gray-500">No orders have been placed yet.</p>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    <!-- Infinite Scroll Controls -->
    <div 
        class="mt-8 text-center" 
        x-data="{ 
            observer: null,
            init() {
                // Initialize the observer
                this.setupObserver();
                
                // Ensure it's reconnected after filter changes
                $wire.on('resetOrders', () => {
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
                            @this.loadMore();
                        }
                    });
                }, { rootMargin: '100px' });
                
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
            <svg class="animate-spin h-6 w-6 text-yellow-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Order #{{ $selectedOrder->id }} Details
                            </h3>
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
                                    <p><span class="font-medium">Total:</span> ${{ number_format($selectedOrder->total, 2) }}</p>
                                </div>
                            </div>
                            
                            <!-- Customer Information -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Customer Information</h4>
                                <div class="space-y-2">
                                    <p><span class="font-medium">Name:</span> {{ $selectedOrder->user->name }}</p>
                                    <p><span class="font-medium">Email:</span> {{ $selectedOrder->user->email }}</p>
                                    @if($selectedOrder->user->customer_number)
                                        <p><span class="font-medium">Customer #:</span> {{ $selectedOrder->user->customer_number }}</p>
                                    @endif
                                    <p><span class="font-medium">State:</span> {{ $selectedOrder->user->state ?? 'Not specified' }}</p>
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
                        
                        <!-- Status Update Buttons -->
                        @can('manage orders')
                            @if($selectedOrder->status === \App\Models\Order::STATUS_PENDING)
                                <div class="mt-6 flex flex-wrap gap-3">
                                    <button 
                                        wire:click="updateStatus({{ $selectedOrder->id }}, '{{ \App\Models\Order::STATUS_COMPLETED }}')"
                                        wire:confirm="Are you sure you want to mark this order as completed?"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                    >
                                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Mark as Completed
                                    </button>
                                    
                                    <button 
                                        wire:click="updateStatus({{ $selectedOrder->id }}, '{{ \App\Models\Order::STATUS_CANCELLED }}')"
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
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>