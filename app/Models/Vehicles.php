<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class Vehicles extends Model
{
    use Filterable;
    
    protected $table = 'vehicles';
    protected $guarded = ['id'];
    public $timestamps = false;
    
    // العلاقات المقترحة تلقائياً
    /**
     * علاقة belongsTo مع Manufacturer
     */
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }
    
    /**
     * علاقة belongsTo مع Dealer
     */
    public function dealer()
    {
        return $this->belongsTo(Dealer::class, 'dealer_id');
    }
    

    /**
     * علاقة belongsTo مع Driver
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
    
    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}