<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use Filterable;

    protected $table = 'orders';
    protected $guarded = [];
    
    // العلاقات المقترحة تلقائياً
    /**
     * علاقة belongsTo مع Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    /**
     * علاقة belongsTo مع Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    
    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}