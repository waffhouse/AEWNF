<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class OrderList extends Component
{
    public $orders = [];
    public $hasMorePages = true;
    public $isLoading = false;
    public $totalCount = 0;
    public $loadedCount = 0;
    public $search = '';
    
    protected OrderService $orderService;
    
    /**
     * Component initialization
     */
    public function boot(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    
    /**
     * Initialize component properties from parent
     */
    public function mount($orders, $totalCount, $loadedCount, $hasMorePages, $isLoading, $search = '')
    {
        $this->orders = $orders;
        $this->totalCount = $totalCount;
        $this->loadedCount = $loadedCount;
        $this->hasMorePages = $hasMorePages;
        $this->isLoading = $isLoading;
        $this->search = $search;
    }
    
    /**
     * Listen for order data updates from parent
     */
    #[On('ordersUpdated')]
    public function updateOrdersList($orders)
    {
        // Accept the orders as-is, since we're now handling arrays in the template
        $this->orders = $orders;
    }
    
    /**
     * Load more orders (called from JavaScript)
     */
    public function loadMore()
    {
        $this->dispatch('loadMore');
    }
    
    /**
     * Open order details
     */
    public function viewOrderDetails($orderId)
    {
        $this->dispatch('viewOrderDetails', $orderId);
    }
    
    /**
     * Update order status
     */
    public function updateStatus($orderId, $status)
    {
        if (!Auth::user()->can('manage orders')) {
            $this->dispatch('error', 'You do not have permission to update order status');
            return;
        }
        
        $this->dispatch('updateStatus', [$orderId, $status]);
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.orders.order-list', [
            'isAdmin' => true,
        ]);
    }
}