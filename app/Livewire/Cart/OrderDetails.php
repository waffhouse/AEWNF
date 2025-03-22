<?php

namespace App\Livewire\Cart;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

class OrderDetails extends Component
{
    public Order $order;
    
    public function mount(Order $order)
    {
        // Check if user is authenticated and has permission to view orders
        if (!Auth::check() || !Auth::user()->can('view own orders')) {
            return redirect()->route('login');
        }
        
        // Check that the order belongs to the authenticated user
        if ($order->user_id !== Auth::id() && !Auth::user()->can('view all orders')) {
            abort(403, 'Unauthorized');
        }
        
        $this->order = $order;
    }

    #[Title('Order Details')]
    public function render()
    {
        return view('livewire.cart.order-details')->layout('layouts.app');
    }
}