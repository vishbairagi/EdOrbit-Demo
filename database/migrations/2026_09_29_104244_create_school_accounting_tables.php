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
        Schema::create('fee_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->unsignedTinyInteger('duration_months');
            $table->timestamps();
            $table->unique(['school_id', 'academic_session_id', 'name']);
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['school_id', 'name']);
        });

        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_period_id')->constrained()->restrictOnDelete();
            $table->string('name', 150);
            $table->string('student_type', 30)->default('all');
            $table->decimal('amount', 12, 2);
            $table->boolean('invoice_on_admission')->default(false);
            $table->boolean('include_on_promotion')->default(false);
            $table->boolean('dashboard_disabled')->default(false);
            $table->timestamps();
            $table->index(['school_id', 'academic_session_id']);
        });

        Schema::create('fee_type_school_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->unique(['fee_type_id', 'school_class_id']);
        });

        Schema::create('fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_title', 150);
            $table->text('description')->nullable();
            $table->boolean('allow_partial_payment')->default(false);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->nullable();
            $table->date('issued_at');
            $table->date('due_at')->nullable();
            $table->string('status', 20)->default('unpaid');
            $table->timestamps();
            $table->index(['school_id', 'academic_session_id', 'status']);
            $table->index(['student_id', 'academic_session_id']);
        });

        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('transaction_id', 150)->nullable();
            $table->string('authorized_by', 150);
            $table->date('paid_on');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'paid_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('fee_invoices');
        Schema::dropIfExists('fee_type_school_class');
        Schema::dropIfExists('fee_types');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('fee_periods');
    }
};
