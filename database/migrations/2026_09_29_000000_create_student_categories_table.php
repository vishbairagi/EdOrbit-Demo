<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->timestamps();
            $table->unique(['school_id', 'name']);
        });

        $timestamp = now();
        foreach (DB::table('schools')->pluck('id') as $schoolId) {
            $categories = array_map(fn (string $name): array => [
                'school_id' => $schoolId,
                'name' => $name,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], ['General', 'OBC', 'SC', 'ST', 'Other']);

            DB::table('student_categories')->insert($categories);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_categories');
    }
};