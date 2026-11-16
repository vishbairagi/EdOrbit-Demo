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
        Schema::create('student_trashes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->unsignedBigInteger('academic_session_id')->nullable()->index();
            $table->unsignedBigInteger('student_id');
            $table->string('admission_no')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('school_class_name')->nullable();
            $table->string('section_name')->nullable();
            $table->string('photo')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->longText('student_data');
            $table->longText('related_data');
            $table->json('related_counts')->nullable();
            $table->timestamp('deleted_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_trashes');
    }
};
