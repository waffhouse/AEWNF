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
     * View order details - can be called directly or via event
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
        
        $this->selectedOrder = Order::with(['items.inventory', 'user'])
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);
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

    #[Title('Your Orders')]
    public function render()
    {
        $orders = Auth::user()->orders()->latest()->get();
        
        return view('livewire.cart.orders-list', [
            'orders' => $orders
        ])->layout('layouts.app');
    }
}