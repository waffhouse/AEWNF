<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class CustomerSyncService
{
    protected $netsuiteService;

    protected $stats;

    public function __construct(NetSuiteService $netsuiteService)
    {
        $this->netsuiteService = $netsuiteService;
        $this->resetStats();
    }

    /**
     * Reset synchronization statistics
     */
    public function resetStats(): void
    {
        $this->stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];
    }

    /**
     * Get synchronization statistics
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * Synchronize all customers from NetSuite
     *
     * @return array Synchronization statistics
     */
    public function syncAllCustomers(): array
    {
        Log::info('Starting customer sync from NetSuite');
        $this->resetStats();

        try {
            // Call the NetSuite RESTlet to get all customers using the getCustomers method
            $timeout = config('netsuite.timeout', 300);

            Log::info('Calling NetSuite customer RESTlet', [
                'timeout' => $timeout,
            ]);

            $customersData = $this->netsuiteService->getCustomers(['timeout' => $timeout]);

            if (empty($customersData) || ! is_array($customersData)) {
                Log::error('Invalid response from NetSuite customer RESTlet', [
                    'response' => $customersData,
                ]);
                throw new \Exception('Invalid response from NetSuite customer RESTlet');
            }

            Log::info('Received customer data from NetSuite', [
                'count' => is_array($customersData) ? count($customersData) : 0,
            ]);

            // Use a transaction to ensure data consistency
            \DB::beginTransaction();

            try {
                // Track processed NetSuite IDs
                $processedIds = [];

                // Process each customer
                foreach ($customersData as $customerData) {
                    $customer = $this->syncCustomer($customerData);
                    if ($customer && $customer->netsuite_id) {
                        $processedIds[] = $customer->netsuite_id;
                    }
                    $this->stats['total']++;
                }

                // Remove customers that no longer exist in NetSuite
                $this->removeDeletedCustomers($processedIds);

                // Commit the transaction
                \DB::commit();

                Log::info('Completed customer sync', $this->stats);

                return $this->stats;
            } catch (\Exception $e) {
                // Rollback the transaction if anything goes wrong
                \DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error during customer sync', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->stats['errors'][] = $e->getMessage();

            return $this->stats;
        }
    }

    /**
     * Synchronize a specific customer from NetSuite
     *
     * @param  string  $customerId  NetSuite internal ID
     * @return array Synchronization result
     */
    public function syncCustomerById(string $customerId): array
    {
        Log::info('Starting sync for specific customer', ['id' => $customerId]);
        $this->resetStats();

        try {
            // Call the NetSuite RESTlet to get a single customer using the getCustomers method
            $timeout = config('netsuite.timeout', 300);

            $customerData = $this->netsuiteService->getCustomers([
                'id' => $customerId,
                'timeout' => $timeout,
            ]);

            if (empty($customerData) || isset($customerData['error'])) {
                Log::error('Error retrieving customer from NetSuite', [
                    'id' => $customerId,
                    'response' => $customerData,
                ]);
                throw new \Exception('Error retrieving customer from NetSuite: '.
                    (isset($customerData['error']) ? $customerData['error'] : 'Unknown error'));
            }

            $this->syncCustomer($customerData);
            $this->stats['total'] = 1;

            Log::info('Completed specific customer sync', $this->stats);

            return $this->stats;

        } catch (\Exception $e) {
            Log::error('Error during specific customer sync', [
                'id' => $customerId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->stats['errors'][] = $e->getMessage();

            return $this->stats;
        }
    }

    /**
     * Process and save a single customer from NetSuite data
     *
     * @param  array  $data  Customer data from NetSuite
     */
    protected function syncCustomer(array $data): ?Customer
    {
        if (empty($data['id']) || empty($data['entityId'])) {
            Log::warning('Skipping customer with missing ID', [
                'data' => $data,
            ]);
            $this->stats['skipped']++;
            throw new \Exception('Customer data missing required fields');
        }

        try {
            // Map NetSuite data to our model
            $customerData = [
                'netsuite_id' => $data['id'],
                'entity_id' => $data['entityId'],
                'company_name' => $data['companyName'] ?? null,
                'email' => $data['email'] ?? null,
                'shipping_address' => $data['shippingAddress'] ?? null,
                'county' => $data['county'] ?? null,
                'home_state' => $data['homeState'] ?? null,
                'license_type' => $data['licenseType'] ?? null,
                'license_number' => $data['tLicenseNo'] ?? null,
                'phone' => $data['phone'] ?? null,
                'price_level' => $data['priceLevel'] ?? null,
                'terms' => $data['terms'] ?? null,
                'last_sync_at' => now(),
            ];

            // Find existing customer or create new one
            $customer = Customer::updateOrCreate(
                ['netsuite_id' => $data['id']],
                $customerData
            );

            // Update stats
            if ($customer->wasRecentlyCreated) {
                $this->stats['created']++;
                Log::info('Created new customer', [
                    'netsuite_id' => $data['id'],
                    'entity_id' => $data['entityId'],
                    'company_name' => $data['companyName'] ?? null,
                ]);
            } else {
                $this->stats['updated']++;
                Log::info('Updated existing customer', [
                    'netsuite_id' => $data['id'],
                    'entity_id' => $data['entityId'],
                    'company_name' => $data['companyName'] ?? null,
                ]);
            }

            return $customer;

        } catch (\Exception $e) {
            $this->stats['failed']++;
            Log::error('Failed to sync customer', [
                'netsuite_id' => $data['id'] ?? 'unknown',
                'entity_id' => $data['entityId'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            $this->stats['errors'][] = 'Failed to sync customer '.
                ($data['entityId'] ?? 'unknown').': '.$e->getMessage();

            throw $e;
        }
    }

    /**
     * Remove customer records that no longer exist in NetSuite
     *
     * @param  array  $processedIds  Array of NetSuite IDs that were processed
     * @return int Number of deleted customers
     */
    protected function removeDeletedCustomers(array $processedIds): int
    {
        if (empty($processedIds)) {
            return 0;
        }

        // Find and delete customers that aren't in the current NetSuite data
        $deleted = Customer::whereNotIn('netsuite_id', $processedIds)->delete();
        $this->stats['deleted'] = $deleted;

        if ($deleted > 0) {
            Log::info("Deleted {$deleted} customers that were no longer active in NetSuite");
        }

        return $deleted;
    }
}
