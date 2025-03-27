<?php

namespace App\Http\Controllers;

use App\Services\NetSuiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InventoryController extends Controller
{
    protected $netsuiteService;

    public function __construct(NetSuiteService $netsuiteService)
    {
        $this->netsuiteService = $netsuiteService;
    }

    /**
     * Display inventory data from NetSuite
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Get parameters from request
            $params = $request->only(['itemId', 'category', 'location']);
            
            // Call NetSuite service to get inventory data
            $inventoryData = $this->netsuiteService->getInventory($params);
            
            return response()->json($inventoryData);
        } catch (\Exception $e) {
            Log::error('Inventory fetch error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to retrieve inventory data from NetSuite',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    // Public catalog is now handled by the Livewire component
    
    /**
     * Check if an item is in the user's cart
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCart(Request $request)
    {
        try {
            // If checking a specific item
            if ($request->has('id')) {
                // Validate request
                $request->validate([
                    'id' => 'required|numeric'
                ]);
                
                $id = $request->input('id');
                $inCart = false;
                
                // Check if user has a cart
                $user = auth()->user();
                $cart = $user->cart;
                
                if ($cart) {
                    // Check if item is in cart
                    $cartItem = $cart->items()->where('inventory_id', $id)->first();
                    $inCart = !is_null($cartItem);
                }
                
                return response()->json([
                    'inCart' => $inCart
                ]);
            } 
            // Return all cart items
            else {
                $user = auth()->user();
                $cart = $user->cart;
                $items = [];
                
                if ($cart) {
                    $cartItems = $cart->items()->with('inventory')->get();
                    foreach ($cartItems as $item) {
                        $items[$item->inventory_id] = [
                            'quantity' => $item->quantity,
                            'sku' => $item->inventory->sku,
                            'description' => $item->inventory->description
                        ];
                    }
                }
                
                return response()->json([
                    'count' => count($items),
                    'items' => $items
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to check cart status',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}