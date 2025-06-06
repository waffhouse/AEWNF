<x-app-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Hero Banner with Red Gradient Background -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6 sm:rounded-t-lg shadow-md">
                <div class="flex flex-col sm:flex-row justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold">Dashboard</h2>
                        <p class="text-sm text-red-100 mt-1">Welcome, {{ Auth::user()->name }}. Access key features and information below.</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-b-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                            <div>
                                <h2 class="text-xl font-semibold">Your Account Overview</h2>
                            </div>
                        </div>
                    </div>

                    @if (session('message'))
                    <div class="mb-4 bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('message') }}</span>
                    </div>
                    @endif

                    <!-- Quick Access Icons -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <div class="flex flex-wrap gap-6 justify-center sm:justify-start">
                            <a href="{{ route('inventory.catalog') }}" class="flex flex-col items-center justify-center text-center">
                                <div class="w-12 h-12 mb-2 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">Catalog</span>
                            </a>
                            <a href="{{ route('customer.cart') }}" class="flex flex-col items-center justify-center text-center">
                                <div class="w-12 h-12 mb-2 flex items-center justify-center bg-green-100 text-green-600 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">Cart</span>
                            </a>
                            <a href="{{ route('customer.orders') }}" class="flex flex-col items-center justify-center text-center">
                                <div class="w-12 h-12 mb-2 flex items-center justify-center bg-purple-100 text-purple-600 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">Orders</span>
                            </a>
                            <a href="{{ route('sales') }}" class="flex flex-col items-center justify-center text-center">
                                <div class="w-12 h-12 mb-2 flex items-center justify-center bg-yellow-100 text-yellow-600 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">History</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Your Top Items Section with View More -->
                    @if(isset($topItems) && $topItems->count() > 0)
                    <div class="mb-6 bg-white p-4 rounded-lg border border-gray-200 shadow" x-data="{ showMore: false }">
                        <div class="mb-3">
                            <h3 class="text-lg font-medium text-gray-800">Your Top Items</h3>
                        </div>
                        
                        <div class="space-y-2">
                            <ul class="divide-y divide-gray-100">
                                @foreach($topItems as $index => $product)
                                <li class="py-2" 
                                    x-show="showMore || {{ $index }} < 4"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform scale-y-95"
                                    x-transition:enter-end="opacity-100 transform scale-y-100"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 transform scale-y-100"
                                    x-transition:leave-end="opacity-0 transform scale-y-95"
                                >
                                    <x-product-item 
                                        :product="$product" 
                                        variant="compact" 
                                        :itemKey="'top-item-'.$product->id" 
                                    />
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        
                        <div class="mt-3 pt-2 border-t border-gray-100 flex justify-center items-center space-x-6">
                            @if($topItems->count() > 4)
                            <button
                                type="button"
                                @click="showMore = !showMore"
                                class="text-sm text-blue-600 hover:text-blue-800 flex items-center focus:outline-none"
                            >
                                <span x-text="showMore ? 'Show Less' : 'View More Items'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 transition-transform" :class="showMore ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            @endif
                            <a href="{{ route('inventory.catalog') }}" class="text-sm text-red-600 hover:text-red-800">
                                Browse Full Catalog →
                            </a>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Featured Brands Section with Accordion -->
                    @if(isset($popularBrands) && $popularBrands->count() > 0)
                    <div class="mb-6 bg-white p-4 rounded-lg border border-gray-200 shadow">
                        <h3 class="text-lg font-medium text-gray-800 mb-3">Featured Brands</h3>
                        
                        <div class="space-y-2" x-data="{ openBrand: null }">
                            @foreach($popularBrands as $brand => $products)
                            <div class="border border-gray-100 rounded-md overflow-hidden">
                                <div 
                                    class="flex justify-between items-center p-3 bg-gray-50 cursor-pointer"
                                    @click="openBrand = openBrand === '{{ $brand }}' ? null : '{{ $brand }}'"
                                >
                                    <div class="flex items-center">
                                        <svg 
                                            xmlns="http://www.w3.org/2000/svg" 
                                            class="h-4 w-4 transform transition-transform duration-200 mr-2" 
                                            :class="openBrand === '{{ $brand }}' ? 'rotate-180' : ''"
                                            fill="none" 
                                            viewBox="0 0 24 24" 
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">{{ $brand }}</h4>
                                        @if($products->count() > 3)
                                        <span class="ml-2 text-xs text-gray-500">
                                            ({{ $products->count() }} products)
                                        </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('inventory.catalog') }}?search={{ urlencode($brand) }}" 
                                       class="text-xs text-red-600 hover:text-red-800"
                                       @click.stop
                                    >
                                        View All →
                                    </a>
                                </div>
                                
                                <div 
                                    x-show="openBrand === '{{ $brand }}'" 
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform scale-y-90"
                                    x-transition:enter-end="opacity-100 transform scale-y-100"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 transform scale-y-100"
                                    x-transition:leave-end="opacity-0 transform scale-y-90"
                                    class="origin-top"
                                    style="display: none;"
                                >
                                    <ul class="divide-y divide-gray-100">
                                        @foreach($products as $index => $product)
                                        <li class="py-2 px-3 {{ $product->initially_visible ? '' : 'border-t border-gray-200' }}">
                                            <x-product-item 
                                                :product="$product" 
                                                variant="compact" 
                                                :itemKey="'featured-brand-'.$product->id" 
                                            />
                                        </li>
                                        @endforeach
                                    </ul>
                                    @if($products->count() > 3)
                                    <div class="px-3 py-2 text-center text-sm text-gray-500 bg-gray-50 rounded-b">
                                        Showing all {{ $products->count() }} {{ $brand }} products
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4 pt-2 border-t border-gray-100 text-center">
                            <a href="{{ route('inventory.catalog') }}" class="text-sm text-red-600 hover:text-red-800">
                                Browse Full Catalog →
                            </a>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Recent Activity Section -->
                    @if(Auth::user()->customer_number)
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Recent Activity</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Recent Orders -->
                            <div class="bg-white p-5 rounded-lg border border-gray-200 shadow">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-md font-medium text-gray-800">Recent Orders</h4>
                                    <a href="{{ route('customer.orders') }}" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                                </div>
                                @php
                                    $recentOrders = Auth::user()->orders()->latest()->take(3)->get();
                                @endphp
                                
                                @if($recentOrders->count() > 0)
                                    <div class="space-y-3">
                                        @foreach($recentOrders as $order)
                                            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                                                <div>
                                                    <span class="block text-sm font-medium">Order #{{ $order->id }}</span>
                                                    <span class="block text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }}</span>
                                                </div>
                                                <div class="text-right">
                                                    <span class="block text-sm font-medium">${{ number_format($order->total, 2) }}</span>
                                                    <div class="flex items-center justify-end space-x-2">
                                                        <span class="inline-block px-2 py-1 text-xs rounded 
                                                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                            {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                                            {{ ucfirst($order->status) }}
                                                        </span>
                                                        <button
                                                            type="button"
                                                            onclick="Livewire.dispatch('showOrderDetail', [{{ $order->id }}])"
                                                            class="text-xs text-blue-600 hover:text-blue-800 underline"
                                                        >
                                                            Details
                                                        </button>
                                                    </div>
                                                </div>
                                                
<!-- Order details now handled by the global OrderDetailModal component -->
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 text-sm py-4">No recent orders found.</p>
                                @endif
                            </div>
                            
                            <!-- Recent Transactions -->
                            <div class="bg-white p-5 rounded-lg border border-gray-200 shadow">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-md font-medium text-gray-800">Recent Transactions</h4>
                                    <a href="{{ route('sales') }}" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                                </div>
                                @php
                                    $recentTransactions = \App\Models\Sale::where('entity_id', Auth::user()->customer_number)
                                        ->orderBy('date', 'desc')
                                        ->take(3)
                                        ->get();
                                @endphp
                                
                                @if($recentTransactions->count() > 0)
                                    <div class="space-y-3">
                                        @foreach($recentTransactions as $transaction)
                                            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                                                <div>
                                                    <div class="flex items-center">
                                                        <span class="text-sm font-medium mr-2">{{ $transaction->tran_id }}</span>
                                                        <span class="inline-block px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-800">
                                                            {{ $transaction->type }}
                                                        </span>
                                                    </div>
                                                    <span class="block text-xs text-gray-500">{{ $transaction->date->format('M d, Y') }}</span>
                                                </div>
                                                <div class="text-right flex flex-col items-end">
                                                    <span class="block text-sm font-medium">${{ number_format($transaction->total_amount, 2) }}</span>
                                                    <div class="flex items-center mt-1 space-x-2">
                                                        <button
                                                            type="button"
                                                            onclick="Livewire.dispatch('showTransactionDetail', [{{ $transaction->id }}])"
                                                            class="text-xs text-blue-600 hover:text-blue-800 underline"
                                                        >
                                                            Details
                                                        </button>
                                                        <a 
                                                            href="{{ route('sales.invoice', $transaction->id) }}"
                                                            target="_blank" 
                                                            class="text-xs text-red-600 hover:text-red-800 inline-flex items-center"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                            </svg>
                                                            Invoice
                                                        </a>
                                                    </div>
                                                </div>
                                                
                                                <!-- Transaction details now handled by the global TransactionDetailModal component -->
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 text-sm py-4">No recent transactions found.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include hidden AddToCart components for all products to handle the quantity incrementer -->
    <script>
        // Listen for cart updates and refresh the quantity inputs
        document.addEventListener('DOMContentLoaded', function() {
            // Listen for general cart updates
            window.Livewire.on('cart-updated', (data) => {
                // Request the latest cart data
                fetch('/api/check-cart')
                    .then(response => response.json())
                    .then(data => {
                        // Update all quantity inputs based on the received data
                        if (data.items) {
                            // Set all inputs to 0 first 
                            document.querySelectorAll('[id^="quantity-input-"]').forEach(input => {
                                input.value = 0;
                            });
                            
                            // Update inputs that have items in cart
                            Object.keys(data.items).forEach(id => {
                                const input = document.getElementById('quantity-input-' + id);
                                if (input) {
                                    input.value = data.items[id].quantity;
                                }
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching cart data:', error));
            });
            
            // Listen for specific quantity updates (faster response)
            window.Livewire.on('quantity-updated', (data) => {
                const input = document.getElementById('quantity-input-' + data.id);
                if (input) {
                    input.value = data.quantity;
                }
            });
        });
    </script>
    
    <!-- Hidden components are no longer needed since they're included in the product-item component -->
</x-app-layout>
