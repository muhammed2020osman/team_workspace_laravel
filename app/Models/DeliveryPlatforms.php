<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class DeliveryPlatforms extends Model
{
    use Filterable;

    protected $table = 'delivery_platforms';
    protected $guarded = ['id'];
    public static $relationFields = [
        'commissionModel' => 'commissions',
    ];
    // العلاقات المقترحة تلقائياً
    public function tiers()
    {
        return $this->hasMany(Tier::class, 'delivery_platform_id');
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
