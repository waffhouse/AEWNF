<?php

namespace App\Livewire\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

class CartPage extends Component
{
    public $cart;
    public $cartItems = [];
    public $notes = '';
    public $viewingOrderDetails = false;
    public $selectedOrder = null;
    
    protected OrderService $orderService;
    
    public function boot(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    
    protected function getListeners()
    {
        return [
            'initiateCheckout' => 'checkout',
            'cartItemUpdated' => 'refreshCart',
            'cartItemRemoved' => 'refreshCart'
        ];
    }
    
    public function mount()
    {
        // Check if user is authenticated and has permission to add to cart
        if (!Auth::check() || !Auth::user()->can('add to cart')) {
            return redirect()->route('login');
        }
        
        $this->refreshCart();
    }
    
    public function refreshCart()
    {
        $cartService = app(CartService::class);
        $cartItems = $cartService->getCartItems();
        
        // Transform the cart items into a collection-like format for the view
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
        
        $this->cartItems = collect($transformedItems);
    }
    
    public function updateQuantity($itemId, $quantity)
    {
        // Get inventory ID from either a database ID or direct inventory ID
        $inventoryId = null;
        
        // Find the item in local cart items
        foreach ($this->cartItems as $item) {
            if ($item->id == $itemId || (is_null($item->id) && $item->inventory_id == $itemId)) {
                $inventoryId = $item->inventory_id;
                break;
            }
        }
        
        if (!$inventoryId) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Item not found'
            ]);
            return;
        }
        
        // Validate quantity
        if ($quantity < 1) {
            $quantity = 1;
        }
        
        // Use cart service to update cart
        $cartService = app(CartService::class);
        $result = $cartService->addToCart($inventoryId, $quantity, true);
        
        if ($result['success']) {
            // Refresh local cart items to update the UI
            $this->refreshCart();
            
            // Dispatch cart-updated event to update counter
            $this->dispatch('cart-updated');
        } else {
            // Handle error case
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => $result['message'] ?? 'Failed to update cart'
            ]);
        }
    }
    
    public function removeItem($itemId)
    {
        // Get inventory ID from either a database ID or direct inventory ID
        $inventoryId = null;
        
        // Find the item in local cart items
        foreach ($this->cartItems as $item) {
            if ($item->id == $itemId || (is_null($item->id) && $item->inventory_id == $itemId)) {
                $inventoryId = $item->inventory_id;
                break;
            }
        }
        
        if (!$inventoryId) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Item not found'
            ]);
            return;
        }
        
        // Use cart service to remove from cart
        $cartService = app(CartService::class);
        $result = $cartService->removeFromCart($inventoryId);
        
        if ($result['success']) {
            // Broadcast that item was removed so AddToCart component can update
            $this->dispatch('cartItemRemoved', inventoryId: $inventoryId)->to('cart.add-to-cart');
            
            // Refresh cart and update cart count
            $this->refreshCart();
            $this->dispatch('cart-updated');
            
            $this->dispatch('notification', [
                'type' => 'warning',
                'message' => 'Item removed from cart'
            ]);
        } else {
            // Handle error case
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => $result['message'] ?? 'Failed to remove item'
            ]);
        }
    }
    
    public function clearCart()
    {
        // Force sync to database first
        $cartService = app(CartService::class);
        $cartService->forceDatabaseSync();
        
        // Get cart from database to clear it
        $cart = Auth::user()->getOrCreateCart();
        
        if ($cart) {
            $cart->items()->delete();
            
            // Clear local cache
            $cartService = app(CartService::class);
            foreach ($this->cartItems as $item) {
                $cartService->removeFromCart($item->inventory_id);
            }
            
            $this->refreshCart();
            $this->dispatch('cart-updated');
            
            $this->dispatch('notification', [
                'type' => 'warning',
                'message' => 'Cart cleared'
            ]);
        }
    }
    
    public function viewOrderDetails($orderId)
    {
        $this->selectedOrder = Order::with(['items.inventory', 'user'])
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);
        $this->viewingOrderDetails = true;
    }
    
    public function closeOrderDetails()
    {
        // Remove the body lock through inline JavaScript for immediate effect
        $this->js('document.body.classList.remove("overflow-hidden")');
        
        $this->viewingOrderDetails = false;
        $this->selectedOrder = null;
    }
    
    public function checkout($data = [])
    {
        // Extract notes from event data if available
        $notes = isset($data['notes']) ? $data['notes'] : $this->notes;
        
        // Check if the user has permission to place orders
        if (!Auth::user()->can('place orders')) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'You do not have permission to place orders'
            ]);
            return;
        }
        
        // Check if cart is empty
        if ($this->cartItems->isEmpty()) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Your cart is empty'
            ]);
            return;
        }
        
        try {
            // Use OrderService to create the order
            $order = $this->orderService->createOrder(Auth::user(), $notes);
            
            // Refresh the cart to show it's now empty
            $this->refreshCart();
            
            // Update cart count for other components
            $this->dispatch('cart-updated');
            
            $this->dispatch('notification', [
                'type' => 'success',
                'message' => 'Order placed successfully'
            ]);
            
            // Reset form 
            $this->notes = '';
            
            // Show order details directly in a modal - need to specify the component name
            $this->dispatch('showOrderDetails', ['orderId' => $order->id])->to('cart.order-confirmation');
            
        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'type' => 'error',
                'message' => 'Error placing order: ' . $e->getMessage()
            ]);
        }
    }
    
    #[Title('Shopping Cart')]
    public function render()
    {
        $cartService = app(CartService::class);
        
        return view('livewire.cart.cart-page', [
            'total' => $cartService->getCartTotal(),
            'itemCount' => $cartService->getCartCount(),
        ])->layout('layouts.app');
    }
}