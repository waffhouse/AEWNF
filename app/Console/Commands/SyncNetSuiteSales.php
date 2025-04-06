<?php

namespace App\Console\Commands;

use App\Services\SalesSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncNetSuiteSales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'netsuite:sync-sales
                           {--size= : Items per page in NetSuite (internal)}
                           {--date= : Transaction date to filter by (YYYY-MM-DD)}
                           {--timeout= : Custom timeout in seconds for large syncs (default: 300)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync sales data from NetSuite';

    /**
     * Execute the console command.
     */
    public function handle(SalesSyncService $salesSyncService)
    {
        // Increase PHP execution time limit to match our timeout
        $timeoutOption = $this->option('timeout');
        $executionTimeLimit = $timeoutOption ? (int) $timeoutOption : 300;
        set_time_limit($executionTimeLimit);

        $this->info('Starting NetSuite sales sync...');
        $this->info("PHP execution time limit set to {$executionTimeLimit} seconds");

        $options = $this->buildOptions();

        try {
            $startTime = now();
            $results = $salesSyncService->syncSales($options);
            $duration = now()->diffInSeconds($startTime);

            $this->displayResults($results, $duration);

            return 0;
        } catch (\Exception $e) {
            $this->error('Error syncing sales data from NetSuite: '.$e->getMessage());
            Log::error('NetSuite sales sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }

    /**
     * Build options array from command line arguments
     */
    protected function buildOptions(): array
    {
        $options = [];

        if ($this->option('size') !== null) {
            $options['pageSize'] = (int) $this->option('size');
        }

        if ($this->option('date') !== null) {
            $options['date'] = $this->option('date');
        }

        if ($this->option('timeout') !== null) {
            $options['timeout'] = (int) $this->option('timeout');
        }

        return $options;
    }

    /**
     * Display sync results
     */
    protected function displayResults(array $results, int $duration): void
    {
        $this->info('NetSuite sales sync completed in '.$duration.' seconds');

        $tableRows = [
            ['Total transactions processed', $results['total']],
            ['Created', $results['created']],
            ['Updated', $results['updated']],
            ['Failed', $results['failed']],
            ['Duration', $results['duration'] ?? "$duration seconds"],
        ];

        // Add NetSuite-specific metrics if available
        if (isset($results['netsuite_pages'])) {
            $tableRows[] = ['NetSuite Pages', $results['netsuite_pages']];
        }

        if (isset($results['netsuite_processed'])) {
            $tableRows[] = ['NetSuite Records Processed', $results['netsuite_processed']];
        }

        $this->table(
            ['Metric', 'Value'],
            $tableRows
        );

        if (isset($results['error'])) {
            $this->error('Error: '.$results['error']);
        }

        if ($results['failed'] > 0) {
            $this->warn('Some transactions failed to sync. Check the logs for details.');
        }
    }
}
