<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Safely drops old single-column unique index if it exists (SQLite legacy).
     * The composite unique (academic_session_id, admission_no) is already
     * defined in the create_students_table migration.
     */
    public function up(): void
    {
        // Check if the old index exists before dropping
        $exists = DB::select("
            SELECT COUNT(*) as cnt
            FROM information_schema.STATISTICS
            WHERE table_schema = DATABASE()
              AND table_name = 'ed_students'
              AND index_name = 'students_admission_no_unique'
        ");
        if ($exists[0]->cnt > 0) {
            DB::statement('ALTER TABLE `ed_students` DROP INDEX `students_admission_no_unique`');
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['academic_session_id', 'admission_no']);
            $table->unique('admission_no');
        });
    }
};
