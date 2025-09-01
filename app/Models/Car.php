<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use Filterable;

    protected $table = 'car';
    protected $guarded = ['id'];
    
    // مودل تم إنشاؤه تلقائياً للعلاقات
    // يمكن إضافة حقول وعلاقات حسب الحاجة
}