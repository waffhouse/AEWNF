<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected $localCart = [];
    protected $syncInProgress = false;
    protected $syncInterval = 5; // seconds

    /**
     * Get all cart items for the current user
     *
     * @return array
     */
    public function getCartItems()
    {
        // If local cart is empty, load from database or session
        if (empty($this->localCart)) {
            $this->loadLocalCart();
        }

        return $this->localCart;
    }

    /**
     * Load local cart data from database or session
     */
    private function loadLocalCart()
    {
        if (Auth::check()) {
            // Logged in user - get from database cart
            $cart = Auth::user()->getOrCreateCart();
            $cartItems = $cart->items()->with('inventory')->get();
            
            // Transform into local cart format
            $this->localCart = $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'inventory_id' => $item->inventory_id,
                    'inventory' => $item->inventory,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'synced' => true,
                ];
            })->keyBy('inventory_id')->toArray();
        } else {
            // Guest user - get from session
            $this->localCart = Session::get('cart', []);
        }
    }

    /**
     * Save local cart to appropriate storage
     *
     * @return void
     */
    public function saveLocalCart()
    {
        if (!Auth::check()) {
            // Guest user - save to session
            Session::put('cart', $this->localCart);
            return;
        }

        // Skip if sync is in progress
        if ($this->syncInProgress) {
            return;
        }

        $this->syncToDatabase();
    }

    /**
     * Sync local cart to database
     *
     * @return void
     */
    public function syncToDatabase()
    {
        if (!Auth::check()) {
            return;
        }

        $this->syncInProgress = true;
        
        try {
            $user = Auth::user();
            $cart = $user->getOrCreateCart();
            
            foreach ($this->localCart as $inventoryId => $item) {
                if (isset($item['synced']) && $item['synced']) {
                    // Item is already synced, skip
                    continue;
                }
                
                $inventory = Inventory::find($inventoryId);
                if (!$inventory) {
                    // Remove invalid items from local cart
                    unset($this->localCart[$inventoryId]);
                    continue;
                }
                
                // Determine price based on user's state permissions
                $priceField = $user->price_field;
                $price = $inventory->$priceField;
                
                if (!$price) {
                    // Skip items without price
                    continue;
                }
                
                $quantity = (int)$item['quantity'];
                
                // Apply quantity limits
                if ($quantity > 99) {
                    $quantity = 99;
                }
                
                // Find or create cart item
                $cartItem = $cart->items()->updateOrCreate(
                    ['inventory_id' => $inventoryId],
                    [
                        'quantity' => $quantity,
                        'price' => $price,
                    ]
                );
                
                // Update local cart with database ID and mark as synced
                $this->localCart[$inventoryId]['id'] = $cartItem->id;
                $this->localCart[$inventoryId]['synced'] = true;
            }
            
            // Handle deleted items
            $existingItems = $cart->items()->get();
            foreach ($existingItems as $item) {
                if (!isset($this->localCart[$item->inventory_id])) {
                    // Item was deleted from local cart, remove from database
                    $item->delete();
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync cart to database: ' . $e->getMessage());
        } finally {
            $this->syncInProgress = false;
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
        // Load local cart if empty
        if (empty($this->localCart)) {
            $this->loadLocalCart();
        }
        
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
        
        // Check if item exists in cart
        if (isset($this->localCart[$inventoryId])) {
            if ($quantity === 0) {
                // Remove item if quantity is 0
                unset($this->localCart[$inventoryId]);
                $this->saveLocalCart();
                
                return [
                    'success' => true,
                    'message' => 'Item removed from cart',
                    'action' => 'removed'
                ];
            } else {
                // Update quantity
                if ($replace) {
                    $this->localCart[$inventoryId]['quantity'] = $quantity;
                } else {
                    $this->localCart[$inventoryId]['quantity'] += $quantity;
                    
                    // Cap at 99
                    if ($this->localCart[$inventoryId]['quantity'] > 99) {
                        $this->localCart[$inventoryId]['quantity'] = 99;
                    }
                }
                
                // Mark as needing sync
                $this->localCart[$inventoryId]['synced'] = false;
                
                $this->saveLocalCart();
                
                return [
                    'success' => true,
                    'message' => 'Cart updated',
                    'action' => 'updated',
                    'quantity' => $this->localCart[$inventoryId]['quantity']
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
            
            // Get price for user
            $priceField = 'price';
            if (Auth::check()) {
                $priceField = Auth::user()->price_field;
            }
            
            $price = $inventory->$priceField;
            
            // Add new item
            $this->localCart[$inventoryId] = [
                'inventory_id' => $inventoryId,
                'inventory' => $inventory,
                'quantity' => $quantity,
                'price' => $price,
                'synced' => false
            ];
            
            $this->saveLocalCart();
            
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
        // Load local cart if empty
        if (empty($this->localCart)) {
            $this->loadLocalCart();
        }
        
        if (isset($this->localCart[$inventoryId])) {
            unset($this->localCart[$inventoryId]);
            $this->saveLocalCart();
            
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

    /**
     * Get total number of items in cart
     *
     * @return int
     */
    public function getCartCount()
    {
        // Load local cart if empty
        if (empty($this->localCart)) {
            $this->loadLocalCart();
        }
        
        $count = 0;
        foreach ($this->localCart as $item) {
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
        // Load local cart if empty
        if (empty($this->localCart)) {
            $this->loadLocalCart();
        }
        
        $total = 0;
        foreach ($this->localCart as $item) {
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
    }

    /**
     * Force a database sync
     *
     * @return void
     */
    public function forceDatabaseSync()
    {
        $this->syncToDatabase();
    }
}