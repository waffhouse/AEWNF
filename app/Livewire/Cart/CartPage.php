<?php

namespace App\Livewire\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

class CartPage extends Component
{
    public $cart;
    public $cartItems = [];
    public $notes = '';
    
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
    
    public function checkout()
    {
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
            DB::beginTransaction();
            
            // Create a new order
            $order = Order::create([
                'user_id' => Auth::id(),
                'total' => $this->cart->getTotal(),
                'status' => Order::STATUS_PENDING,
                'notes' => $this->notes,
            ]);
            
            // Add cart items to order
            foreach ($this->cartItems as $cartItem) {
                $inventory = $cartItem->inventory;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'inventory_id' => $cartItem->inventory_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'product_name' => $inventory->description,
                    'product_sku' => $inventory->sku,
                ]);
            }
            
            // Clear the cart
            $this->cart->items()->delete();
            
            DB::commit();
            
            // Update cart count
            $this->dispatch('cart-updated');
            
            $this->dispatch('notification', [
                'type' => 'success',
                'message' => 'Order placed successfully'
            ]);
            
            // Reset form 
            $this->notes = '';
            
            // Redirect to order details page with success message
            return redirect()->route('customer.order.details', ['order' => $order->id])
                ->with('message', 'Order placed successfully! Your order #' . $order->id . ' has been received.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
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