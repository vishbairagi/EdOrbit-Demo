<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_books', function (Blueprint $table) {
            // 'individual' = no class assignment, 'class_wise' = assigned to classes
            $table->enum('book_type', ['individual', 'class_wise'])->default('individual')->after('school_id');
            // JSON arrays of class IDs and subject IDs
            $table->json('class_ids')->nullable()->after('book_type');
            $table->json('subject_ids')->nullable()->after('class_ids');
        });
    }

    public function down(): void
    {
        Schema::table('library_books', function (Blueprint $table) {
            $table->dropColumn(['book_type', 'class_ids', 'subject_ids']);
        });
    }
};
