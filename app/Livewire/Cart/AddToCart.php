<?php

namespace App\Livewire\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Services\CartService;
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
        
        // Check if this item is already in local cart
        $cartService = app(CartService::class);
        $cartItems = $cartService->getCartItems();
        
        if (isset($cartItems[$inventoryId])) {
            $this->isInCart = true;
            $this->quantity = $cartItems[$inventoryId]['quantity'];
        }
    }
    
    public function addToCart()
    {
        // Check if user has permission to add to cart
        if (Auth::check() && !Auth::user()->can('add to cart')) {
            // No notification, just don't proceed
            return;
        }
        
        // Use CartService for immediate local response
        $cartService = app(CartService::class);
        
        // Add to cart using service
        $result = $cartService->addToCart($this->inventoryId, $this->quantity, true);
        
        // Set local state based on result
        if ($result['success']) {
            if ($result['action'] === 'removed') {
                $this->isInCart = false;
                $this->quantity = 0;
                
                // Send notifications
                $this->dispatch('notification', type: 'warning', message: 'Item removed from cart');
            } else if ($result['action'] === 'updated' || $result['action'] === 'added') {
                $this->isInCart = true;
                
                // Show notification for new items being added
                if ($result['action'] === 'added') {
                    $this->dispatch('notification', type: 'success', message: 'Item added to cart');
                }
            }
            
            // Emit events to update cart UI components
            $this->dispatch('cart-updated');
            $this->dispatch('cart-status-changed'); // For badge updates
            
            // Update UI with JavaScript for immediate feedback
            $this->js("
                document.dispatchEvent(new CustomEvent('product-cart-updated', {
                    detail: {
                        inventoryId: {$this->inventoryId},
                        isInCart: " . ($this->isInCart ? 'true' : 'false') . ",
                        quantity: {$this->quantity}
                    }
                }));
            ");
        } else {
            // Handle error case
            if (isset($result['message'])) {
                $this->dispatch('notification', type: 'error', message: $result['message']);
            }
        }
        
        // If not logged in and attempted to add to cart, redirect to login
        if (!Auth::check() && $this->quantity > 0) {
            return redirect()->route('login');
        }
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
        
        // We're now using Alpine.js's x-model for the input value and explicitly updating via the update button
        // The quantity value will be set by Alpine.js before calling addToCart
    }
    
    #[On('cartItemRemoved')]
    public function resetCartStatus(int $inventoryId)
    {
        if ($this->inventoryId === $inventoryId) {
            $this->isInCart = false;
            $this->quantity = 0;
        }
    }
    
    // Method to handle updates from Alpine.js
    public function updateQuantity($newQuantity)
    {
        $this->quantity = (int)$newQuantity;
        $this->addToCart();
    }
    
    #[On('products-loaded')]
    public function refreshState()
    {
        // Check if this item is already in the local cart
        $cartService = app(CartService::class);
        $cartItems = $cartService->getCartItems();
        
        if (isset($cartItems[$this->inventoryId])) {
            $this->isInCart = true;
            $this->quantity = $cartItems[$this->inventoryId]['quantity'];
        } else {
            $this->isInCart = false;
            $this->quantity = 0;
        }
    }
    
    public function render()
    {
        return view('livewire.cart.add-to-cart');
    }
}
