<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Financialaccounts extends Model
{
    use Filterable;

    protected $table = 'financialaccounts';
    protected $guarded = ['id'];

    // العلاقات المقترحة تلقائياً

    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}