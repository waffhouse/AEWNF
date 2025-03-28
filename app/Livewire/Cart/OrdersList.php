<?php

namespace App\Livewire\Cart;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

class OrdersList extends Component
{
    public $viewingOrderDetails = false;
    public $selectedOrder = null;
    
    public function mount()
    {
        // Check if user is authenticated and has permission to view orders
        if (!Auth::check() || !Auth::user()->can('view own orders')) {
            return redirect()->route('login');
        }
    }

    /**
     * View order details - redirected to use the global modal system
     * 
     * @param int|array $orderId The order ID or event data containing orderId
     */
    #[On('viewOrderDetails')]
    public function viewOrderDetails($orderId)
    {
        // Handle both direct ID and event data formats
        if (is_array($orderId) && isset($orderId['orderId'])) {
            $orderId = $orderId['orderId'];
        }
        
        // Instead of loading the order here, dispatch the global event
        // that will be caught by the global OrderDetailModal component
        $this->dispatch('showOrderDetail', $orderId);
    }

    #[Title('Your Orders')]
    public function render()
    {
        $orders = Auth::user()->orders()->latest()->get();
        
        return view('livewire.cart.orders-list', [
            'orders' => $orders
        ])->layout('layouts.app');
    }
}