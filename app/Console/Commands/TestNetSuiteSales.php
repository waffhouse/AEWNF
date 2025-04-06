<?php

namespace App\Console\Commands;

use App\Services\NetSuiteService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestNetSuiteSales extends Command
{
    protected $signature = 'netsuite:test-sales';

    protected $description = 'Test the NetSuite sales data connection';

    public function handle(NetSuiteService $netSuiteService)
    {
        $this->info('Testing NetSuite sales data connection...');

        try {
            // Use the values from the provided URL
            $scriptId = '1270';
            $deployId = '2';

            $this->info("Using Script ID: {$scriptId}, Deploy ID: {$deployId}");

            // Test connection with minimal data request and include credit memos
            $result = $netSuiteService->callRestlet(
                'GET',
                [
                    'pageIndex' => 0,
                    'pageSize' => 100,
                    'includeCredits' => true,  // Try adding this parameter
                ],
                $scriptId,
                $deployId
            );

            if (! is_array($result)) {
                $this->error('Invalid response format: '.(is_string($result) ? $result : gettype($result)));

                return 1;
            }

            if (isset($result['status']) && $result['status'] === 'ERROR') {
                $this->error('Error from NetSuite: '.($result['message'] ?? 'Unknown error'));

                return 1;
            }

            $this->info('Connection successful!');
            $this->info('Response summary:');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Status', $result['status'] ?? 'Unknown'],
                    ['Total Pages', $result['totalPages'] ?? 'Unknown'],
                    ['Current Page', $result['currentPage'] ?? 'Unknown'],
                    ['Page Size', $result['pageSize'] ?? 'Unknown'],
                    ['Transactions', count($result['data'] ?? [])],
                ]
            );

            if (! empty($result['data'])) {
                $this->info('First transaction details:');
                $first = $result['data'][0];
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Transaction ID', $first['tranId'] ?? 'Unknown'],
                        ['Type', $first['type'] ?? 'Unknown'],
                        ['Date', $first['date'] ?? 'Unknown'],
                        ['Customer', $first['customerName'] ?? 'Unknown'],
                        ['Lines', count($first['lines'] ?? [])],
                    ]
                );
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Error testing NetSuite connection: '.$e->getMessage());
            Log::error('NetSuite test command error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }
}
