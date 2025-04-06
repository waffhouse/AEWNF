<?php

namespace App\Livewire\Cart;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

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
        if (! Auth::check()) {
            $this->count = 0;
            $this->total = 0;

            return;
        }

        $cart = Auth::user()->getOrCreateCart();

        // Calculate cart counts directly
        $this->count = $cart->items()->sum('quantity');

        // Calculate total
        $total = 0;
        foreach ($cart->items as $item) {
            $total += $item->price * $item->quantity;
        }
        $this->total = $total;
    }

    public function render()
    {
        // Always get fresh cart data on render for real-time updates
        $this->updateCartData();

        return view('livewire.cart.cart-counter');
    }
}
