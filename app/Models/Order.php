<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'total',
        'status',
        'notes',
        'delivery_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total' => 'decimal:2',
    ];

    /**
     * Possible order statuses
     */
    const STATUS_PENDING = 'pending';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    /**
     * Delivery types
     */
    const DELIVERY_TYPE_PICKUP = 'pickup';

    const DELIVERY_TYPE_DELIVERY = 'delivery';

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get total quantity of items in the order.
     */
    public function getTotalItems(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get items grouped by category/class
     */
    public function getItemsByCategory(): array
    {
        $categorized = [];

        foreach ($this->items as $item) {
            // Get inventory item and its class/category
            if ($item->inventory_id) {
                $inventory = $item->inventory;
                $category = $inventory ? $inventory->class : 'Other';
            } else {
                $category = 'Other';
            }

            if (! isset($categorized[$category])) {
                $categorized[$category] = [
                    'items' => [],
                    'total_quantity' => 0,
                ];
            }

            $categorized[$category]['items'][] = $item;
            $categorized[$category]['total_quantity'] += $item->quantity;
        }

        return $categorized;
    }

    /**
     * Check if the order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Cancel the order.
     */
    public function cancel(): bool
    {
        if (! $this->canBeCancelled()) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;

        return $this->save();
    }

    /**
     * Complete the order.
     */
    public function complete(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->status = self::STATUS_COMPLETED;

        return $this->save();
    }
}
