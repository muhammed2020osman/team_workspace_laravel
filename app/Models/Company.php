<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use Filterable;

    protected $table = 'companies';
    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'description',
        'contact_email',
        'contact_phone',
        'address',
        'website',
        'industry',
        'employee_count',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'employee_count' => 'integer',
    ];

    /**
     * Get the drivers that belong to this company
     */
    public function drivers()
    {
        return $this->hasMany(Driver::class, 'company_id');
    }

    /**
     * Get the groups that belong to this company
     */
    public function groups()
    {
        return $this->hasMany(Group::class, 'company_id');
    }

    /**
     * Get the vehicles that belong to this company
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'company_id');
    }

    /**
     * Get the transactions that belong to this company
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'company_id');
    }

    /**
     * Get the payroll runs that belong to this company
     */
    public function payrollRuns()
    {
        return $this->hasMany(PayrollRun::class, 'company_id');
    }
}
