<?php 
namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
class Tier extends Model
{
    use Filterable;

    protected $table = 'tiers';
    protected $guarded = ['id'];
     
    // العلاقات المقترحة تلقائياً
   
    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}
