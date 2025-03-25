<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Throwable;

class SalesSyncService
{
    protected $netSuiteService;
    protected $stats = [
        'created' => 0,
        'updated' => 0,
        'failed' => 0,
        'total' => 0,
    ];
    
    public function __construct(NetSuiteService $netSuiteService)
    {
        $this->netSuiteService = $netSuiteService;
    }
    
    public function syncSales(array $options = []): array
    {
        $startTime = microtime(true);
        $this->resetStats();
        
        Log::info('Starting NetSuite sales sync', $options);
        
        try {
            // Fetch sales data
            $salesData = $this->fetchSalesData($options);
            
            if (empty($salesData['data'])) {
                Log::info('No sales data retrieved from NetSuite');
                return $this->stats;
            }
            
            $this->stats['total'] = count($salesData['data']);
            
            // Process sales in a transaction
            DB::beginTransaction();
            
            try {
                $this->processSalesData($salesData['data']);
                
                DB::commit();
                Log::info('Sales sync completed successfully', $this->stats);
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
    
    protected function resetStats(): void
    {
        $this->stats = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'total' => 0,
        ];
    }
    
    protected function fetchSalesData(array $options = []): array
    {
        // Add debugging for connection parameters
        Log::info("Calling NetSuite RESTlet for sales data:", [
            'options' => $options,
            'script_id' => config('netsuite.sales_script_id') ?? 'not set',
            'deploy_id' => config('netsuite.sales_deploy_id') ?? 'not set'
        ]);
        
        // Use the specific script and deploy IDs from the URL
        $result = $this->netSuiteService->callRestlet(
            'GET', 
            $options, 
            '1270', // Script ID from the provided URL
            '2'     // Deploy ID from the provided URL
        );
        
        if (!is_array($result)) {
            throw new Exception('Invalid response from NetSuite: ' . 
                (is_string($result) ? $result : 'Unknown error'));
        }
        
        if (isset($result['status']) && $result['status'] === 'ERROR') {
            throw new Exception('Error from NetSuite: ' . ($result['message'] ?? 'Unknown error'));
        }
        
        Log::info("Retrieved sales data from NetSuite", [
            'total_pages' => $result['totalPages'] ?? 0,
            'current_page' => $result['currentPage'] ?? 0,
            'transactions' => count($result['data'] ?? [])
        ]);
        
        return $result;
    }
    
    protected function processSalesData(array $transactions): void
    {
        foreach ($transactions as $transaction) {
            try {
                $this->validateTransaction($transaction);
                $this->processSaleTransaction($transaction);
            } catch (Throwable $e) {
                $this->handleTransactionException($e, $transaction);
            }
        }
    }
    
    protected function validateTransaction(array $transaction): void
    {
        if (empty($transaction['tranId'])) {
            throw new Exception('Transaction missing required ID field');
        }
        
        if (empty($transaction['lines']) || !is_array($transaction['lines'])) {
            throw new Exception('Transaction missing line items array');
        }
    }
    
    protected function processSaleTransaction(array $transaction): Sale
    {
        // Debug the transaction type
        Log::info('Processing transaction', [
            'tranId' => $transaction['tranId'] ?? 'unknown',
            'type' => $transaction['type'] ?? 'unknown',
            'date' => $transaction['date'] ?? 'unknown',
            'lines_count' => count($transaction['lines'] ?? [])
        ]);
        
        // Calculate the total amount from line items
        $totalAmount = 0;
        foreach ($transaction['lines'] as $line) {
            $totalAmount += $line['amount'] ?? 0;
        }
        
        // Prepare sale data
        $saleData = [
            'tran_id' => $transaction['tranId'],
            'type' => $transaction['type'] ?? null,
            'date' => !empty($transaction['date']) ? Carbon::parse($transaction['date']) : null,
            'entity_id' => $transaction['entityId'] ?? null,
            'customer_name' => $transaction['customerName'] ?? null,
            'total_amount' => $totalAmount,
            'raw_data' => $transaction,
            'last_synced_at' => Carbon::now(),
        ];
        
        // Create or update the sale record
        $sale = Sale::updateOrCreate(
            ['tran_id' => $transaction['tranId']],
            $saleData
        );
        
        // Update stats
        if ($sale->wasRecentlyCreated) {
            $this->stats['created']++;
        } else {
            $this->stats['updated']++;
        }
        
        // Process line items
        $this->processSaleItems($sale, $transaction['lines']);
        
        return $sale;
    }
    
    protected function processSaleItems(Sale $sale, array $lines): void
    {
        // Delete existing line items to replace with updated ones
        $sale->items()->delete();
        
        foreach ($lines as $line) {
            if (empty($line['sku']) && empty($line['item'])) {
                continue; // Skip empty lines
            }
            
            SaleItem::create([
                'sale_id' => $sale->id,
                'sku' => $line['sku'] ?? null,
                'item_description' => $line['item'] ?? null,
                'quantity' => $line['quantity'] ?? 0,
                'amount' => $line['amount'] ?? 0,
            ]);
        }
    }
    
    protected function handleTransactionException(Throwable $e, array $transaction): void
    {
        Log::error('Error processing sales transaction', [
            'transaction_id' => $transaction['tranId'] ?? 'unknown',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        $this->stats['failed']++;
    }
    
    protected function handleSyncException(Throwable $e): void
    {
        Log::error('Error syncing sales data', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        $this->stats['failed'] = $this->stats['total'];
        $this->stats['error'] = $e->getMessage();
    }
}