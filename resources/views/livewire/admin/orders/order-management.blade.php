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

    <!-- Use the shared component directly -->
    <div>
        <x-orders-list 
            :orders="$orders" 
            :is-admin="true" 
            :total-count="$totalCount" 
            :loaded-count="$loadedCount" 
            :has-more-pages="$hasMorePages" 
            :is-loading="$isLoading"
            :search="$search ?? ''"
        />
    </div>
    
    <!-- Place modal inside main component div to prevent multiple root elements issue -->
    @if($viewingOrderDetails && $selectedOrder)
        <x-order-details-modal :order="$selectedOrder" />
    @endif
</div>