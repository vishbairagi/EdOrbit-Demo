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
        Schema::create('class_timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('day', 20); // Monday, Tuesday, etc.
            $table->string('start_time', 20); // 08:00 or 08:00 AM
            $table->string('end_time', 20); // 09:00 or 09:00 AM
            $table->string('room_number', 50)->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_session_id', 'school_class_id', 'section_id'], 'class_timetable_lookup_idx');
            $table->index(['school_id', 'academic_session_id', 'day'], 'class_timetable_day_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_timetables');
    }
};
