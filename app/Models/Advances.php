<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Advances extends Model
{
    use Filterable;

    protected $table = 'advances';
    protected $guarded = ['id'];

    // العلاقات المقترحة تلقائياً


    /**
     * علاقة belongsTo مع Driver
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
    
    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}