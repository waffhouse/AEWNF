<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserSyncService
{
    /**
     * @var array
     */
    protected $stats = [
        'refreshed' => 0,
        'failed' => 0,
        'total' => 0,
    ];

    /**
     * Sync users from database
     *
     * @param  array  $options  Optional parameters to customize sync behavior
     * @return array Summary of sync results
     */
    public function syncUsers(array $options = []): array
    {
        $startTime = microtime(true);
        $this->resetStats();

        Log::info('Starting user data refresh', $options);

        try {
            // Count total users
            $this->stats['total'] = User::count();

            // Process users
            $this->refreshUsers($options);

            Log::info('User refresh completed successfully', $this->stats);
        } catch (Throwable $e) {
            Log::error('Error refreshing users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->stats['failed'] = $this->stats['total'];
            $this->stats['error'] = $e->getMessage();
        }

        $this->stats['duration'] = round(microtime(true) - $startTime, 2).' seconds';

        return $this->stats;
    }

    /**
     * Reset sync statistics
     */
    protected function resetStats(): void
    {
        $this->stats = [
            'refreshed' => 0,
            'failed' => 0,
            'total' => 0,
        ];
    }

    /**
     * Refresh users data
     *
     * @param  array  $options  Optional parameters
     * @return int Number of refreshed users
     */
    protected function refreshUsers(array $options = []): int
    {
        // Set the last_refreshed_at field for all users
        $now = Carbon::now();
        $batchSize = $options['batch_size'] ?? 100;
        $refreshed = 0;

        try {
            // Process users in batches to avoid memory issues with large datasets
            User::query()->chunkById($batchSize, function ($users) use (&$refreshed, $now) {
                foreach ($users as $user) {
                    try {
                        $user->last_refreshed_at = $now;
                        $user->save();
                        $refreshed++;
                    } catch (Throwable $e) {
                        Log::error('Error refreshing user', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                        $this->stats['failed']++;
                    }
                }
            });

            $this->stats['refreshed'] = $refreshed;

            return $refreshed;
        } catch (Throwable $e) {
            throw new Exception('Failed to refresh users: '.$e->getMessage(), 0, $e);
        }
    }
}
