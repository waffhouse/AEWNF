<div>
    <!-- Alpine.js Component Definition -->
    <script>
        // Ensure the accordion component is available immediately
        if (window.Alpine) {
            window.Alpine.data('ordersAccordion', () => ({
                expandedId: null,
                initialized: false,
                
                init() {
                    console.log('Initializing orders accordion');
                    
                    // Mark as initialized
                    this.initialized = true;
                    
                    // Force a reset to ensure everything is properly set up
                    this.$nextTick(() => {
                        // Ensure this component is fully initialized before watching
                        this.$watch('expandedId', value => {
                            console.log('Expanded ID changed to:', value);
                        });
                    });
                    
                    // Listen for order status updates
                    if (window.Livewire) {
                        window.Livewire.on('order-status-updated', () => {
                            console.log('Order status updated, clearing expandedId');
                            this.expandedId = null;
                        });
                        
                        window.Livewire.on('ordersUpdated', () => {
                            console.log('Orders updated, clearing expandedId');
                            this.expandedId = null;
                        });
                    }
                    
                    // Listen for navigation-related reinitialization
                    window.addEventListener('alpine-reinit', () => {
                        console.log('Alpine reinit detected, ensuring order accordion is initialized');
                        // Force Alpine to reevaluate this component
                        if (!this.initialized) {
                            this.initialized = true;
                        }
                        this.$nextTick(() => {
                            console.log('Component re-evaluated after navigation');
                        });
                    });
                    
                    // Also handle direct page loads
                    window.addEventListener('DOMContentLoaded', () => {
                        console.log('DOMContentLoaded, ensuring order accordion is ready');
                        this.$nextTick(() => {
                            console.log('Component checked after DOMContentLoaded');
                        });
                    });
                },
                
                toggle(id) {
                    console.log('Toggle called for ID:', id, 'Current expandedId:', this.expandedId);
                    this.expandedId = (this.expandedId === id) ? null : id;
                },
                
                isExpanded(id) {
                    return this.expandedId === id;
                }
            }));
        }
        
        // Also register on alpine:init for consistency
        document.addEventListener('alpine:init', () => {
            Alpine.data('ordersAccordion', () => ({
                expandedId: null,
                initialized: false,
                
                init() {
                    console.log('Initializing orders accordion (via alpine:init)');
                    
                    // Mark as initialized
                    this.initialized = true;
                    
                    // Force a reset to ensure everything is properly set up
                    this.$nextTick(() => {
                        // Ensure this component is fully initialized before watching
                        this.$watch('expandedId', value => {
                            console.log('Expanded ID changed to:', value);
                        });
                    });
                    
                    // Listen for order status updates
                    Livewire.on('order-status-updated', () => {
                        console.log('Order status updated, clearing expandedId');
                        this.expandedId = null;
                    });
                    
                    Livewire.on('ordersUpdated', () => {
                        console.log('Orders updated, clearing expandedId');
                        this.expandedId = null;
                    });
                    
                    // Listen for navigation-related reinitialization
                    window.addEventListener('alpine-reinit', () => {
                        console.log('Alpine reinit detected, ensuring order accordion is initialized');
                        // Force Alpine to reevaluate this component
                        if (!this.initialized) {
                            this.initialized = true;
                        }
                        this.$nextTick(() => {
                            console.log('Component re-evaluated after navigation');
                        });
                    });
                },
                
                toggle(id) {
                    console.log('Toggle called for ID:', id, 'Current expandedId:', this.expandedId);
                    this.expandedId = (this.expandedId === id) ? null : id;
                },
                
                isExpanded(id) {
                    return this.expandedId === id;
                }
            }));
        });
    </script>

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
        <!-- Accordion-style Orders List -->
        <div 
            wire:key="orders-list-{{ count($orders) }}-{{ time() }}"
            x-data="ordersAccordion()"
            x-init="
                $nextTick(() => { 
                    console.log('Orders accordion fully initialized');
                    
                    // Dispatch an event to alert any parent components
                    window.dispatchEvent(new CustomEvent('orders-accordion-initialized'));
                    
                    // Force layout recalculation to ensure Alpine.js bindings are active
                    setTimeout(() => {
                        console.log('Delayed initialization check running');
                        let triggerLayout = document.body.offsetHeight;
                    }, 100);
                });
            "
            class="space-y-4"
        >
            @foreach($orders as $order)
                @php $orderId = is_array($order) ? $order['id'] : $order->id; @endphp
                <div 
                    class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200"
                >
                    <!-- Order Header (Always Visible) -->
                    <div 
                        @click="toggle({{ $orderId }})" 
                        class="px-4 py-4 flex flex-wrap justify-between items-center cursor-pointer hover:bg-gray-50 transition-colors duration-150"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900">Order #{{ is_array($order) ? $order['id'] : $order->id }}</span>
                                </div>
                                <span class="text-xs text-gray-500 mt-1">@formatdate(is_array($order) ? $order['created_at'] : $order->created_at)</span>
                            </div>
                            
                            <div>
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
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center">
                                <span class="mr-2 font-medium text-gray-900">${{ number_format(is_array($order) ? $order['total'] : $order->total, 2) }}</span>
                                <span 
                                    x-bind:class="isExpanded({{ $orderId }}) ? 'rotate-180' : ''" 
                                    class="transform transition-transform duration-200 text-gray-500"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Expandable Content (Order Details & Actions) -->
                    <div x-show="isExpanded({{ $orderId }})" x-collapse class="border-t border-gray-100">
                        <div class="p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                                <!-- Order Info -->
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Order Info</h4>
                                    <div class="space-y-1 text-sm">
                                        <p><span class="font-medium">Date:</span> @formatdate(is_array($order) ? $order['created_at'] : $order->created_at)</p>
                                        <p><span class="font-medium">Status:</span> 
                                            @if((is_array($order) ? $order['status'] : $order->status) === \App\Models\Order::STATUS_PENDING)
                                                <span class="text-yellow-700">Pending</span>
                                            @elseif((is_array($order) ? $order['status'] : $order->status) === \App\Models\Order::STATUS_COMPLETED)
                                                <span class="text-green-700">Completed</span>
                                            @elseif((is_array($order) ? $order['status'] : $order->status) === \App\Models\Order::STATUS_CANCELLED)
                                                <span class="text-red-700">Cancelled</span>
                                            @endif
                                        </p>
                                        <p><span class="font-medium">Items:</span> {{ is_array($order) && isset($order['items']) ? count($order['items']) : (is_object($order) && method_exists($order, 'getTotalItems') ? $order->getTotalItems() : (is_object($order) ? $order->items()->count() : 'N/A')) }}</p>
                                        <p><span class="font-medium">Total:</span> ${{ number_format(is_array($order) ? $order['total'] : $order->total, 2) }}</p>
                                    </div>
                                </div>
                                
                                <!-- Customer Info (Admin Only) -->
                                @if($isAdmin)
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Customer</h4>
                                    <div class="space-y-1 text-sm">
                                        <p class="font-medium">{{ is_array($order) 
                                            ? (isset($order['user']) ? (is_array($order['user']) ? ($order['user']['name'] ?? 'Unknown') : $order['user']->name) : 'Unknown') 
                                            : (isset($order->user) ? $order->user->name : 'Unknown') }}</p>
                                        <p class="text-gray-500">{{ is_array($order) 
                                            ? (isset($order['user']) ? (is_array($order['user']) ? ($order['user']['email'] ?? '') : $order['user']->email) : '') 
                                            : (isset($order->user) ? $order->user->email : '') }}</p>
                                        @if(is_array($order) 
                                            ? (isset($order['user']) && (is_array($order['user']) 
                                                ? isset($order['user']['customer_number']) && $order['user']['customer_number'] 
                                                : isset($order['user']->customer_number) && $order['user']->customer_number)) 
                                            : (isset($order->user) && isset($order->user->customer_number) && $order->user->customer_number))
                                            <p class="text-gray-500">
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
                                </div>
                                @endif
                                
                                <!-- Actions -->
                                <div class="bg-gray-50 rounded-lg p-3 flex flex-col sm:justify-between">
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Actions</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-auto">
                                        <!-- View Details Button -->
                                        <button 
                                            onclick="Livewire.dispatch('viewOrderDetails', { orderId: {{ is_array($order) ? $order['id'] : $order->id }} })" 
                                            class="sm:col-span-2 inline-flex justify-center items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View Full Details
                                        </button>
                                        
                                        @if($isAdmin && (is_array($order) ? $order['status'] : $order->status) === \App\Models\Order::STATUS_PENDING)
                                            @can('manage orders')
                                                <!-- Complete Order Button -->
                                                <button 
                                                    @click.stop="if(confirm('Are you sure you want to mark this order as completed?')) { 
                                                        expandedId = null;
                                                        Livewire.dispatch('updateStatus', { 
                                                            orderId: {{ is_array($order) ? $order['id'] : $order->id }}, 
                                                            status: '{{ \App\Models\Order::STATUS_COMPLETED }}' 
                                                        });
                                                    }" 
                                                    class="sm:col-span-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Complete
                                                </button>
                                                
                                                <!-- Cancel Order Button -->
                                                <button 
                                                    @click.stop="if(confirm('Are you sure you want to cancel this order?')) { 
                                                        expandedId = null;
                                                        Livewire.dispatch('updateStatus', { 
                                                            orderId: {{ is_array($order) ? $order['id'] : $order->id }}, 
                                                            status: '{{ \App\Models\Order::STATUS_CANCELLED }}' 
                                                        });
                                                    }" 
                                                    class="sm:col-span-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Cancel
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
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