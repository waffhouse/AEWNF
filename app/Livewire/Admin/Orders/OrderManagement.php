<?php
namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use App\Traits\AdminAuthorization;
use App\Traits\InfiniteScrollable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class OrderManagement extends Component
{
    use AdminAuthorization;
    use InfiniteScrollable;
    
    // For searching
    public $search = '';
    
    public $selectedOrder = null;
    public $viewingOrderDetails = false;
    
    // For infinite scroll pagination
    public int $perPage = 10;
    public array $orders = [];
    
    protected OrderService $orderService;
    
    /**
     * Listen for events to refresh orders
     */
    #[On('order-status-updated')]
    public function refreshOrders()
    {
        $this->resetItems($this->getFilteredQuery(), 'orders');
        $this->dispatch('resetOrders');
        
        // Convert to array but include relationships properly
        $orders = collect($this->orders)->map(function($order) {
            if (is_object($order)) {
                $data = $order->toArray();
                if ($order->relationLoaded('user')) {
                    $data['user'] = $order->user->toArray();
                }
                return $data;
            }
            return $order;
        })->toArray();
        
        $this->dispatch('ordersUpdated', $orders);
    }
    
    /**
     * Handle load more for infinite scrolling
     * Called by the OrderList component
     */
    #[On('loadMore')]
    public function loadMore()
    {
        if ($this->hasMorePages && !$this->isLoading) {
            $this->loadItems($this->getFilteredQuery(), 'orders');
            
            // Convert to array but include relationships properly
            $orders = collect($this->orders)->map(function($order) {
                if (is_object($order)) {
                    $data = $order->toArray();
                    if ($order->relationLoaded('user')) {
                        $data['user'] = $order->user->toArray();
                    }
                    return $data;
                }
                return $order;
            })->toArray();
            
            $this->dispatch('ordersUpdated', $orders);
        }
    }
    
    /**
     * Listen for event to update order status
     */
    #[On('updateStatus')]
    public function handleUpdateStatus($orderId = null, $status = null, $data = null)
    {
        // Handle different parameter formats:
        // 1. Directly passed orderId and status
        // 2. Array format with indices [0, 1]
        // 3. Array/object with named keys 'orderId' and 'status'
        
        if ($orderId !== null && $status !== null) {
            // Parameters already provided directly
        } elseif ($data !== null) {
            // Data parameter provided
            if (is_array($data) && isset($data[0]) && isset($data[1])) {
                // Original array format from Livewire component
                $orderId = $data[0];
                $status = $data[1];
            } else {
                // Named parameter format from Blade component
                $orderId = $data['orderId'] ?? null;
                $status = $data['status'] ?? null;
            }
        } elseif (is_array($orderId) && !$status) {
            // First parameter is the data array
            if (isset($orderId[0]) && isset($orderId[1])) {
                // It's the array format
                $status = $orderId[1];
                $orderId = $orderId[0];
            } else {
                // It's an object/array with named keys
                $status = $orderId['status'] ?? null;
                $orderId = $orderId['orderId'] ?? null;
            }
        }
        
        if (!$orderId || !$status) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Invalid order data provided'
            ]);
            return;
        }
        
        $success = $this->updateStatus($orderId, $status);
        
        if ($success) {
            // Force a refresh of the orders to reflect the change immediately
            $this->resetItems($this->getFilteredQuery(), 'orders');
            
            // Convert to array but include relationships properly
            $orders = collect($this->orders)->map(function($order) {
                if (is_object($order)) {
                    $data = $order->toArray();
                    if ($order->relationLoaded('user')) {
                        $data['user'] = $order->user->toArray();
                    }
                    return $data;
                }
                return $order;
            })->toArray();
            
            // Update any component that displays orders
            $this->dispatch('ordersUpdated', $orders);
            
            // Also update order statistics in UI
            $orderStats = $this->orderService->getOrderStats();
            $this->dispatch('orderStatsUpdated', [
                'pending' => $orderStats['pending'],
                'completed' => $orderStats['completed'],
                'cancelled' => $orderStats['cancelled']
            ]);
        }
    }
    
    /**
     * Component initialization
     */
    public function boot(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    
    /**
     * Load component and check permissions
     */
    public function mount()
    {
        // Only users with 'view all orders' or 'manage orders' permission can access this component
        if (!Auth::user()->hasAnyPermission(['view all orders', 'manage orders'])) {
            $this->dispatch('error', 'You do not have permission to manage orders');
            $this->redirect(route('dashboard'));
        }
        
        // Load initial orders
        $this->resetItems($this->getFilteredQuery(), 'orders');
        
        // Dispatch to ensure the OrderList component has the latest data
        // Convert to array but include relationships properly
        $orders = collect($this->orders)->map(function($order) {
            if (is_object($order)) {
                $data = $order->toArray();
                if ($order->relationLoaded('user')) {
                    $data['user'] = $order->user->toArray();
                }
                return $data;
            }
            return $order;
        })->toArray();
        
        $this->dispatch('ordersUpdated', $orders);
    }
    
    /**
     * Reset orders when search is updated
     */
    public function updatedSearch()
    {
        $this->resetItems($this->getFilteredQuery(), 'orders');
        $this->dispatch('resetOrders');
        
        // Convert to array but include relationships properly
        $orders = collect($this->orders)->map(function($order) {
            if (is_object($order)) {
                $data = $order->toArray();
                if ($order->relationLoaded('user')) {
                    $data['user'] = $order->user->toArray();
                }
                return $data;
            }
            return $order;
        })->toArray();
        
        $this->dispatch('ordersUpdated', $orders);
    }
    
    /**
     * View order details
     * 
     * @param int $orderId The ID of the order to view
     */
    #[On('viewOrderDetails')]
    public function viewOrderDetails($orderId)
    {
        $this->selectedOrder = $this->orderService->getOrderById($orderId, ['items.inventory', 'user']);
        $this->viewingOrderDetails = true;
    }
    
    /**
     * Close order details modal
     */
    #[On('closeOrderDetails')]
    public function closeOrderDetails()
    {
        // Remove the body lock through inline JavaScript for immediate effect
        $this->js('document.body.classList.remove("overflow-hidden")');
        
        $this->viewingOrderDetails = false;
        $this->selectedOrder = null;
    }
    
    /**
     * Update order status
     * 
     * @param int $orderId The ID of the order to update
     * @param string $status The new status
     * @return bool Success indicator
     */
    public function updateStatus($orderId, $status)
    {
        // Check if user has permission to manage orders
        if (!Auth::user()->can('manage orders')) {
            $this->dispatch('error', 'You do not have permission to update order status');
            return false;
        }
        
        $success = $this->orderService->updateOrderStatus($orderId, $status, Auth::user());
        
        if ($success) {
            $this->dispatch('notification', [
                'type' => 'success',
                'message' => 'Order status updated successfully'
            ]);
            
            // Refresh the selected order if we're viewing details
            if ($this->selectedOrder && $this->selectedOrder->id === $orderId) {
                $this->selectedOrder = $this->orderService->getOrderById($orderId, ['items.inventory', 'user']);
            }
            
            // Dispatch event to refresh orders
            $this->dispatch('order-status-updated');
        } else {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Failed to update order status'
            ]);
        }
        
        return $success;
    }
    
    /**
     * Get filtered query for orders
     */
    protected function getFilteredQuery()
    {
        $filters = [];
        
        // Add search filter if provided
        if (!empty($this->search)) {
            $filters['search'] = $this->search;
        }
        
        // Always include options to load relationships needed by the view
        $options = [
            'with' => ['user', 'items.inventory']
        ];
        
        return $this->orderService->getOrdersQuery($filters, $options);
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        // Get order statistics
        $orderStats = $this->orderService->getOrderStats();
        
        // Load orders directly here instead of using OrderList component
        // Only load if orders array is empty (first load)
        if (empty($this->orders)) {
            $this->loadItems($this->getFilteredQuery(), 'orders');
        }
        
        return view('livewire.admin.orders.order-management', [
            'pendingCount' => $orderStats['pending'],
            'completedCount' => $orderStats['completed'],
            'cancelledCount' => $orderStats['cancelled'],
            'orders' => $this->orders,
            'totalCount' => $this->totalCount,
            'loadedCount' => $this->loadedCount,
            'hasMorePages' => $this->hasMorePages,
            'isLoading' => $this->isLoading,
        ]);
    }
}