<div>
    <!-- Scroll to Top Button -->
    <x-scroll-to-top />
    
    <h2 class="text-lg font-semibold flex items-center text-red-600 mb-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
        </svg>
        Order Management
    </h2>
    <p class="text-sm text-gray-600">{{ $pendingCount }} pending, {{ $completedCount }} completed, {{ $cancelledCount }} cancelled</p>
    
    <!-- Search Box -->
    <div class="my-4">
        <x-filters.search-input
            model="search"
            placeholder="Search orders by #, customer, or product..."
            id="orders-search"
            class="w-full md:w-96"
        />
    </div>
    
    <!-- Orders List Component -->
    <div class="mt-4">
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
    
    <!-- Order Details Modal -->
    @if($viewingOrderDetails && $selectedOrder)
        <x-order-details-modal :order="$selectedOrder" />
    @endif
</div>