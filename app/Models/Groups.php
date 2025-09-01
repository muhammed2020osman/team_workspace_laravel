<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Groups extends Model
{
    use Filterable;

    protected $table = 'groups';
    protected $guarded = ['id'];

    // العلاقات المقترحة تلقائياً

    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}