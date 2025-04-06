<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'netsuite_id',
        'entity_id',
        'company_name',
        'email',
        'shipping_address',
        'county',
        'home_state',
        'license_type',
        'license_number',
        'phone',
        'price_level',
        'terms',
        'last_sync_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_sync_at' => 'datetime',
    ];

    /**
     * Get the users associated with this customer.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'customer_number', 'entity_id');
    }

    /**
     * Get the sales records for this customer.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class, 'entity_id', 'entity_id');
    }

    /**
     * Get the orders for this customer.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_number', 'entity_id');
    }
}
