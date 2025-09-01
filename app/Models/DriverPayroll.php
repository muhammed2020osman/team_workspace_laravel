<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DriverPayroll extends Model
{
    use Filterable;

    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'base_salary' => 'decimal:2',
        'target_bonus' => 'decimal:2',
        'working_days_actual' => 'integer',
        'absence_days' => 'integer',
        'overtime_hours' => 'integer',
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'expenses_sum' => 'decimal:2',
        'advances_sum' => 'decimal:2',
        'payment_date' => 'datetime'
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(Allowance::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(Deduction::class);
    }

    public function manualAdjustments(): HasMany
    {
        return $this->hasMany(ManualAdjustment::class);
    }

    public function calculateTotalEarnings(): float
    {
        $baseSalary = $this->base_salary ?? 0;
        $targetBonus = $this->target_bonus ?? 0;
        $allowancesTotal = $this->allowances()->sum('amount');
        $manualAdditions = $this->manualAdjustments()
            ->where('type', 'addition')
            ->sum('amount');

        return $baseSalary + $targetBonus + $allowancesTotal + $manualAdditions;
    }

    public function calculateTotalDeductions(): float
    {
        $deductionsTotal = $this->deductions()->sum('amount');
        $manualSubtractions = $this->manualAdjustments()
            ->where('type', 'subtraction')
            ->sum('amount');

        return $deductionsTotal + $manualSubtractions;
    }

    public function calculateNetSalary(): float
    {
        $totalEarnings = $this->calculateTotalEarnings();
        $totalDeductions = $this->calculateTotalDeductions();

        return $totalEarnings - $totalDeductions;
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeByDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeByPayrollRun($query, $payrollRunId)
    {
        return $query->where('payroll_run_id', $payrollRunId);
    }
}
