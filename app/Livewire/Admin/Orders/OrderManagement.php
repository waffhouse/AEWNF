<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use App\Models\User;
use App\Traits\AdminAuthorization;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class OrderManagement extends Component
{
    use AdminAuthorization;

    // For infinite scroll
    public $orders = [];
    public $hasMorePages = true;
    public $isLoading = false;
    public $totalCount = 0;
    public $loadedCount = 0;

    public $search = '';
    public int $perPage = 10;
    public $selectedOrder = null;
    public $viewingOrderDetails = false;
    
    // Listen for event to refresh orders
    #[On('order-status-updated')]
    public function refreshOrders()
    {
        $this->resetOrders();
        $this->dispatch('resetOrders');
    }
    
    public function mount()
    {
        // Only users with 'view all orders' or 'manage orders' permission can access this component
        if (!Auth::user()->hasAnyPermission(['view all orders', 'manage orders'])) {
            $this->dispatch('error', 'You do not have permission to manage orders');
            $this->redirect(route('dashboard'));
        }

        // Load initial orders
        $this->loadOrders();
    }
    
    public function updatingSearch()
    {
        $this->resetOrders();
        $this->dispatch('resetOrders');
    }
    
    // Status filter removed

    public function updatingPerPage()
    {
        $this->resetOrders();
        $this->dispatch('resetOrders');
    }
    
    public function viewOrderDetails($orderId)
    {
        $this->selectedOrder = Order::with(['items.inventory', 'user'])->findOrFail($orderId);
        $this->viewingOrderDetails = true;
    }
    
    public function closeOrderDetails()
    {
        // Remove the body lock through inline JavaScript for immediate effect
        $this->js('document.body.classList.remove("overflow-hidden")'); 
        
        $this->viewingOrderDetails = false;
        $this->selectedOrder = null;
    }
    
    public function updateStatus($orderId, $status)
    {
        // Check if user has permission to manage orders
        if (!Auth::user()->can('manage orders')) {
            $this->dispatch('error', 'You do not have permission to update order status');
            return;
        }
        
        $order = Order::findOrFail($orderId);
        
        switch ($status) {
            case Order::STATUS_COMPLETED:
                $success = $order->complete();
                break;
            case Order::STATUS_CANCELLED:
                $success = $order->cancel();
                break;
            default:
                $this->dispatch('error', 'Invalid status');
                return;
        }
        
        if ($success) {
            $this->dispatch('notification', [
                'type' => 'success',
                'message' => 'Order status updated successfully'
            ]);
            
            // Refresh the selected order if we're viewing details
            if ($this->selectedOrder && $this->selectedOrder->id === $orderId) {
                $this->selectedOrder = Order::with(['items.inventory', 'user'])->findOrFail($orderId);
            }
            
            // Dispatch event to refresh orders
            $this->dispatch('order-status-updated');
        } else {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Failed to update order status'
            ]);
        }
    }

    public function loadOrders()
    {
        $this->isLoading = true;
        
        $query = $this->getFilteredQuery();
        
        // Get total count for informational purposes
        $this->totalCount = $query->count();
        
        // Get orders for current page
        $newOrders = $query->offset($this->loadedCount)
                           ->limit($this->perPage + 1) // get one extra to check if there are more
                           ->get();
        
        // Check if there are more orders
        $this->hasMorePages = $newOrders->count() > $this->perPage;
        
        // Remove the extra item we used to check for more
        if ($this->hasMorePages) {
            $newOrders = $newOrders->slice(0, $this->perPage);
        }
        
        // Append new orders to existing collection
        foreach ($newOrders as $order) {
            $this->orders[] = $order;
        }
        
        // Update loaded count
        $this->loadedCount += $newOrders->count();
        
        $this->isLoading = false;
    }
    
    public function loadMore()
    {
        if ($this->hasMorePages && !$this->isLoading) {
            $this->loadOrders();
        }
    }
    
    public function resetOrders()
    {
        $this->orders = [];
        $this->loadedCount = 0;
        $this->hasMorePages = true;
        $this->loadOrders();
    }

    protected function getFilteredQuery()
    {
        $query = Order::query()->with('user');
        
        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('id', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function ($userQuery) {
                      $userQuery->where('name', 'like', '%' . $this->search . '%')
                              ->orWhere('email', 'like', '%' . $this->search . '%')
                              ->orWhere('customer_number', 'like', '%' . $this->search . '%');
                  });
            });
        }
        
        // Always prioritize pending orders, then sort by newest first
        $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'completed' THEN 1 ELSE 2 END")
              ->orderBy('created_at', 'desc');
        
        return $query;
    }
    
    public function render()
    {
        return view('livewire.admin.orders.order-management', [
            'pendingCount' => Order::where('status', Order::STATUS_PENDING)->count(),
            'completedCount' => Order::where('status', Order::STATUS_COMPLETED)->count(),
            'cancelledCount' => Order::where('status', Order::STATUS_CANCELLED)->count(),
        ]);
    }
}