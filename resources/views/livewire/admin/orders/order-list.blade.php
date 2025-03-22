<div>
    <!-- Shared Orders List Component -->
    <x-orders-list 
        :orders="$orders" 
        :is-admin="$isAdmin" 
        :total-count="$totalCount" 
        :loaded-count="$loadedCount" 
        :has-more-pages="$hasMorePages" 
        :is-loading="$isLoading"
        :search="$search ?? ''"
    />
</div>