<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Expensecategories extends Model
{
    use Filterable;

    protected $table = 'expensecategories';
    protected $guarded = ['id'];
    protected $appends=['main_cat'];

    // العلاقات المقترحة تلقائياً
 

    /**
     * التصنيفات الفرعية (الأبناء)
     */
    public function subcategories()
    {
        return $this->hasMany(Expensecategories::class, 'parent_id');
    }

    /**
     * علاقة belongsTo مع Parent
     */
    public function parent()
    {
        return $this->belongsTo(Expensecategories::class, 'parent_id');
    }
    public function getMainCatAttribute()
    {
        if($this->parent_id == 0 || $this->parent_id == null)
        return  true;
    return false;
    }
    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}
