<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    use Filterable;

    use HasFactory;

    protected $fillable = [
        'period',
        'period_name',
        'working_days',
        'status',
        'summary'
    ];

    protected $casts = [
        'summary' => 'array',
        'working_days' => 'integer'
    ];

    public function driverPayrolls(): HasMany
    {
        return $this->hasMany(DriverPayroll::class);
    }

    public function getTotalDriversAttribute(): int
    {
        return $this->driverPayrolls()->count();
    }

    public function getTotalNetSalariesAttribute(): float
    {
        return $this->driverPayrolls()->sum('net_salary');
    }

    public function getTotalWorkingDaysAttribute(): int
    {
        return $this->working_days * $this->driverPayrolls()->count();
    }

    public function getTotalAbsenceDaysAttribute(): int
    {
        return $this->driverPayrolls()->sum('absence_days');
    }

    public function getTotalOvertimeHoursAttribute(): int
    {
        return $this->driverPayrolls()->sum('overtime_hours');
    }

    public function getTotalAllowancesAttribute(): float
    {
        return $this->driverPayrolls()->with('allowances')->get()->sum(function ($driverPayroll) {
            return $driverPayroll->allowances->sum('amount');
        });
    }

    public function getTotalDeductionsAttribute(): float
    {
        return $this->driverPayrolls()->with('deductions')->get()->sum(function ($driverPayroll) {
            return $driverPayroll->deductions->sum('amount');
        });
    }

    public function getTotalTargetBonusAttribute(): float
    {
        return $this->driverPayrolls()->sum('target_bonus');
    }

    public function getTotalExpensesAttribute(): float
    {
        return $this->driverPayrolls()->sum('expenses_sum');
    }

    public function getTotalAdvancesAttribute(): float
    {
        return $this->driverPayrolls()->sum('advances_sum');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['draft', 'approved']);
    }
}
