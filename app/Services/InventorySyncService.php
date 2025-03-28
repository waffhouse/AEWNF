<?php

namespace App\Services;

use App\Models\Inventory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Throwable;

class InventorySyncService
{
    /**
     * @var NetSuiteService
     */
    protected $netSuiteService;
    
    /**
     * @var array
     */
    protected $stats = [
        'created' => 0,
        'updated' => 0,
        'failed' => 0,
        'deleted' => 0,
        'total' => 0,
    ];
    
    /**
     * @param NetSuiteService $netSuiteService
     */
    public function __construct(NetSuiteService $netSuiteService)
    {
        $this->netSuiteService = $netSuiteService;
    }
    
    /**
     * Sync inventory data from NetSuite to local database
     * 
     * @param array $options Optional parameters to customize sync behavior
     * @return array Summary of sync results
     */
    public function syncInventory(array $options = []): array
    {
        $startTime = microtime(true);
        $this->resetStats();
        
        Log::info('Starting NetSuite inventory sync', $options);
        
        try {
            // Fetch inventory data
            $inventoryData = $this->fetchInventoryData($options);
            $this->stats['total'] = count($inventoryData);
            
            // Process inventory in a transaction
            DB::beginTransaction();
            
            try {
                $processedIds = $this->processInventoryItems($inventoryData);
                $this->removeDeletedItems($processedIds);
                
                DB::commit();
                Log::info('Inventory sync completed successfully', $this->stats);
            } catch (Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Throwable $e) {
            $this->handleSyncException($e);
        }
        
        $this->stats['duration'] = round(microtime(true) - $startTime, 2) . ' seconds';
        return $this->stats;
    }
    
    /**
     * Reset sync statistics
     */
    protected function resetStats(): void
    {
        $this->stats = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'deleted' => 0,
            'total' => 0,
        ];
    }
    
    /**
     * Fetch inventory data from NetSuite
     * 
     * @return array
     * @throws Exception If NetSuite returns invalid data
     */
    protected function fetchInventoryData(array $options = []): array
    {
        $inventoryData = $this->netSuiteService->getInventory($options);
        
        if (!is_array($inventoryData)) {
            throw new Exception('Invalid response from NetSuite: ' . 
                (is_string($inventoryData) ? $inventoryData : 'Unknown error'));
        }
        
        Log::info("Retrieved {$this->stats['total']} inventory items from NetSuite");
        return $inventoryData;
    }
    
    /**
     * Process multiple inventory items
     * 
     * @param array $items Array of inventory items from NetSuite
     * @return array Array of processed NetSuite IDs
     */
    protected function processInventoryItems(array $items): array
    {
        $processedIds = [];
        
        foreach ($items as $item) {
            try {
                $this->validateInventoryItem($item);
                $this->processInventoryItem($item);
                
                // Store the ID of each processed item
                if (!empty($item['id'])) {
                    $processedIds[] = $item['id'];
                }
            } catch (Throwable $e) {
                $this->handleItemProcessingException($e, $item);
            }
        }
        
        return $processedIds;
    }
    
    /**
     * Validate that an inventory item has the required fields
     * 
     * @param array $item The inventory item data
     * @throws Exception If validation fails
     */
    protected function validateInventoryItem(array $item): void
    {
        if (empty($item['id'])) {
            throw new Exception('Inventory item missing required ID field');
        }
    }
    
    /**
     * Process a single inventory item from NetSuite
     * 
     * @param array $item The inventory item data from NetSuite
     * @return Inventory The processed inventory record
     */
    protected function processInventoryItem(array $item): Inventory
    {
        // Extract pricing information
        $prices = $this->extractPricing($item);
        
        // Prepare inventory data
        $inventoryData = [
            'netsuite_id' => $item['id'],
            'sku' => $item['name'] ?? null,
            'brand' => $item['brand'] ?? null,
            'class' => $item['class'] ?? null,
            'description' => $item['description'] ?? null,
            'state' => $item['state'] ?? null,
            'quantity' => is_numeric($item['available'] ?? null) ? (int)$item['available'] : null,
            'fl_price' => $prices['fl_price'],
            'ga_price' => $prices['ga_price'],
            'bulk_price' => $prices['bulk_price'],
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
            $this->stats['created']++;
        } else {
            $this->stats['updated']++;
        }
        
        return $inventory;
    }
    
    /**
     * Extract pricing information from an inventory item
     * 
     * @param array $item The inventory item data
     * @return array Extracted pricing data
     */
    protected function extractPricing(array $item): array
    {
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
        
        return [
            'fl_price' => $flPrice,
            'ga_price' => $gaPrice,
            'bulk_price' => $bulkPrice,
        ];
    }
    
    /**
     * Remove inventory items that no longer exist in NetSuite
     * 
     * @param array $processedIds Array of NetSuite IDs that were processed
     * @return int Number of deleted items
     */
    protected function removeDeletedItems(array $processedIds): int
    {
        if (empty($processedIds)) {
            return 0;
        }
        
        $deleted = Inventory::whereNotIn('netsuite_id', $processedIds)->delete();
        $this->stats['deleted'] = $deleted;
        
        if ($deleted > 0) {
            Log::info("Deleted {$deleted} inventory items that were no longer active in NetSuite");
        }
        
        return $deleted;
    }
    
    /**
     * Handle exceptions during item processing
     * 
     * @param Throwable $e The exception
     * @param array $item The inventory item data
     */
    protected function handleItemProcessingException(Throwable $e, array $item): void
    {
        Log::error('Error processing inventory item', [
            'item_id' => $item['id'] ?? 'unknown',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        $this->stats['failed']++;
    }
    
    /**
     * Handle exceptions during the overall sync process
     * 
     * @param Throwable $e The exception
     */
    protected function handleSyncException(Throwable $e): void
    {
        Log::error('Error syncing inventory', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        $this->stats['failed'] = $this->stats['total'];
        $this->stats['error'] = $e->getMessage();
    }
}