<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    use Filterable;

    protected $table = 'messages';
    protected $guarded = ['id'];
    
    // العلاقات المقترحة تلقائياً
    

    /**
     * علاقة belongsTo مع Channel
     */
    public function channel()
    {
        return $this->belongsTo(Channels::class, 'channel_id');
    }
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
     * علاقة belongsTo مع User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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