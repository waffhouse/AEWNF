<div>
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
    
    @if(count($orders) > 0)
        <ul class="mt-4 list-disc pl-4">
            @foreach($orders as $order)
                @php 
                    $orderId = is_array($order) ? $order['id'] : $order->id;
                    $total = is_array($order) ? $order['total'] : $order->total;
                @endphp
                <li wire:key="simple-order-{{ $orderId }}">
                    Order #{{ $orderId }} - ${{ number_format($total, 2) }}
                </li>
            @endforeach
        </ul>
    @else
        <p class="mt-4">No orders found</p>
    @endif
    
    @if($viewingOrderDetails && $selectedOrder)
        <div class="mt-4 p-4 border border-gray-200 rounded">
            <h4>Order #{{ $selectedOrder->id }} Details</h4>
            <p>Total: ${{ number_format($selectedOrder->total, 2) }}</p>
        </div>
    @endif
</div>