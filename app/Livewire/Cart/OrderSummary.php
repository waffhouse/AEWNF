<?php

namespace App\Livewire\Cart;

use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderSummary extends Component
{
    public $cart;

    public $total = 0;

    public $itemCount = 0;

    public $notes = '';

    protected $listeners = [
        'cartUpdated' => 'refreshSummary',
    ];

    public function mount($cart = null, $total = 0, $itemCount = 0)
    {
        $this->cart = $cart;
        $this->total = $total;
        $this->itemCount = $itemCount;
    }

    public function refreshSummary()
    {
        $cartService = app(CartService::class);
        $this->total = $cartService->getCartTotal();
        $this->itemCount = $cartService->getCartCount();
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
            'canPlaceOrders' => Auth::check() && Auth::user()->can('place orders'),
        ]);
    }
}
