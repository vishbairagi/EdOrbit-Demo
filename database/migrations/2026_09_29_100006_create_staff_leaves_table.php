<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();

            // Applicant - either staff or school_user (admin/principal)
            $table->unsignedBigInteger('applicant_id');
            $table->string('applicant_type'); // 'staff' or 'school_user'

            $table->date('from_date');
            $table->date('to_date');
            $table->string('leave_type')->default('Casual'); // Casual, Sick, Earned, etc.
            $table->text('reason');
            $table->enum('status', ['Pending', 'Processing', 'Approved', 'Rejected'])->default('Pending');
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_leaves');
    }
};
