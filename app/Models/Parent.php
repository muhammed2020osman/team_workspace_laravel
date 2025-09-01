<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Parent extends Model
{
    use Filterable;

    protected $table = 'parent';
    protected $guarded = ['id'];

    // مودل تم إنشاؤه تلقائياً للعلاقات
    // يمكن إضافة حقول وعلاقات حسب الحاجة
}