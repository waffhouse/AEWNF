<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class OrderService
 *
 * Service class responsible for order-related business logic.
 * Extracted from CartPage and OrderManagement components.
 */
class OrderService
{
    /**
     * Create a new order from a user's cart
     *
     * @param  User  $user  The user placing the order
     * @param  string|null  $notes  Optional order notes
     * @return Order|false The created order or false if failed
     *
     * @throws \Exception If an error occurs during order creation
     */
    public function createOrder(User $user, ?string $notes = null, string $deliveryType = 'pickup'): Order|false
    {
        // Ensure the user has a cart
        $cart = $user->cart;
        if (! $cart || $cart->items()->count() === 0) {
            throw new \Exception('Cart is empty');
        }

        // Validate delivery type
        if (! in_array($deliveryType, [Order::DELIVERY_TYPE_PICKUP, Order::DELIVERY_TYPE_DELIVERY])) {
            $deliveryType = Order::DELIVERY_TYPE_PICKUP;
        }

        // Begin a database transaction
        DB::beginTransaction();

        try {
            Log::info("Creating order for user ID: {$user->id}");

            // Create the order
            $order = Order::create([
                'user_id' => $user->id,
                'total' => $cart->getTotal(),
                'status' => Order::STATUS_PENDING,
                'notes' => $notes,
                'delivery_type' => $deliveryType,
            ]);

            // Add cart items to the order
            $cartItems = $cart->items()->with('inventory')->get();

            foreach ($cartItems as $cartItem) {
                $inventory = $cartItem->inventory;

                // Skip items with missing inventory
                if (! $inventory) {
                    Log::warning("Skipping cart item with missing inventory: {$cartItem->id}");

                    continue;
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'inventory_id' => $cartItem->inventory_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'product_name' => $inventory->description,
                    'product_sku' => $inventory->sku,
                ]);
            }

            // Clear the cart after successful order creation
            $cart->items()->delete();

            // Commit the transaction
            DB::commit();

            Log::info("Order created successfully: {$order->id}");

            return $order;
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();

            Log::error('Error creating order: '.$e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Update order status
     *
     * @param  int  $orderId  Order ID
     * @param  string  $status  New status (one of Order::STATUS_* constants)
     * @param  User|null  $updatedBy  User performing the update (for logging)
     * @return bool Success status
     *
     * @throws \InvalidArgumentException If status is invalid
     */
    public function updateOrderStatus(int $orderId, string $status, ?User $updatedBy = null): bool
    {
        // Validate the status
        $validStatuses = [
            Order::STATUS_PENDING,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELLED,
        ];

        if (! in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid order status: {$status}");
        }

        try {
            $order = Order::findOrFail($orderId);
            $oldStatus = $order->status;

            // Use the appropriate method based on the requested status
            $success = match ($status) {
                Order::STATUS_COMPLETED => $order->complete(),
                Order::STATUS_CANCELLED => $order->cancel(),
                Order::STATUS_PENDING => $this->setOrderStatus($order, $status),
                default => false
            };

            if ($success) {
                Log::info('Order status updated', [
                    'order_id' => $orderId,
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'updated_by' => $updatedBy ? $updatedBy->id : 'system',
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Error updating order status', [
                'order_id' => $orderId,
                'status' => $status,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Set an order's status directly
     * Used when we need to override normal status flow
     *
     * @param  Order  $order  The order to update
     * @param  string  $status  The new status
     * @return bool Success status
     */
    private function setOrderStatus(Order $order, string $status): bool
    {
        $order->status = $status;

        return $order->save();
    }

    /**
     * Get an order by ID with related items
     *
     * @param  int  $orderId  Order ID
     * @param  array  $with  Related models to eager load
     */
    public function getOrderById(int $orderId, array $with = ['items', 'user']): ?Order
    {
        return Order::with($with)->find($orderId);
    }

    /**
     * Get orders with filtering options
     *
     * @param  array  $filters  Array of filters to apply
     * @param  array  $options  Query options ['with' => array, 'orderBy' => string, 'direction' => string]
     * @return Builder Query builder instance
     */
    public function getOrdersQuery(array $filters = [], array $options = []): Builder
    {
        $query = Order::query();

        // Apply eager loading
        if (isset($options['with']) && is_array($options['with'])) {
            $query->with($options['with']);
        } else {
            // Default eager loading
            $query->with(['user']);
        }

        // Filter by user
        if (isset($filters['user_id']) && ! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by status
        if (isset($filters['status']) && ! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Date range filter
        if (isset($filters['date_from']) && ! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && ! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Search filter (ID, customer name, email, etc.)
        if (isset($filters['search']) && ! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('customer_number', 'like', "%{$search}%");
                    });
            });
        }

        // Default sorting: pending orders first, then by date descending
        if (! isset($options['orderBy']) || ! isset($options['direction'])) {
            $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'completed' THEN 1 ELSE 2 END")
                ->orderBy('created_at', 'desc');
        } else {
            // Custom sorting
            $query->orderBy($options['orderBy'], $options['direction']);
        }

        return $query;
    }

    /**
     * Get a paginated list of orders with optional filters
     *
     * @param  int  $perPage  Number of items per page
     * @param  array  $filters  Array of filters to apply
     * @param  array  $options  Query options
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedOrders(int $perPage = 15, array $filters = [], array $options = [])
    {
        return $this->getOrdersQuery($filters, $options)->paginate($perPage);
    }

    /**
     * Get orders for the current user
     *
     * @param  User  $user  The user to get orders for
     * @param  array  $with  Relations to eager load
     */
    public function getUserOrders(User $user, array $with = ['items']): Collection
    {
        return $user->orders()->with($with)->latest()->get();
    }

    /**
     * Cancel an order with validation checks
     *
     * @param  int  $orderId  Order ID
     * @param  User|null  $canceledBy  User canceling the order (for logging)
     * @return bool Success status
     */
    public function cancelOrder(int $orderId, ?User $canceledBy = null): bool
    {
        try {
            $order = Order::findOrFail($orderId);

            // Check if order can be canceled
            if (! $order->canBeCancelled()) {
                Log::warning('Attempt to cancel order that cannot be canceled', [
                    'order_id' => $orderId,
                    'status' => $order->status,
                ]);

                return false;
            }

            $success = $order->cancel();

            if ($success) {
                Log::info('Order canceled', [
                    'order_id' => $orderId,
                    'canceled_by' => $canceledBy ? $canceledBy->id : 'system',
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Error canceling order', [
                'order_id' => $orderId,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get order statistics for dashboard
     *
     * @return array Order statistics
     */
    public function getOrderStats(): array
    {
        return [
            'total' => Order::count(),
            'pending' => Order::where('status', Order::STATUS_PENDING)->count(),
            'completed' => Order::where('status', Order::STATUS_COMPLETED)->count(),
            'cancelled' => Order::where('status', Order::STATUS_CANCELLED)->count(),
        ];
    }
}
