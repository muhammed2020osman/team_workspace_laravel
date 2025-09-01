<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class Drivers extends Model
{
    use Filterable;
    
    protected $table = 'drivers';
    protected $guarded = ['id'];
    protected $with = ['vehicle'];
    
    public function vehicle()
    {
        return $this->belongsTo(Vehicles::class, 'vehicle_id');
    }
    //ve العلاقات المقترحة تلقائياً


    /**
     * علاقة belongsTo مع Driver
     */
    public function expenses()
    {
        return $this->hasMany(Transaction::class, 'driver_id')->where('type','expense');
    }
    public function advances()
    {
        return $this->hasMany(Transaction::class, 'driver_id')->where('type','advance');
    }
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
    /**
     * علاقة belongsTo مع Group
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
    /**
     * علاقة belongsTo مع Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    
    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}