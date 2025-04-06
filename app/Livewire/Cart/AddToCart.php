<?php

namespace App\Livewire\Cart;

use App\Models\Cart;
use App\Models\Inventory;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

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

    #[On('add-to-cart-increment')]
    public function handleIncrement($id, $change)
    {
        // If this component is for a different product, ignore the event
        if ($this->inventoryId !== $id) {
            return;
        }

        if ($change > 0) {
            $this->incrementQuantity();
        } else {
            $this->decrementQuantity();
        }
    }

    #[On('add-to-cart-quantity')]
    public function handleSetQuantity($id, $quantity)
    {
        // If this component is for a different product, ignore the event
        if ($this->inventoryId !== $id) {
            return;
        }

        // Handle empty or non-numeric values
        $this->quantity = empty($quantity) || ! is_numeric($quantity) ? 0 : (int) $quantity;
        $this->updatedQuantity();
    }

    public function mount(int $inventoryId, bool $showQuantity = true, string $quantityInputType = 'stepper', int $maxQuantity = 99, string $variant = 'default')
    {
        $this->inventoryId = $inventoryId;
        $this->showQuantity = $showQuantity;
        $this->quantityInputType = $quantityInputType;
        $this->maxQuantity = min($maxQuantity, 99); // Ensure max is never greater than 99
        $this->variant = $variant;

        $this->refreshCartData();
    }

    /**
     * Load the latest cart data for this inventory item
     */
    private function refreshCartData()
    {
        $cartItems = app(CartService::class)->getCartItems();

        if (isset($cartItems[$this->inventoryId])) {
            $this->isInCart = true;
            $this->quantity = $cartItems[$this->inventoryId]['quantity'];
        } else {
            $this->isInCart = false;
            $this->quantity = 0;
        }
    }

    public function addToCart()
    {
        // Check if user has permission to add to cart
        if (Auth::check() && ! Auth::user()->can('add to cart')) {
            // No notification, just don't proceed
            return;
        }

        // If not logged in and attempted to add to cart, redirect to login
        if (! Auth::check() && $this->quantity > 0) {
            return redirect()->route('login');
        }

        // Use CartService to handle cart operations
        $cartService = app(CartService::class);
        $result = $cartService->addToCart($this->inventoryId, $this->quantity, true);

        if (! $result['success']) {
            // Notification removed
            return;
        }

        // Update UI state based on result
        if ($result['action'] === 'removed') {
            $this->isInCart = false;
            // Notification removed
        } elseif ($result['action'] === 'added') {
            $this->isInCart = true;
            // Notification removed
        } elseif ($result['action'] === 'updated') {
            $this->isInCart = true;
            // No notification for updates to reduce noise
        }

        // Emit events to update cart UI components
        $this->dispatch('cart-updated');
        $this->dispatch('quantity-updated', ['id' => $this->inventoryId, 'quantity' => $this->quantity]);
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
        // Validate quantity input is a number and not empty
        if (empty($this->quantity) || ! is_numeric($this->quantity)) {
            $this->quantity = 0;

            return;
        }

        // Convert to integer
        $this->quantity = (int) $this->quantity;

        // Validate quantity input (allow 0)
        if ($this->quantity < 0) {
            $this->quantity = 0;
        }

        // Check if quantity exceeds the maximum (99)
        if ($this->quantity > 99) {
            // Reset to 99
            $this->quantity = 99;

            // Notification removed
            return;
        }

        // Cap at configured max quantity (which is already limited to 99 in mount)
        if ($this->quantity > $this->maxQuantity) {
            $this->quantity = $this->maxQuantity;
        }

        // Auto-update cart when quantity changes via input
        $this->addToCart();
    }

    #[On('cartItemRemoved')]
    public function resetCartStatus(int $inventoryId)
    {
        if ($this->inventoryId === $inventoryId) {
            $this->refreshCartData();
        }
    }

    // Method to handle updates from Alpine.js
    public function updateQuantity($newQuantity)
    {
        $this->quantity = (int) $newQuantity;
        $this->addToCart();
    }

    #[On('products-loaded')]
    public function refreshState()
    {
        $this->refreshCartData();
    }

    public function render()
    {
        return view('livewire.cart.add-to-cart');
    }
}
