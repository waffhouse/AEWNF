<?php

namespace App\Livewire\Cart;

use App\Services\CartService;
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
        $cartService = app(CartService::class);
        $this->count = $cartService->getCartCount();
        $this->total = $cartService->getCartTotal();
    }
    
    public function render()
    {
        // Always get fresh cart data on render for real-time updates
        $this->updateCartData();
        
        return view('livewire.cart.cart-counter');
    }
}