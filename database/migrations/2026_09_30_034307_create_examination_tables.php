<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('grade_criteria')->nullable();
            $table->json('result_thresholds')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'academic_session_id', 'name']);
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('exam_center');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('exam_type')->default('marks');
            $table->json('grade_criteria')->nullable();
            $table->json('result_thresholds')->nullable();
            $table->json('settings')->nullable();
            $table->string('admit_roll_prefix')->nullable();
            $table->unsignedInteger('admit_roll_start')->default(1);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->index(['school_id', 'academic_session_id', 'school_class_id']);
        });

        Schema::create('exam_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->unique(['exam_id', 'section_id']);
        });

        Schema::create('exam_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('subject_name');
            $table->string('subject_type')->nullable();
            $table->decimal('maximum_marks', 8, 2)->nullable();
            $table->string('paper_code')->nullable();
            $table->date('paper_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('room_number')->nullable();
            $table->timestamps();
            $table->unique(['exam_id', 'subject_id']);
        });

        Schema::create('exam_admit_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('exam_roll_number');
            $table->timestamps();
            $table->unique(['exam_id', 'student_id']);
            $table->unique(['exam_id', 'exam_roll_number']);
        });

        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->text('teacher_remark')->nullable();
            $table->text('school_remark')->nullable();
            $table->timestamps();
            $table->unique(['exam_id', 'student_id']);
        });

        Schema::create('exam_result_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_result_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_paper_id')->constrained()->cascadeOnDelete();
            $table->decimal('obtained_marks', 8, 2)->nullable();
            $table->string('grade')->nullable();
            $table->string('status')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();
            $table->unique(['exam_result_id', 'exam_paper_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_result_items');
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('exam_admit_cards');
        Schema::dropIfExists('exam_papers');
        Schema::dropIfExists('exam_sections');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_groups');
    }
};
