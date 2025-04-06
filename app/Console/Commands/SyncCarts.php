<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CartService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carts:sync {--user= : Sync specific user cart}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all users\' carts from local storage to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cart synchronization...');

        try {
            $userId = $this->option('user');

            if ($userId) {
                // Sync a specific user's cart
                $user = User::find($userId);

                if (! $user) {
                    $this->error("User with ID {$userId} not found.");

                    return Command::FAILURE;
                }

                $this->syncUserCart($user);
                $this->info("Successfully synced cart for user ID {$userId}.");
            } else {
                // Sync all active users' carts
                $users = User::whereNotNull('last_login_at')
                    ->orderBy('last_login_at', 'desc')
                    ->take(100)
                    ->get();

                $total = $users->count();
                $synced = 0;
                $errors = 0;

                $this->output->progressStart($total);

                foreach ($users as $user) {
                    try {
                        $this->syncUserCart($user);
                        $synced++;
                    } catch (\Exception $e) {
                        $errors++;
                        Log::error("Failed to sync cart for user {$user->id}", [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }

                    $this->output->progressAdvance();
                }

                $this->output->progressFinish();

                $this->table(
                    ['Total Users', 'Successfully Synced', 'Errors'],
                    [[$total, $synced, $errors]]
                );
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
            Log::error('Cart sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Sync a specific user's cart
     *
     * @return void
     */
    protected function syncUserCart(User $user)
    {
        // Force database sync for the user
        $cartService = app(CartService::class);
        $cartService->syncToDatabase();
    }
}
