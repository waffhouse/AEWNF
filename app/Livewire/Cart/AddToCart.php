<?php

namespace App\Livewire\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class AddToCart extends Component
{
    public int $inventoryId;
    public int $quantity = 1;
    public bool $isInCart = false;
    public bool $showQuantity = true;
    public string $quantityInputType = 'stepper'; // 'stepper' or 'input'
    public int $maxQuantity = 100; // Default max quantity
    
    public function mount(int $inventoryId, bool $showQuantity = true, string $quantityInputType = 'stepper', int $maxQuantity = 100)
    {
        $this->inventoryId = $inventoryId;
        $this->showQuantity = $showQuantity;
        $this->quantityInputType = $quantityInputType;
        $this->maxQuantity = $maxQuantity;
        
        // Check if this item is already in the user's cart
        if (Auth::check()) {
            $cart = Auth::user()->cart;
            if ($cart) {
                $cartItem = $cart->items()->where('inventory_id', $this->inventoryId)->first();
                if ($cartItem) {
                    $this->isInCart = true;
                    $this->quantity = $cartItem->quantity;
                }
            }
        }
    }
    
    public function addToCart()
    {
        // Check if user has permission to add to cart
        if (!Auth::check() || !Auth::user()->can('add to cart')) {
            // Redirect to login if not authenticated
            if (!Auth::check()) {
                return redirect()->route('login');
            }
            
            // Return error message if authenticated but no permission
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'You do not have permission to add items to cart'
            ]);
            return;
        }
        
        // Get the inventory item
        $inventoryItem = Inventory::findOrFail($this->inventoryId);
        
        // Get user's cart or create one
        $user = Auth::user();
        $cart = $user->getOrCreateCart();
        
        // Determine price based on user's state permissions
        $priceField = $user->price_field;
        $price = $inventoryItem->$priceField;
        
        // Check if price is available
        if (!$price) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'This item is not available for purchase in your region'
            ]);
            return;
        }
        
        // Check if item already exists in cart
        $cartItem = $cart->items()->where('inventory_id', $this->inventoryId)->first();
        
        if ($cartItem) {
            // Update quantity if item exists (replacing the quantity, not adding to it)
            $cartItem->update([
                'quantity' => $this->quantity,
                'price' => $price, // Update price in case it changed
            ]);
            
            $successMessage = 'Cart updated';
        } else {
            // Create new cart item
            $cart->items()->create([
                'inventory_id' => $this->inventoryId,
                'quantity' => $this->quantity,
                'price' => $price,
            ]);
            
            $successMessage = 'Item added to cart';
        }
        
        $this->isInCart = true;
        
        // Emit event to update cart count in navbar
        $this->dispatch('cart-updated');
        
        // Show success notification
        $this->dispatch('notification', [
            'type' => 'success',
            'message' => $successMessage
        ]);
    }
    
    public function incrementQuantity()
    {
        // Increment quantity, respecting max limit
        if ($this->quantity < $this->maxQuantity) {
            $this->quantity++;
        }
    }
    
    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }
    
    public function updatedQuantity()
    {
        // Validate quantity input
        if ($this->quantity < 1) {
            $this->quantity = 1;
        }
        
        // Cap at max quantity
        if ($this->quantity > $this->maxQuantity) {
            $this->quantity = $this->maxQuantity;
        }
    }
    
    #[On('cartItemRemoved')]
    public function resetCartStatus(int $inventoryId)
    {
        if ($this->inventoryId === $inventoryId) {
            $this->isInCart = false;
        }
    }
    
    public function render()
    {
        return view('livewire.cart.add-to-cart');
    }
}
