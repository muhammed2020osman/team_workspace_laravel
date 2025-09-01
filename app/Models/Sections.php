<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Sections extends Model
{
    use Filterable;

    protected $table = 'sections';
    protected $guarded = ['id'];
    
    // العلاقات المقترحة تلقائياً
    

    /**
     * علاقة belongsTo مع Workspace
     */

    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }
    public function channels(){
        return $this->hasMany(Channels::class,'section_id');
    }
    

    /**
     * علاقة belongsTo مع Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    /**
     * علاقة belongsTo مع Workspaces
     */
    public function workspaces()
    {
        return $this->belongsTo(Workspaces::class, 'workspaces_id');
    }
    
    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}