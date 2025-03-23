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
    public int $quantity = 0;
    public bool $isInCart = false;
    public bool $showQuantity = true;
    public string $quantityInputType = 'stepper'; // 'stepper' or 'input'
    public int $maxQuantity = 100; // Default max quantity
    public string $variant = 'default'; // 'default' or 'compact'
    
    #[On('add-to-cart-quick')]
    public function quickAdd($id, $quantity = 1)
    {
        // If this component is for a different product, ignore the event
        if ($this->inventoryId !== $id) {
            return;
        }
        
        // Set quantity and add to cart
        $this->quantity = $quantity;
        $this->addToCart();
    }
    
    public function mount(int $inventoryId, bool $showQuantity = true, string $quantityInputType = 'stepper', int $maxQuantity = 99, string $variant = 'default')
    {
        $this->inventoryId = $inventoryId;
        $this->showQuantity = $showQuantity;
        $this->quantityInputType = $quantityInputType;
        $this->maxQuantity = min($maxQuantity, 99); // Ensure max is never greater than 99
        $this->variant = $variant;
        
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
            
            // No notification, just don't proceed
            return;
        }
        
        // Get the inventory item
        $inventoryItem = Inventory::findOrFail($this->inventoryId);
        
        // Get user's cart or create one
        $user = Auth::user();
        $cart = $user->getOrCreateCart();
        
        // Check if item already exists in cart
        $cartItem = $cart->items()->where('inventory_id', $this->inventoryId)->first();
        
        // If quantity is 0, remove the item from cart if it exists
        if ($this->quantity <= 0) {
            if ($cartItem) {
                $cartItem->delete();
                $this->isInCart = false;
                
                // Emit events to update cart UI components
                $this->dispatch('cart-updated');
                $this->dispatch('cartItemRemoved', inventoryId: $this->inventoryId);
                $this->dispatch('cart-status-changed'); // For badge updates
                
                // We'll use JavaScript to trigger a custom event for the cart indicators
                $this->js("
                    document.dispatchEvent(new CustomEvent('product-cart-updated', {
                        detail: {
                            inventoryId: {$this->inventoryId},
                            isInCart: false
                        }
                    }));
                ");
                
                $this->dispatch('notification', type: 'success', message: 'Item removed from cart');
            }
            return;
        }
        
        // Check for quantity limit first
        if ($this->quantity > 99) {
            $this->quantity = 99;
            
            // This is the only notification we want to keep
            $this->dispatch('notification', type: 'warning', message: 'For orders of 100+ items, please contact our office directly.');
            return;
        }
        
        // Determine price based on user's state permissions
        $priceField = $user->price_field;
        $price = $inventoryItem->$priceField;
        
        // Check if price is available
        if (!$price) {
            // No notification for unavailable items
            return;
        }
        
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
        
        // Emit events to update cart UI components
        $this->dispatch('cart-updated');
        $this->dispatch('cart-status-changed'); // For badge updates
        
        // We'll use JavaScript to trigger a custom event for the cart indicators
        $this->js("
            document.dispatchEvent(new CustomEvent('product-cart-updated', {
                detail: {
                    inventoryId: {$this->inventoryId},
                    isInCart: true
                }
            }));
        ");
        
        // Add success notification
        $this->dispatch('notification', type: 'success', message: $successMessage);
    }
    
    public function incrementQuantity()
    {
        // Increment quantity, respecting max limit
        if ($this->quantity < $this->maxQuantity) {
            $this->quantity++;
            // Auto-update cart when incrementing
            $this->addToCart();
        }
    }
    
    public function decrementQuantity()
    {
        if ($this->quantity > 0) {
            $this->quantity--;
            // Auto-update cart when decrementing (including removal when quantity becomes 0)
            $this->addToCart();
        }
    }
    
    public function updatedQuantity()
    {
        // Validate quantity input is a number
        if (!is_numeric($this->quantity)) {
            $this->quantity = 0;
            return;
        }
        
        // Convert to integer
        $this->quantity = (int)$this->quantity;
        
        // Validate quantity input (allow 0)
        if ($this->quantity < 0) {
            $this->quantity = 0;
        }
        
        // Check if quantity exceeds the maximum (99)
        if ($this->quantity > 99) {
            // Reset to 99
            $this->quantity = 99;
            
            // This is the only notification we want to keep
            $this->dispatch('notification', type: 'warning', message: 'For orders of 100+ items, please contact our office directly.');
            return;
        }
        
        // Cap at configured max quantity (which is already limited to 99 in mount)
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
    
    #[On('products-loaded')]
    public function refreshState()
    {
        // Check if this item is already in the user's cart
        if (Auth::check()) {
            $cart = Auth::user()->cart;
            if ($cart) {
                $cartItem = $cart->items()->where('inventory_id', $this->inventoryId)->first();
                if ($cartItem) {
                    $this->isInCart = true;
                    $this->quantity = $cartItem->quantity;
                    
                    // We used to emit cart status events here, but we've removed the cart indicator functionality
                } else {
                    // The item is not in the cart
                }
            }
        }
    }
    
    public function render()
    {
        return view('livewire.cart.add-to-cart');
    }
}
