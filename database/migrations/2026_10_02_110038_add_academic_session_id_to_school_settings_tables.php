<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First get an active session or default session for existing records
        $defaultSessionId = DB::table('academic_sessions')->value('id');

        Schema::table('student_types', function (Blueprint $table) use ($defaultSessionId) {
            $table->foreignId('academic_session_id')->default($defaultSessionId ?? 1)->constrained()->cascadeOnDelete();
            $table->index('school_id'); // Add explicit index for FK before dropping unique
            $table->dropUnique(['school_id', 'name']);
            $table->unique(['school_id', 'academic_session_id', 'name']);
        });

        Schema::table('student_categories', function (Blueprint $table) use ($defaultSessionId) {
            $table->foreignId('academic_session_id')->default($defaultSessionId ?? 1)->constrained()->cascadeOnDelete();
            $table->index('school_id');
            $table->dropUnique(['school_id', 'name']);
            $table->unique(['school_id', 'academic_session_id', 'name'], 'student_categories_school_session_name_unique');
        });

        Schema::table('payment_methods', function (Blueprint $table) use ($defaultSessionId) {
            $table->foreignId('academic_session_id')->default($defaultSessionId ?? 1)->constrained()->cascadeOnDelete();
            $table->index('school_id');
            $table->dropUnique(['school_id', 'name']);
            $table->unique(['school_id', 'academic_session_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'academic_session_id', 'name']);
            $table->unique(['school_id', 'name']);
            $table->dropForeign(['academic_session_id']);
            $table->dropColumn('academic_session_id');
        });

        Schema::table('student_categories', function (Blueprint $table) {
            $table->dropUnique('student_categories_school_session_name_unique');
            $table->unique(['school_id', 'name']);
            $table->dropForeign(['academic_session_id']);
            $table->dropColumn('academic_session_id');
        });

        Schema::table('student_types', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'academic_session_id', 'name']);
            $table->unique(['school_id', 'name']);
            $table->dropForeign(['academic_session_id']);
            $table->dropColumn('academic_session_id');
        });
    }
};
