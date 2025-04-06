<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderDetails extends Component
{
    public Order $order;

    public bool $show = true;

    protected OrderService $orderService;

    /**
     * Component initialization
     */
    public function boot(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Close the order details modal
     */
    public function closeOrderDetails()
    {
        // Allow parent component to handle closing
        $this->dispatch('closeOrderDetails');
    }

    /**
     * Update order status from the modal
     */
    public function updateStatus($status)
    {
        // Check if user has permission to manage orders
        if (! Auth::user()->can('manage orders')) {
            // Notification removed
            return;
        }

        // Use OrderService to update the status
        $success = $this->orderService->updateOrderStatus($this->order->id, $status, Auth::user());

        if ($success) {
            // Notification removed

            // Refresh the order details
            $this->order = $this->orderService->getOrderById($this->order->id, ['items.inventory', 'user']);

            // Dispatch event to refresh orders list
            $this->dispatch('order-status-updated');
        } else {
            // Notification removed
        }
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.orders.order-details', [
            'order' => $this->order,
        ]);
    }
}
