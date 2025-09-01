<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Workspaces extends Model
{
    use Filterable;

    protected $table = 'workspaces';
    protected $guarded = ['id'];

    protected $with=['sections.channels'];
    public $custom_relations=['sections.channels'];
public function sections(){
    return $this->hasMany(Sections::class,'workspace_id');
}
public function users(){
    return $this->hasMany(User::class);
}
 
    // العلاقات المقترحة تلقائياً
    // public function posts() {
    //     return $this->hasMany(Post::class);
    // }

    /**
     * علاقة belongsTo مع Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    
    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}