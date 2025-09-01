<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Companies extends Model
{
    use Filterable;

    protected $table = 'companies';
    protected $guarded = ['id'];
    
    // العلاقات المقترحة تلقائياً
    

    /**
     * علاقة belongsTo مع Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    
    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}