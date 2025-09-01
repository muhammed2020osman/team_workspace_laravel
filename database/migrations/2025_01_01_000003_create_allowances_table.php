<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allowances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_payroll_id')->constrained('driver_payrolls')->onDelete('cascade');
            $table->string('name'); // بدل إنترنت شهري
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('type', ['monthly', 'daily', 'one_time'])->default('monthly');
            $table->timestamps();

            $table->index(['driver_payroll_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allowances');
    }
};
