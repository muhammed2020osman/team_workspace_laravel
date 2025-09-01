<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class DailyReports extends Model
{
    use Filterable;

    protected $table = 'daily_reports';
    protected $guarded = ['id'];
   

    // العلاقات المقترحة تلقائياً

    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}