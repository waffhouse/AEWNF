<?php

namespace App\Livewire\Cart;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class CartCounter extends Component
{
    public $count = 0;
    public $total = 0;
    public $location = 'default'; // desktop, mobile, mobile-icon
    public $showTotal = false;
    
    public function mount($location = 'default', $showTotal = false)
    {
        $this->location = $location;
        $this->showTotal = $showTotal;
        $this->updateCartData();
    }
    
    // Listen for the general cart update event
    #[On('cart-updated')]
    public function updateCartData()
    {
        if (Auth::check()) {
            // Fetch fresh cart data from the database to ensure accuracy
            $user = Auth::user();
            $user->refresh();
            $cart = $user->getOrCreateCart();
            $this->count = $cart->getTotalItems();
            $this->total = $cart->getTotal();
        } else {
            $this->count = 0;
            $this->total = 0;
        }
    }
    
    public function render()
    {
        // Ensure count and total are up-to-date on each render
        if (Auth::check()) {
            $cart = Auth::user()->getOrCreateCart();
            $this->count = $cart->getTotalItems();
            $this->total = $cart->getTotal();
        }
        
        return view('livewire.cart.cart-counter');
    }
}