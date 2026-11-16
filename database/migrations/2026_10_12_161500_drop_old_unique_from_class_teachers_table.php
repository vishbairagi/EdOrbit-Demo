<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $indexes = DB::select("
                SELECT index_name
                FROM information_schema.STATISTICS
                WHERE table_schema = DATABASE()
                  AND table_name = 'ed_class_teachers'
                  AND index_name LIKE '%session_id_class_id_staff_id_unique%'
            ");

            foreach ($indexes as $idx) {
                $name = $idx->INDEX_NAME ?? $idx->index_name ?? null;
                if ($name) {
                    DB::statement("ALTER TABLE `ed_class_teachers` DROP INDEX `{$name}`");
                    break;
                }
            }
        }
    }

    public function down(): void
    {
        // Non-destructive down
    }
};
