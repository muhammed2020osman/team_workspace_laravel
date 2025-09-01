<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('driver_payrolls', function (Blueprint $table) {
            // إضافة حقول المنصرفات والسلفيات
            $table->decimal('expenses_sum', 10, 2)->default(0)->after('total_deductions');
            $table->decimal('advances_sum', 10, 2)->default(0)->after('expenses_sum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_payrolls', function (Blueprint $table) {
            // حذف الحقول المُضافة
            $table->dropColumn(['expenses_sum', 'advances_sum']);
        });
    }
};
