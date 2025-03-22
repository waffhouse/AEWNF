<?php

namespace App\Livewire\Cart;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderSummary extends Component
{
    public $cart;
    public $total = 0;
    public $itemCount = 0;
    public $notes = '';
    
    protected $listeners = [
        'cartUpdated' => 'refreshSummary'
    ];
    
    public function mount($cart = null, $total = 0, $itemCount = 0)
    {
        $this->cart = $cart;
        $this->total = $total;
        $this->itemCount = $itemCount;
    }
    
    public function refreshSummary()
    {
        $this->cart = Auth::user()->getOrCreateCart();
        $this->total = $this->cart ? $this->cart->getTotal() : 0;
        $this->itemCount = $this->cart ? $this->cart->getTotalItems() : 0;
    }
    
    public function checkout()
    {
        // Dispatch event to parent for handling checkout
        $this->dispatch('initiateCheckout', ['notes' => $this->notes]);
        
        // Reset notes
        $this->notes = '';
    }
    
    public function render()
    {
        return view('livewire.cart.order-summary', [
            'canPlaceOrders' => Auth::check() && Auth::user()->can('place orders')
        ]);
    }
}