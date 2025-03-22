<?php

namespace App\Services;

use App\Models\Inventory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventorySyncService
{
    protected $netSuiteService;
    
    public function __construct(NetSuiteService $netSuiteService)
    {
        $this->netSuiteService = $netSuiteService;
    }
    
    /**
     * Sync inventory data from NetSuite to local database
     * 
     * @return array Summary of sync results
     */
    public function syncInventory(): array
    {
        Log::info('Starting NetSuite inventory sync');
        
        $startTime = microtime(true);
        $stats = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'deleted' => 0,
            'total' => 0,
        ];
        
        try {
            // Get inventory data from NetSuite
            $inventoryData = $this->netSuiteService->getInventory();
            
            if (!is_array($inventoryData)) {
                throw new \Exception('Invalid response from NetSuite: ' . (is_string($inventoryData) ? $inventoryData : 'Unknown error'));
            }
            
            $stats['total'] = count($inventoryData);
            Log::info("Retrieved {$stats['total']} inventory items from NetSuite");
            
            // Process each inventory item
            DB::beginTransaction();
            
            try {
                // Keep track of NetSuite IDs that were processed in this sync
                $processedIds = [];
                
                foreach ($inventoryData as $item) {
                    $this->processInventoryItem($item, $stats);
                    
                    // Store the ID of each processed item
                    if (!empty($item['id'])) {
                        $processedIds[] = $item['id'];
                    }
                }
                
                // Delete items that weren't in this sync (no longer active in NetSuite)
                if (!empty($processedIds)) {
                    $deleted = Inventory::whereNotIn('netsuite_id', $processedIds)->delete();
                    $stats['deleted'] = $deleted;
                    Log::info("Deleted {$deleted} inventory items that were no longer active in NetSuite");
                }
                
                DB::commit();
                Log::info('Inventory sync completed successfully', $stats);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error syncing inventory: ' . $e->getMessage());
            $stats['failed'] = $stats['total'];
            $stats['error'] = $e->getMessage();
        }
        
        $stats['duration'] = round(microtime(true) - $startTime, 2) . ' seconds';
        return $stats;
    }
    
    /**
     * Process a single inventory item from NetSuite
     * 
     * @param array $item The inventory item data from NetSuite
     * @param array &$stats Statistics tracking array
     */
    protected function processInventoryItem(array $item, array &$stats): void
    {
        // Skip items without an ID
        if (empty($item['id'])) {
            Log::warning('Skipping inventory item without ID', ['item' => $item]);
            $stats['failed']++;
            return;
        }
        
        try {
            // Extract pricing information
            $flPrice = null;
            $gaPrice = null;
            $bulkPrice = null;
            
            if (isset($item['pricing']) && is_array($item['pricing'])) {
                foreach ($item['pricing'] as $price) {
                    if (!isset($price['priceLevel']) || !isset($price['unitPrice'])) {
                        continue;
                    }
                    
                    switch ($price['priceLevel']) {
                        case 'FL':
                            $flPrice = $price['unitPrice'];
                            break;
                        case 'GA':
                            $gaPrice = $price['unitPrice'];
                            break;
                        case 'Bulk Discount':
                            $bulkPrice = $price['unitPrice'];
                            break;
                    }
                }
            }
            
            // Prepare inventory data
            $inventoryData = [
                'netsuite_id' => $item['id'],
                'sku' => $item['name'] ?? null,
                'brand' => $item['brand'] ?? null,
                'class' => $item['class'] ?? null,
                'description' => $item['description'] ?? null,
                'state' => $item['state'] ?? null,
                'quantity' => is_numeric($item['available'] ?? null) ? (int)$item['available'] : null,
                'fl_price' => $flPrice,
                'ga_price' => $gaPrice,
                'bulk_price' => $bulkPrice,
                'raw_data' => $item,
                'last_synced_at' => Carbon::now(),
            ];
            
            // Create or update the inventory record
            $inventory = Inventory::updateOrCreate(
                ['netsuite_id' => $item['id']],
                $inventoryData
            );
            
            // Update stats
            if ($inventory->wasRecentlyCreated) {
                $stats['created']++;
            } else {
                $stats['updated']++;
            }
            
        } catch (\Exception $e) {
            Log::error('Error processing inventory item', [
                'item_id' => $item['id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            $stats['failed']++;
        }
    }
}