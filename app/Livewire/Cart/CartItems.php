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
            // Use CartService to get items
            $cartService = app(CartService::class);
            $items = $cartService->getCartItems();
            
            // Transform to a collection compatible with the view
            if (Auth::check() && !empty($items)) {
                $cart = Auth::user()->getOrCreateCart();
                $this->cartItems = $cart->items()->with('inventory')->get();
            } else {
                $this->cartItems = collect();
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
        
        $inventoryId = $cartItem->inventory_id;
        
        // Use CartService to update quantity
        $cartService = app(CartService::class);
        $result = $cartService->addToCart($inventoryId, $quantity, true);
        
        if (!$result['success']) {
            $this->dispatch('notification', type: 'error', message: $result['message']);
            return;
        }
        
        // Refresh cart items to update the UI
        $this->refreshCartItems();
        
        if ($result['action'] === 'removed') {
            // Broadcast that item was removed so AddToCart component can update
            $this->dispatch('cartItemRemoved', inventoryId: $inventoryId)->to('cart.add-to-cart');
            
            // Notify parent component to refresh cart
            $this->dispatch('cartItemRemoved');
            
            // Dispatch notification
            $this->dispatch('notification', type: 'warning', message: 'Item removed from cart');
        } else {
            // Notify parent component to refresh cart
            $this->dispatch('cartItemUpdated');
        }
        
        // Update cart count
        $this->dispatch('cart-updated');
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
        
        // Use CartService to remove the item
        $cartService = app(CartService::class);
        $result = $cartService->removeFromCart($inventoryId);
        
        if (!$result['success']) {
            $this->dispatch('notification', type: 'error', message: $result['message']);
            return;
        }
        
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