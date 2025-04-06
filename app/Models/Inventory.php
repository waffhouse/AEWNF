<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'netsuite_id',
        'sku',
        'brand',
        'class',
        'description',
        'state',
        'quantity',
        'fl_price',
        'ga_price',
        'bulk_price',
        'raw_data',
        'last_synced_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'fl_price' => 'decimal:2',
        'ga_price' => 'decimal:2',
        'bulk_price' => 'decimal:2',
        'raw_data' => 'array',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Determine if the inventory item is available in Florida
     */
    public function isAvailableInFlorida(): bool
    {
        return empty($this->state) || $this->state === 'Florida';
    }

    /**
     * Determine if the inventory item is available in Georgia
     */
    public function isAvailableInGeorgia(): bool
    {
        return empty($this->state) || $this->state === 'Georgia';
    }

    /**
     * Scope a query to only include items available in Florida
     */
    public function scopeAvailableInFlorida($query)
    {
        return $query->where(function ($query) {
            $query->where('state', '')
                ->orWhere('state', 'Florida');
        });
    }

    /**
     * Scope a query to only include items available in Georgia
     */
    public function scopeAvailableInGeorgia($query)
    {
        return $query->where(function ($query) {
            $query->where('state', '')
                ->orWhere('state', 'Georgia');
        });
    }
}
