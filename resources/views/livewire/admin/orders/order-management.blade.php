<div>
    <!-- Scroll to Top Button -->
    <x-scroll-to-top />
    
    <h3 class="text-lg font-medium text-gray-900 mb-2">Order Management</h3>
    <p>{{ $pendingCount }} pending, {{ $completedCount }} completed, {{ $cancelledCount }} cancelled</p>
    
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