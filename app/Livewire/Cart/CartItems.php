<?php

namespace App\Livewire\Cart;

use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CartItems extends Component
{
    public $cartItems = [];
    
    protected $listeners = [
        'refreshCart' => 'refreshCartItems',
        'refresh' => 'refreshCartItems'
    ];
    
    // Used for direct updates when using +/- buttons
    public $suppressNotifications = false;
    
    public function mount($cartItems = [])
    {
        $this->refreshCartItems($cartItems);
    }
    
    public function refreshCartItems($cartItems = null)
    {
        if ($cartItems) {
            // Use provided cart items
            $this->cartItems = $cartItems;
        } else {
            // Default to empty array
            $this->cartItems = [];
            
            // Get from database directly if user is authenticated
            $user = auth()->user();
            if ($user) {
                $cart = $user->getOrCreateCart();
                $this->cartItems = $cart->items()->with('inventory')->get();
            }
        }
    }
    
    public function updateQuantity($itemId, $quantity)
    {
        // Find the cart item directly
        $cartItem = null;
        if (is_numeric($itemId)) {
            $cartItem = CartItem::find($itemId);
        }
        
        if (!$cartItem) {
            $this->dispatch('notification', type: 'error', message: 'Item not found');
            return;
        }
        
        // Validate quantity is a number
        if (!is_numeric($quantity)) {
            $quantity = 1;
        }
        
        // Convert to integer
        $quantity = (int)$quantity;
        
        // Cap quantity at 99
        if ($quantity > 99) {
            $quantity = 99;
        }
        
        $inventoryId = $cartItem->inventory_id;
        
        if ($quantity <= 0) {
            // Remove item if quantity is 0 or less
            $cartItem->delete();
            
            // Refresh cart items to update the UI
            $this->refreshCartItems();
            
            // Broadcast that item was removed so AddToCart component can update
            $this->dispatch('cartItemRemoved', inventoryId: $inventoryId)->to('cart.add-to-cart');
            
            // Notify parent component to refresh cart
            $this->dispatch('cartItemRemoved');
            
            // Update cart count
            $this->dispatch('cart-updated');
            
            $this->dispatch('notification', type: 'warning', message: 'Item removed from cart');
        } else {
            // Update quantity
            $cartItem->update([
                'quantity' => $quantity
            ]);
            
            // Refresh cart items to update the UI
            $this->refreshCartItems();
            
            // Notify parent component to refresh cart
            $this->dispatch('cartItemUpdated');
            
            // Dispatch cart-updated event to update counter
            $this->dispatch('cart-updated');
        }
    }
    
    public function removeItem($itemId)
    {
        // Find the cart item directly
        $cartItem = null;
        if (is_numeric($itemId)) {
            $cartItem = CartItem::find($itemId);
        }
        
        if (!$cartItem) {
            $this->dispatch('notification', type: 'error', message: 'Item not found');
            return;
        }
        
        $inventoryId = $cartItem->inventory_id;
        
        // Delete the cart item
        $cartItem->delete();
        
        // Refresh cart items to update the UI
        $this->refreshCartItems();
        
        // Broadcast that item was removed so AddToCart component can update
        $this->dispatch('cartItemRemoved', inventoryId: $inventoryId)->to('cart.add-to-cart');
        
        // Notify parent component to refresh cart
        $this->dispatch('cartItemRemoved');
        
        // Update cart count
        $this->dispatch('cart-updated');
        
        $this->dispatch('notification', type: 'warning', message: 'Item removed from cart');
    }
    
    public function render()
    {
        return view('livewire.cart.cart-items');
    }
}