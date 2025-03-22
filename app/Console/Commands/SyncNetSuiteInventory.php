<?php

namespace App\Console\Commands;

use App\Services\InventorySyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncNetSuiteInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'netsuite:sync-inventory {--force : Force sync even if last sync was recent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync inventory data from NetSuite to local database';

    /**
     * Execute the console command.
     */
    public function handle(InventorySyncService $syncService)
    {
        $this->info('Starting NetSuite inventory sync...');
        $this->newLine();
        
        try {
            $result = $syncService->syncInventory();
            
            if (isset($result['error'])) {
                $this->error('Sync failed: ' . $result['error']);
                return Command::FAILURE;
            }
            
            $this->info('Sync completed in ' . $result['duration']);
            $this->table(
                ['Total', 'Created', 'Updated', 'Failed', 'Deleted'],
                [[
                    $result['total'],
                    $result['created'],
                    $result['updated'],
                    $result['failed'],
                    $result['deleted'] ?? 0
                ]]
            );
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Inventory sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}
