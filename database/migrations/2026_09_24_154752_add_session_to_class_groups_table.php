<?php

use App\Models\AcademicSession;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_groups', function (Blueprint $table) {
            $table->foreignId('academic_session_id')->nullable()->after('school_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('class_groups', function (Blueprint $table) {
            $table->dropForeignIdFor(AcademicSession::class);
            $table->dropColumn('academic_session_id');
        });
    }
};
