<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Prou extends Model
{
    use Filterable;

    protected $table = 'prou';
    protected $guarded = [];
    
    // العلاقات المقترحة تلقائياً
    /**
     * علاقة belongsTo مع Car
     */
    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }
    
    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}