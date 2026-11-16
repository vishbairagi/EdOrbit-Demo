<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_teachers', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('class_id')->constrained()->cascadeOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            // Safely drop old unique index if it exists
            $exists = DB::select("
                SELECT COUNT(*) as cnt
                FROM information_schema.STATISTICS
                WHERE table_schema = DATABASE()
                  AND table_name = 'ed_class_teachers'
                  AND index_name = 'class_teachers_session_id_class_id_staff_id_unique'
            ");
            if (!empty($exists) && $exists[0]->cnt > 0) {
                DB::statement('ALTER TABLE `ed_class_teachers` DROP INDEX `class_teachers_session_id_class_id_staff_id_unique`');
            }

            // Add new unique with section_id
            $newExists = DB::select("
                SELECT COUNT(*) as cnt
                FROM information_schema.STATISTICS
                WHERE table_schema = DATABASE()
                  AND table_name = 'ed_class_teachers'
                  AND index_name = 'class_teachers_session_id_class_id_section_id_unique'
            ");
            if (empty($newExists) || $newExists[0]->cnt === 0) {
                Schema::table('class_teachers', function (Blueprint $table) {
                    $table->unique(['session_id', 'class_id', 'section_id']);
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('class_teachers', function (Blueprint $table) {
            $table->dropUnique('class_teachers_session_id_class_id_section_id_unique');
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
            $table->unique(['session_id', 'class_id', 'staff_id']);
        });
    }
};
