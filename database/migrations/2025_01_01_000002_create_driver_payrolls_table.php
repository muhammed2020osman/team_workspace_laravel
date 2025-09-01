<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->string('driver_name');
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('target_bonus', 10, 2)->default(0);
            $table->integer('working_days_actual')->default(26);
            $table->integer('absence_days')->default(0);
            $table->integer('overtime_hours')->default(0);
            $table->decimal('total_earnings', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('payment_date')->nullable();
            $table->string('bank_transaction_id')->nullable();
            $table->text('payment_note')->nullable();
            $table->timestamps();

            $table->index(['payroll_run_id', 'driver_id']);
            $table->index(['payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_payrolls');
    }
};
