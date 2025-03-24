<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Get all cart items for the current user
     *
     * @return array
     */
    public function getCartItems()
    {
        if (Auth::check()) {
            // Logged in user - get from database cart
            $cart = Auth::user()->getOrCreateCart();
            $cartItems = $cart->items()->with('inventory')->get();
            
            // Transform into array format
            return $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'inventory_id' => $item->inventory_id,
                    'inventory' => $item->inventory,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ];
            })->keyBy('inventory_id')->toArray();
        } else {
            // Guest user - get from session
            return Session::get('cart', []);
        }
    }

    /**
     * Add an item to the cart
     *
     * @param int $inventoryId
     * @param int $quantity
     * @param bool $replace Whether to replace existing quantity or add to it
     * @return array
     */
    public function addToCart($inventoryId, $quantity, $replace = true)
    {
        $inventory = Inventory::find($inventoryId);
        if (!$inventory) {
            return [
                'success' => false,
                'message' => 'Product not found'
            ];
        }
        
        // Validate quantity
        $quantity = (int)$quantity;
        if ($quantity < 0) {
            $quantity = 0;
        }
        
        if ($quantity > 99) {
            $quantity = 99;
        }
        
        if (Auth::check()) {
            // Database cart for authenticated users
            return $this->addToUserCart(Auth::user(), $inventory, $quantity, $replace);
        } else {
            // Session cart for guest users
            return $this->addToSessionCart($inventory, $quantity, $replace);
        }
    }

    /**
     * Add item to an authenticated user's cart in the database
     */
    private function addToUserCart(User $user, Inventory $inventory, int $quantity, bool $replace = true)
    {
        $inventoryId = $inventory->id;
        
        try {
            DB::beginTransaction();
            
            $cart = $user->getOrCreateCart();
            $cartItem = $cart->items()->where('inventory_id', $inventoryId)->first();
            
            if ($cartItem) {
                if ($quantity === 0) {
                    // Remove item if quantity is 0
                    $cartItem->delete();
                    
                    DB::commit();
                    return [
                        'success' => true,
                        'message' => 'Item removed from cart',
                        'action' => 'removed'
                    ];
                } else {
                    // Update quantity
                    if ($replace) {
                        $newQuantity = $quantity;
                    } else {
                        $newQuantity = $cartItem->quantity + $quantity;
                        
                        // Cap at 99
                        if ($newQuantity > 99) {
                            $newQuantity = 99;
                        }
                    }
                    
                    $cartItem->update([
                        'quantity' => $newQuantity
                    ]);
                    
                    DB::commit();
                    return [
                        'success' => true,
                        'message' => 'Cart updated',
                        'action' => 'updated',
                        'quantity' => $newQuantity
                    ];
                }
            } else {
                // Don't add if quantity is 0
                if ($quantity === 0) {
                    DB::commit();
                    return [
                        'success' => true,
                        'message' => 'No changes made',
                        'action' => 'none'
                    ];
                }
                
                // Determine price based on user's state permissions
                $priceField = $user->price_field;
                $price = $inventory->$priceField;
                
                if (!$price) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Price not available for this item',
                    ];
                }
                
                // Create new cart item
                $cartItem = $cart->items()->create([
                    'inventory_id' => $inventoryId,
                    'quantity' => $quantity,
                    'price' => $price,
                ]);
                
                DB::commit();
                return [
                    'success' => true,
                    'message' => 'Item added to cart',
                    'action' => 'added',
                    'quantity' => $quantity
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update cart: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to update cart'
            ];
        }
    }
    
    /**
     * Add item to session cart for guest users
     */
    private function addToSessionCart(Inventory $inventory, int $quantity, bool $replace = true)
    {
        $inventoryId = $inventory->id;
        $sessionCart = Session::get('cart', []);
        
        if (isset($sessionCart[$inventoryId])) {
            if ($quantity === 0) {
                // Remove item if quantity is 0
                unset($sessionCart[$inventoryId]);
                Session::put('cart', $sessionCart);
                
                return [
                    'success' => true,
                    'message' => 'Item removed from cart',
                    'action' => 'removed'
                ];
            } else {
                // Update quantity
                if ($replace) {
                    $sessionCart[$inventoryId]['quantity'] = $quantity;
                } else {
                    $sessionCart[$inventoryId]['quantity'] += $quantity;
                    
                    // Cap at 99
                    if ($sessionCart[$inventoryId]['quantity'] > 99) {
                        $sessionCart[$inventoryId]['quantity'] = 99;
                    }
                }
                
                Session::put('cart', $sessionCart);
                
                return [
                    'success' => true,
                    'message' => 'Cart updated',
                    'action' => 'updated',
                    'quantity' => $sessionCart[$inventoryId]['quantity']
                ];
            }
        } else {
            // Don't add if quantity is 0
            if ($quantity === 0) {
                return [
                    'success' => true,
                    'message' => 'No changes made',
                    'action' => 'none'
                ];
            }
            
            // For guests, use standard price
            $price = $inventory->price;
            
            // Add new item
            $sessionCart[$inventoryId] = [
                'inventory_id' => $inventoryId,
                'inventory' => $inventory,
                'quantity' => $quantity,
                'price' => $price
            ];
            
            Session::put('cart', $sessionCart);
            
            return [
                'success' => true,
                'message' => 'Item added to cart',
                'action' => 'added',
                'quantity' => $quantity
            ];
        }
    }

    /**
     * Remove an item from the cart
     *
     * @param int $inventoryId
     * @return array
     */
    public function removeFromCart($inventoryId)
    {
        if (Auth::check()) {
            // Database cart for authenticated users
            try {
                $user = Auth::user();
                $cart = $user->getOrCreateCart();
                $cartItem = $cart->items()->where('inventory_id', $inventoryId)->first();
                
                if ($cartItem) {
                    $cartItem->delete();
                    
                    return [
                        'success' => true,
                        'message' => 'Item removed from cart',
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => 'Item not found in cart',
                ];
            } catch (\Exception $e) {
                Log::error('Failed to remove cart item: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Failed to remove item from cart',
                ];
            }
        } else {
            // Session cart for guest users
            $sessionCart = Session::get('cart', []);
            
            if (isset($sessionCart[$inventoryId])) {
                unset($sessionCart[$inventoryId]);
                Session::put('cart', $sessionCart);
                
                return [
                    'success' => true,
                    'message' => 'Item removed from cart',
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Item not found in cart',
            ];
        }
    }

    /**
     * Get total number of items in cart
     *
     * @return int
     */
    public function getCartCount()
    {
        $cartItems = $this->getCartItems();
        
        $count = 0;
        foreach ($cartItems as $item) {
            $count += (int)$item['quantity'];
        }
        
        return $count;
    }

    /**
     * Get cart total price
     *
     * @return float
     */
    public function getCartTotal()
    {
        $cartItems = $this->getCartItems();
        
        $total = 0;
        foreach ($cartItems as $item) {
            $total += (float)$item['price'] * (int)$item['quantity'];
        }
        
        return $total;
    }

    /**
     * Sync guest cart to user cart on login
     *
     * @param User $user
     * @return void
     */
    public function syncFromSessionToUser(User $user)
    {
        $sessionCart = Session::get('cart', []);
        if (empty($sessionCart)) {
            return;
        }
        
        try {
            DB::beginTransaction();
            
            $cart = $user->getOrCreateCart();
            
            foreach ($sessionCart as $inventoryId => $item) {
                $inventory = Inventory::find($inventoryId);
                if (!$inventory) {
                    continue;
                }
                
                // Determine price based on user's state permissions
                $priceField = $user->price_field;
                $price = $inventory->$priceField;
                
                if (!$price) {
                    continue;
                }
                
                $quantity = (int)$item['quantity'];
                
                // Apply quantity limits
                if ($quantity > 99) {
                    $quantity = 99;
                }
                
                // Find existing cart item
                $cartItem = $cart->items()->where('inventory_id', $inventoryId)->first();
                
                if ($cartItem) {
                    // Update existing item with higher quantity
                    $cartItem->update([
                        'quantity' => max($cartItem->quantity, $quantity),
                        'price' => $price,
                    ]);
                } else {
                    // Create new cart item
                    $cart->items()->create([
                        'inventory_id' => $inventoryId,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);
                }
            }
            
            // Clear session cart
            Session::forget('cart');
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sync cart: ' . $e->getMessage());
        }
    }
}