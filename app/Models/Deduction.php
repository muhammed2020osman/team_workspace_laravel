<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deduction extends Model
{
    use Filterable;

    use HasFactory;

    protected $fillable = [
        'driver_payroll_id',
        'name',
        'amount',
        'type',
        'reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function driverPayroll(): BelongsTo
    {
        return $this->belongsTo(DriverPayroll::class);
    }
}
