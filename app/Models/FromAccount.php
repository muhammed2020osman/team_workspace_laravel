<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class FromAccount extends Model
{
    use Filterable;

    protected $table = 'from_account';
    protected $guarded = ['id'];

    // مودل تم إنشاؤه تلقائياً للعلاقات
    // يمكن إضافة حقول وعلاقات حسب الحاجة
}