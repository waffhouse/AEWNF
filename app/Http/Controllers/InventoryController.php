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
}