<?php

namespace App\Models;

use App\Traits\Filterable;

use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    use Filterable;

    protected $table = 'accounts';
    protected $guarded = ['id'];

    /**
     * علاقة belongsTo مع Drivers
     */
    public function driver()
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }

    /**
     * علاقة belongsTo مع Companies
     */
    public function company()
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }

    /**
     * علاقة hasMany مع Transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'account_id');
    }

    /**
     * علاقة hasMany مع Transactions (كحساب مصدر)
     */
    // public function sourceTransactions()
    // {
    //     return $this->hasMany(Transactions::class, 'source_account_id');
    // }

    /**
     * علاقة hasMany مع Transactions (كحساب وجهة)
     */
    // public function destinationTransactions()
    // {
    //     return $this->hasMany(Transactions::class, 'destination_account_id');
    // }

    /**
     * Accessor للحصول على الرصيد المنسق
     */
    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 2) . ' ريال';
    }

    /**
     * Accessor لحالة الحساب النشط
     */
    public function getStatusTextAttribute()
    {
        return $this->isActive ? 'نشط' : 'غير نشط';
    }

    /**
     * Scope للحسابات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', true);
    }

    /**
     * Scope للحسابات حسب النوع
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope للحسابات حسب البنك
     */
    public function scopeOfBank($query, $bankName)
    {
        return $query->where('bank_name', $bankName);
    }
}
