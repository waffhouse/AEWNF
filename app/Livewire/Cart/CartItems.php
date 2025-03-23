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
        'refreshCart' => 'refreshCartItems'
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
            $this->cartItems = $cartItems;
        } else {
            $cartService = app(CartService::class);
            $cartItems = $cartService->getCartItems();
            
            // Transform associative array to collection-like array for blade
            $transformedItems = [];
            foreach ($cartItems as $item) {
                $transformedItems[] = (object)[
                    'id' => $item['id'] ?? null,
                    'inventory_id' => $item['inventory_id'],
                    'inventory' => $item['inventory'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ];
            }
            
            $this->cartItems = $transformedItems;
        }
    }
    
    public function updateQuantity($itemId, $quantity)
    {
        // Get the inventory ID
        $inventoryId = null;
        
        // Find the item by ID
        foreach ($this->cartItems as $item) {
            if ($item->id == $itemId || (is_null($item->id) && $item->inventory_id == $itemId)) {
                $inventoryId = $item->inventory_id;
                break;
            }
        }
        
        if (!$inventoryId) {
            $this->dispatch('notification', type: 'error', message: 'Item not found');
            return;
        }
        
        // Validate quantity is a number
        if (!is_numeric($quantity)) {
            $quantity = 1;
        }
        
        // Convert to integer
        $quantity = (int)$quantity;
        
        // Use cart service to update cart
        $cartService = app(CartService::class);
        $result = $cartService->addToCart($inventoryId, $quantity, true);
        
        if ($result['success']) {
            // Refresh local cart items to update the UI
            $this->refreshCartItems();
            
            if ($result['action'] === 'removed') {
                // Broadcast that item was removed so AddToCart component can update
                $this->dispatch('cartItemRemoved', inventoryId: $inventoryId)->to('cart.add-to-cart');
                
                // Notify parent component to refresh cart
                $this->dispatch('cartItemRemoved');
                
                // Update cart count
                $this->dispatch('cart-updated');
                
                $this->dispatch('notification', type: 'warning', message: 'Item removed from cart');
            } else {
                // Notify parent component to refresh cart
                $this->dispatch('cartItemUpdated');
                
                // Dispatch cart-updated event to update counter
                $this->dispatch('cart-updated');
                
                // No notification needed for normal quantity updates
            }
        } else {
            // Handle error case
            $this->dispatch('notification', type: 'error', message: $result['message'] ?? 'Failed to update cart');
        }
    }
    
    public function removeItem($itemId)
    {
        // Get the inventory ID
        $inventoryId = null;
        
        // Find the item by ID
        foreach ($this->cartItems as $item) {
            if ($item->id == $itemId || (is_null($item->id) && $item->inventory_id == $itemId)) {
                $inventoryId = $item->inventory_id;
                break;
            }
        }
        
        if (!$inventoryId) {
            $this->dispatch('notification', type: 'error', message: 'Item not found');
            return;
        }
        
        // Use cart service to remove from cart
        $cartService = app(CartService::class);
        $result = $cartService->removeFromCart($inventoryId);
        
        if ($result['success']) {
            // Refresh local cart items to update the UI
            $this->refreshCartItems();
            
            // Broadcast that item was removed so AddToCart component can update
            $this->dispatch('cartItemRemoved', inventoryId: $inventoryId)->to('cart.add-to-cart');
            
            // Notify parent component to refresh cart
            $this->dispatch('cartItemRemoved');
            
            // Update cart count
            $this->dispatch('cart-updated');
            
            $this->dispatch('notification', type: 'warning', message: 'Item removed from cart');
        } else {
            // Handle error case
            $this->dispatch('notification', type: 'error', message: $result['message'] ?? 'Failed to remove item');
        }
    }
    
    public function render()
    {
        return view('livewire.cart.cart-items');
    }
}