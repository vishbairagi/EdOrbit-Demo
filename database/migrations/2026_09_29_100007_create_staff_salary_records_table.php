<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_salary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->string('month'); // e.g. "2026-09"
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('allowances', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->enum('payment_status', ['Pending', 'Paid', 'Partial'])->default('Pending');
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('note')->nullable();
            $table->json('details')->nullable(); // breakdown
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_salary_records');
    }
};
