<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Channels extends Model
{
    use Filterable;

    protected $table = 'channels';
    protected $guarded = ['id'];
    protected $casts = [
        'messages' => 'array',
        'memberIds' => 'array'
    ];
    
    // العلاقات المقترحة تلقائياً
    

    /**
     * علاقة belongsTo مع Workspace
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }
    

    /**
     * علاقة belongsTo مع Section
     */
    public function section()
    {
        return $this->belongsTo(Sections::class, 'section_id');
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