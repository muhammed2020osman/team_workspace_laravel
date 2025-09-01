<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Plans extends Model
{
    use Filterable;

    protected $table = 'plans';
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
     * علاقة belongsTo مع Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    
    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}