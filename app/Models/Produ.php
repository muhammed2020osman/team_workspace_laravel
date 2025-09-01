<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Produ extends Model
{
    use Filterable;

    protected $table = 'produ';
    protected $guarded = [];
    
    // العلاقات المقترحة تلقائياً
    /**
     * علاقة belongsTo مع Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
    

    /**
     * علاقة belongsTo مع Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    

    /**
     * علاقة belongsTo مع Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    

    /**
     * علاقة belongsTo مع Car
     */
    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }
    
    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}