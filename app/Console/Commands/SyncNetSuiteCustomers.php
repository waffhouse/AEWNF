<?php

namespace App\Console\Commands;

use App\Services\CustomerSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncNetSuiteCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'netsuite:sync-customers 
                            {--id= : Sync a specific customer by NetSuite internal ID}
                            {--timeout=300 : API timeout in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize customer data from NetSuite';

    /**
     * The CustomerSyncService instance.
     *
     * @var \App\Services\CustomerSyncService
     */
    protected $customerSyncService;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\CustomerSyncService $customerSyncService
     * @return void
     */
    public function __construct(CustomerSyncService $customerSyncService)
    {
        parent::__construct();
        $this->customerSyncService = $customerSyncService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Increase PHP execution time limit for large syncs
        set_time_limit($this->option('timeout'));
        
        $this->info('Starting NetSuite customer synchronization...');
        
        try {
            // If a specific customer ID is provided, sync only that customer
            $customerId = $this->option('id');
            
            if ($customerId) {
                $this->info("Syncing customer with ID: {$customerId}");
                $stats = $this->customerSyncService->syncCustomerById($customerId);
            } else {
                $this->info("Syncing all customers from NetSuite");
                $stats = $this->customerSyncService->syncAllCustomers();
            }
            
            // Display results
            $this->info('Synchronization completed!');
            $this->displayStats($stats);
            
            // Return success if no errors, otherwise return failure
            return empty($stats['errors']) ? Command::SUCCESS : Command::FAILURE;
            
        } catch (\Exception $e) {
            $this->error('Error during customer synchronization: ' . $e->getMessage());
            Log::error('Customer sync command error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Display sync statistics in a formatted table
     *
     * @param array $stats
     * @return void
     */
    protected function displayStats(array $stats): void
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Processed', $stats['total']],
                ['Created', $stats['created']],
                ['Updated', $stats['updated']],
                ['Failed', $stats['failed']],
                ['Skipped', $stats['skipped']],
            ]
        );
        
        // Display errors if any
        if (!empty($stats['errors'])) {
            $this->error('Errors occurred during synchronization:');
            foreach ($stats['errors'] as $index => $error) {
                $this->line(" " . ($index + 1) . ". {$error}");
            }
        }
    }
}
