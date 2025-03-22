<?php

namespace App\Livewire\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
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
        $this->cart = Auth::user()->getOrCreateCart();
        $this->cartItems = $this->cart->items()->with('inventory')->get();
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
        
        $this->dispatch('notification', [
            'type' => 'success',
            'message' => 'Cart updated'
        ]);
        
        $this->refreshCart();
        
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
        
        // Broadcast that item was removed so AddToCart component can update
        $this->dispatch('cartItemRemoved', inventoryId: $inventoryId)->to('cart.add-to-cart');
        
        // Refresh cart and update cart count
        $this->refreshCart();
        $this->dispatch('cart-updated');
        
        $this->dispatch('notification', [
            'type' => 'success',
            'message' => 'Item removed from cart'
        ]);
    }
    
    public function clearCart()
    {
        $cart = Auth::user()->cart;
        
        if ($cart) {
            $cart->items()->delete();
            
            $this->refreshCart();
            $this->dispatch('cart-updated');
            
            $this->dispatch('notification', [
                'type' => 'success',
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
        return view('livewire.cart.cart-page', [
            'total' => $this->cart ? $this->cart->getTotal() : 0,
            'itemCount' => $this->cart ? $this->cart->getTotalItems() : 0,
        ])->layout('layouts.app');
    }
}