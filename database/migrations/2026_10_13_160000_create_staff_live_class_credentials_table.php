<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_live_class_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('google_client_id', 500)->nullable();
            $table->string('google_client_secret', 500)->nullable();
            $table->string('google_redirect_uri', 500)->nullable();
            $table->text('google_access_token')->nullable();
            $table->text('google_refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('is_connected')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'academic_session_id', 'staff_id'], 'school_session_staff_cred_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_live_class_credentials');
    }
};
