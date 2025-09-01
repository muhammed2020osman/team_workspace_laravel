<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    use Filterable;

    protected $table = 'dealer';
    protected $guarded = ['id'];
    
    // مودل تم إنشاؤه تلقائياً للعلاقات
    // يمكن إضافة حقول وعلاقات حسب الحاجة
}