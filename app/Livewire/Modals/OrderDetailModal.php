<?php

namespace App\Livewire\Modals;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderDetailModal extends Component
{
    public ?int $orderId = null;
    public ?Order $order = null;
    public bool $show = false;
    
    protected $listeners = [
        'showOrderDetail' => 'showOrder',
    ];
    
    /**
     * Get the OrderService instance
     */
    protected function getOrderService(): OrderService
    {
        return app(OrderService::class);
    }
    
    public function showOrder($orderId)
    {
        // Handle both array parameter (from Livewire.dispatch(['id'])) and direct parameter
        if (is_array($orderId)) {
            $orderId = $orderId[0] ?? null;
        }
        
        $this->orderId = $orderId;
        $this->loadOrder();
        $this->show = true;
    }
    
    protected function loadOrder()
    {
        if ($this->orderId) {
            $orderService = $this->getOrderService();
            $this->order = $orderService->getOrderById($this->orderId, ['items.inventory', 'user']);
        }
    }
    
    /**
     * Update order status from the modal
     */
    public function updateStatus($status)
    {
        // Check if user has permission to manage orders
        if (!Auth::user()->can('manage orders')) {
            return;
        }
        
        // Use OrderService to update the status
        $orderService = $this->getOrderService();
        $success = $orderService->updateOrderStatus($this->order->id, $status, Auth::user());
        
        if ($success) {
            // Refresh the order details
            $this->loadOrder();
            
            // Dispatch event to refresh orders list
            $this->dispatch('order-status-updated');
        }
    }
    
    public function close()
    {
        // Remove the body lock through inline JavaScript for immediate effect
        $this->js('document.body.classList.remove("overflow-hidden")'); 
        
        $this->show = false;
        $this->order = null;
        $this->orderId = null;
    }
    
    public function render()
    {
        // If the modal is not being shown, ensure body scroll is restored
        if (!$this->show) {
            $this->js('document.body.classList.remove("overflow-hidden")'); 
        }
        
        return view('livewire.modals.order-detail-modal');
    }
}