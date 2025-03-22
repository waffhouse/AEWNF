<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'customer_number',
        'state',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    /**
     * Check if user can view Florida items
     *
     * @return bool
     */
    public function canViewFloridaItems(): bool
    {
        // Allow if user has explicit permission to view Florida items
        return $this->hasPermissionTo('view florida items');
    }
    
    /**
     * Check if user can view Georgia items
     *
     * @return bool
     */
    public function canViewGeorgiaItems(): bool
    {
        // Allow if user has explicit permission to view Georgia items
        return $this->hasPermissionTo('view georgia items');
    }
    
    /**
     * Check if user can view unrestricted items
     * 
     * @return bool
     */
    public function canViewUnrestrictedItems(): bool
    {
        // Allow if user has explicit permission to view unrestricted items
        return $this->hasPermissionTo('view unrestricted items');
    }
    
    /**
     * Get the cart associated with the user.
     */
    public function cart()
    {
        return $this->hasOne(Cart::class)->with('items');
    }
    
    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class)->latest();
    }
    
    /**
     * Get the active cart for the user or create one if it doesn't exist.
     */
    public function getOrCreateCart()
    {
        if (!$this->cart) {
            return $this->cart()->create();
        }
        
        return $this->cart;
    }
    
    /**
     * Determine the appropriate price field based on user's state permissions.
     */
    public function getPriceFieldAttribute(): string
    {
        if ($this->canViewFloridaItems() && !$this->canViewGeorgiaItems()) {
            return 'fl_price';
        }
        
        if ($this->canViewGeorgiaItems() && !$this->canViewFloridaItems()) {
            return 'ga_price';
        }
        
        // Default to Florida price for staff/admin (who can view both states)
        return 'fl_price';
    }
}
