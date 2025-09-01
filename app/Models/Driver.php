<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Driver extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'email',
        'national_id',
        'license_number',
        'license_expiry',
        'salary',
        'status',
        'mobile_number',
        'driver_id',
        'nationality',
        'residence_number',
        'birth_date',
        'residence_expiry_date',
        'number',
        'group_id',
        'vehicle_id',
        'platformIds',
        'platform_ids',
        'history',
        'dailyTarget',
        'notes',
        'account_number',
        'bank_name',
        'iban',
        'id_number',
        'name_ar',
        'name_en',
        'ragat_number'
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'license_expiry' => 'date',
        'birth_date' => 'date',
        'residence_expiry_date' => 'date',
        'platform_ids' => 'array',
        'platformIds' => 'array',
        'history' => 'array'
    ];
    public function expenses()
    {
        return $this->hasMany(Transaction::class, 'driver_id')->where('type','expense');
    }
    public function advances()
    {
        return $this->hasMany(Transaction::class, 'driver_id')->where('type','advance');
    }
    public function driverPayrolls(): HasMany
    {
        return $this->hasMany(DriverPayroll::class);
    }

    /**
     * Get platform order items for this driver
     */
    public function platformOrderItems(): HasMany
    {
        return $this->hasMany(PlatformOrderItem::class);
    }

    /**
     * Get platform orders through order items
     */
    public function platformOrders()
    {
        return $this->hasManyThrough(
            PlatformOrder::class,
            PlatformOrderItem::class,
            'driver_id', // Foreign key on platform_order_items table
            'id', // Foreign key on platform_orders table
            'id', // Local key on drivers table
            'platform_order_id' // Local key on platform_order_items table
        );
    }

    /**
     * Get the vehicle that belongs to the driver
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the group that belongs to the driver
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the company that belongs to the driver
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all transactions for this driver
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'driver_id');
    }



    public function getActivePayrollsAttribute()
    {
        return $this->driverPayrolls()->whereHas('payrollRun', function ($query) {
            $query->whereIn('status', ['draft', 'approved']);
        });
    }

    public function getTotalEarningsAttribute()
    {
        return $this->driverPayrolls()->sum('total_earnings');
    }

    public function getTotalDeductionsAttribute()
    {
        return $this->driverPayrolls()->sum('total_deductions');
    }

    public function getNetSalaryAttribute()
    {
        return $this->driverPayrolls()->sum('net_salary');
    }
}
