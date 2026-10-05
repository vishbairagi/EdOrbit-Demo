<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('employee_id')->nullable();

            // Personal Details
            $table->string('name');
            $table->enum('gender', ['Male', 'Female', 'Other'])->default('Male');
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('photo')->nullable();

            // Joining Details
            $table->date('joining_date')->nullable();
            $table->enum('role', ['Principal', 'Headmaster', 'Admin'])->default('Admin');
            $table->text('note')->nullable();

            // Government Documents
            $table->json('govt_documents')->nullable(); // [{type, method, value/path}]

            // Salary
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('salary_type')->nullable(); // Monthly, Weekly, etc.
            $table->json('salary_details')->nullable();

            // Auth
            $table->string('username')->unique();
            $table->string('login_email')->nullable()->unique();
            $table->string('login_phone')->nullable()->unique();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('email_verified_at')->nullable();

            // Status
            $table->enum('status', ['Active', 'Inactive'])->default('Active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_users');
    }
};
