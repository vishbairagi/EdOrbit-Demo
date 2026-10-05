<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_role_id')->constrained('staff_roles')->cascadeOnDelete();
            $table->string('permission_key'); // e.g. academic.dashboard, student.list
            $table->timestamps();

            $table->unique(['staff_role_id', 'permission_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
