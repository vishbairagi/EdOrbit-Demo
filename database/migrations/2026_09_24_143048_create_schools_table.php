<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('status')->default('active'); // active, inactive

            $table->string('enrollment_prefix')->nullable();
            $table->integer('enrollment_base_number')->default(0);
            $table->integer('enrollment_base_padding')->default(6);

            $table->string('admission_prefix')->nullable();
            $table->integer('admission_base_number')->default(1);
            $table->integer('admission_base_padding')->default(20);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
