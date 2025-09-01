<?php
namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use Filterable;

    protected $table = 'commissions';
    protected $guarded = ['id'];
}
