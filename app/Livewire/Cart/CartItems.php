<?php

namespace App\Livewire\Cart;

use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CartItems extends Component
{
    public $cartItems = [];
    
    protected $listeners = [
        'refreshCart' => 'refreshCartItems'
    ];
    
    public function mount($cartItems = [])
    {
        $this->cartItems = $cartItems;
    }
    
    public function refreshCartItems($cartItems = null)
    {
        if ($cartItems) {
            $this->cartItems = $cartItems;
        } else {
            $cart = Auth::user()->getOrCreateCart();
            $this->cartItems = $cart->items()->with('inventory')->get();
        }
    }
    
    public function updateQuantity($cartItemId, $quantity)
    {
        $cartItem = CartItem::findOrFail($cartItemId);
        
        // Make sure the cart item belongs to the current user
        if ($cartItem->cart->user_id !== Auth::id()) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'You do not have permission to update this item'
            ]);
            return;
        }
        
        // Validate quantity
        if ($quantity < 1) {
            $quantity = 1;
        }
        
        $cartItem->update(['quantity' => $quantity]);
        
        // Refresh local cart items to update the UI
        $this->refreshCartItems();
        
        $this->dispatch('notification', [
            'type' => 'success',
            'message' => 'Cart updated'
        ]);
        
        // Notify parent component to refresh cart
        $this->dispatch('cartItemUpdated');
        
        // Dispatch cart-updated event to update counter
        $this->dispatch('cart-updated');
    }
    
    public function removeItem($cartItemId)
    {
        $cartItem = CartItem::findOrFail($cartItemId);
        
        // Make sure the cart item belongs to the current user
        if ($cartItem->cart->user_id !== Auth::id()) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'You do not have permission to remove this item'
            ]);
            return;
        }
        
        // Store inventory_id before deleting to broadcast event
        $inventoryId = $cartItem->inventory_id;
        
        $cartItem->delete();
        
        // Refresh local cart items to update the UI
        $this->refreshCartItems();
        
        // Broadcast that item was removed so AddToCart component can update
        $this->dispatch('cartItemRemoved', inventoryId: $inventoryId)->to('cart.add-to-cart');
        
        // Notify parent component to refresh cart
        $this->dispatch('cartItemRemoved');
        
        // Update cart count
        $this->dispatch('cart-updated');
        
        $this->dispatch('notification', [
            'type' => 'success',
            'message' => 'Item removed from cart'
        ]);
    }
    
    public function render()
    {
        return view('livewire.cart.cart-items');
    }
}