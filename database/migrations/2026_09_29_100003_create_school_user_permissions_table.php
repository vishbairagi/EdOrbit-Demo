<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_user_id')->constrained('school_users')->cascadeOnDelete();
            $table->string('permission_key');
            $table->timestamps();

            $table->unique(['school_user_id', 'permission_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_user_permissions');
    }
};
