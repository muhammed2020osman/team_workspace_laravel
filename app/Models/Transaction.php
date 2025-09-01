<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use Filterable;

    protected $table = 'transactions';
    protected $guarded = ['id'];

    // العلاقات المقترحة تلقائياً


    /**
     * علاقة belongsTo مع Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    

    /**
     * علاقة belongsTo مع FromAccount
     */
    public function from_account()
    {
        return $this->belongsTo(Accounts::class, 'from_account_id');
    }
    /**
     * علاقة belongsTo مع Driver
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
    /**
     * علاقة belongsTo مع Vehicle
     */
    public function category()
    {
        return $this->belongsTo(Expensecategories::class, 'category_id');
    }  
    public function vehicle()
    {
        return $this->belongsTo(Vehicles::class, 'vehicle_id');
    }
    
    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}