<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class PayrollRuns extends Model
{
    use Filterable;

    protected $table = 'payroll_runs';
    protected $guarded = ['id'];

    // العلاقات المقترحة تلقائياً

    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}