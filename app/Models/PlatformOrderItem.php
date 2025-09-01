<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformOrderItem extends Model
{
    use HasFactory;

    protected $table = 'platform_orders_items';

    protected $fillable = [
        'driver_id',
        'platform_id',
        'orders',
        'status',
        'platform_order_id'
    ];

    protected $casts = [
        'orders' => 'integer',
        'platform_id' => 'integer',
        'driver_id' => 'integer',
        'platform_order_id' => 'integer'
    ];

    /**
     * Get the driver that owns this order item
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the platform order that owns this item
     */
    public function platformOrder(): BelongsTo
    {
        return $this->belongsTo(PlatformOrder::class, 'platform_order_id');
    }
}
