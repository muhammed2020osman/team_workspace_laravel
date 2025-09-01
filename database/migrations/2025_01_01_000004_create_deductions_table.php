<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_payroll_id')->constrained('driver_payrolls')->onDelete('cascade');
            $table->string('name'); // خصم تأخير، خصم غياب
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('type', ['fixed', 'percentage', 'daily'])->default('fixed');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['driver_payroll_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
