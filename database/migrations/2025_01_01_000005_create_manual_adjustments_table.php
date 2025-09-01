<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_payroll_id')->constrained('driver_payrolls')->onDelete('cascade');
            $table->string('name'); // تعديل خاص، مكافأة إضافية
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('type', ['addition', 'subtraction'])->default('addition');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['driver_payroll_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_adjustments');
    }
};
