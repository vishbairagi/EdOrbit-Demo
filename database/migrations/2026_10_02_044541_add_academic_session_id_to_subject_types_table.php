<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_types', function (Blueprint $table) {
            $table->foreignId('academic_session_id')
                  ->nullable()
                  ->after('school_id')
                  ->constrained('academic_sessions')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subject_types', function (Blueprint $table) {
            $table->dropForeign(['academic_session_id']);
            $table->dropColumn('academic_session_id');
        });
    }
};
