<?php

namespace App\Livewire\Cart;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderConfirmation extends Component
{
    public $viewingOrderDetails = false;
    public $selectedOrder = null;
    
    protected function getListeners()
    {
        return [
            'showOrderDetails' => 'viewOrderDetails'
        ];
    }
    
    public function viewOrderDetails($data)
    {
        // Check if data is an array with orderId or just the orderId directly
        $orderId = is_array($data) && isset($data['orderId']) ? $data['orderId'] : $data;
        
        $this->selectedOrder = Order::with(['items.inventory', 'user'])
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);
            
        $this->viewingOrderDetails = true;
    }
    
    public function closeOrderDetails()
    {
        // Remove the body lock through inline JavaScript for immediate effect
        $this->js('document.body.classList.remove("overflow-hidden")');
        
        $this->viewingOrderDetails = false;
        $this->selectedOrder = null;
    }
    
    public function render()
    {
        return view('livewire.cart.order-confirmation');
    }
}