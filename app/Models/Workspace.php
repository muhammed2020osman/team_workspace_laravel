<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use Filterable;

    protected $table = 'workspaces';
    protected $guarded = ['id'];
    protected $with=['sections.channels'];
    public $custom_relations=['sections.channels'];
public function sections(){
    return $this->hasMany(Sections::class);
}
public function users(){
    return $this->hasMany(User::class);
}
 
    // مودل تم إنشاؤه تلقائياً للعلاقات
    // يمكن إضافة حقول وعلاقات حسب الحاجة
}