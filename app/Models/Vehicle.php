<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use Filterable;

    protected $table = 'vehicles';
    protected $guarded = ['id'];




    protected $casts = [
        'insurance_expiry' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
