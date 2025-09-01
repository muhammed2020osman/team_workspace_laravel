<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class DaylayReports extends Model
{
    use Filterable;

    protected $table = 'daylay_reports';
    protected $guarded = ['id'];
    protected $casts = [
        'data' => 'array'
    ];

    // العلاقات المقترحة تلقائياً

    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}