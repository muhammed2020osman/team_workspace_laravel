<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    use Filterable;

    protected $table = 'customers';
    protected $guarded = ['id'];
    
    // يمكن إضافة علاقات أو طرق خاصة هنا حسب الحاجة
}