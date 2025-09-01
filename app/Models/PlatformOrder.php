<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformOrder extends Model
{
    use HasFactory;

    protected $table = 'platform_orders';

    protected $fillable = [
        'status',
        'mappedDrivers',
        'totalDrivers',
        'totalOrders',
        'unmappedDrivers',
        'date',
        'company_id',
        'amount',
        'from_account_id',
        'beneficiary',
        'driver_id',
        'category',
        'notes',
        'invoice_url',
        'created_by'
    ];

    protected $casts = [
        'mappedDrivers' => 'integer',
        'totalDrivers' => 'integer',
        'totalOrders' => 'integer',
        'unmappedDrivers' => 'integer',
        'date' => 'date',
        'company_id' => 'integer'
    ];

    /**
     * Get the company that owns this platform order
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the order items for this platform order
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(PlatformOrderItem::class, 'platform_order_id');
    }

    /**
     * Get the drivers through order items
     */
    public function drivers()
    {
        return $this->hasManyThrough(
            Driver::class,
            PlatformOrderItem::class,
            'platform_order_id', // Foreign key on platform_order_items table
            'id', // Foreign key on drivers table
            'id', // Local key on platform_orders table
            'driver_id' // Local key on platform_order_items table
        );
    }
}
