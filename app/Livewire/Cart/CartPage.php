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
    public $deliveryType = 'pickup';
    
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
            'cartItemRemoved' => 'refreshCart',
            'refresh' => 'refresh'
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
        // Get cart items directly from database
        $user = auth()->user();
        if (!$user) {
            $this->cartItems = collect([]);
            return;
        }
        
        $cart = $user->getOrCreateCart();
        $this->cartItems = $cart->items()->with('inventory')->get();
    }
    
    public function updateQuantity($itemId, $quantity)
    {
        // Find the cart item directly
        $cartItem = CartItem::find($itemId);
        
        if (!$cartItem) {
            // Notification removed
            return;
        }
        
        // Validate quantity
        $quantity = (int)$quantity;
        if ($quantity < 1) {
            $quantity = 1;
        }
        
        if ($quantity > 99) {
            $quantity = 99;
        }
        
        // Update the cart item
        $cartItem->update([
            'quantity' => $quantity
        ]);
        
        // Refresh cart items to update the UI
        $this->refreshCart();
        
        // Dispatch cart-updated event to update counter
        $this->dispatch('cart-updated');
    }
    
    public function removeItem($itemId)
    {
        // Find the cart item directly
        $cartItem = CartItem::find($itemId);
        
        if (!$cartItem) {
            // Notification removed
            return;
        }
        
        $inventoryId = $cartItem->inventory_id;
        
        // Delete the cart item
        $cartItem->delete();
        
        // Broadcast that item was removed so AddToCart component can update
        $this->dispatch('cartItemRemoved', inventoryId: $inventoryId)->to('cart.add-to-cart');
        
        // Refresh cart and update cart count
        $this->refreshCart();
        $this->dispatch('cart-updated');
        
        // Notification removed
    }
    
    public function clearCart()
    {
        // Get cart from database to clear it
        $cart = Auth::user()->getOrCreateCart();
        
        if ($cart) {
            $cart->items()->delete();
            
            // Rather than trying to update component state through events,
            // use the redirect approach to fully refresh the page
            // Session notification removed
            
            // This will do a full page refresh, avoiding the component errors
            return redirect()->route('customer.cart');
        }
    }
    
    public function refresh()
    {
        $this->refreshCart();
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
            // Notification removed
            return;
        }
        
        // Check if cart is empty
        if ($this->cartItems->isEmpty()) {
            // Notification removed
            return;
        }
        
        try {
            // Use OrderService to create the order
            $order = $this->orderService->createOrder(Auth::user(), $notes, $this->deliveryType);
            
            // Refresh the cart to show it's now empty
            $this->refreshCart();
            
            // Update cart count for other components
            $this->dispatch('cart-updated');
            
            // Notification removed
            
            // Reset form 
            $this->notes = '';
            
            // Show order details directly in a modal - need to specify the component name
            $this->dispatch('showOrderDetails', ['orderId' => $order->id])->to('cart.order-confirmation');
            
        } catch (\Exception $e) {
            // Notification removed
        }
    }
    
    #[Title('Shopping Cart')]
    public function render()
    {
        // Calculate totals directly
        $total = 0;
        $itemCount = 0;
        
        foreach ($this->cartItems as $item) {
            $total += $item->price * $item->quantity;
            $itemCount += $item->quantity;
        }
        
        return view('livewire.cart.cart-page', [
            'total' => $total,
            'itemCount' => $itemCount,
        ])->layout('layouts.app');
    }
}