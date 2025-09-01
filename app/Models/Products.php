<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use Filterable;

    protected $table = 'products';
    protected $guarded = [];
    
    // العلاقات المقترحة تلقائياً
    
    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}