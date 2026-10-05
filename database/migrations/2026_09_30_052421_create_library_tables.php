<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('subject')->nullable();
            $table->string('rack_number')->nullable();
            $table->string('book_number')->nullable();
            $table->string('isbn_number')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::create('library_book_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('student_id');
            $table->date('issue_date');
            $table->date('return_due_date');
            $table->date('actual_return_date')->nullable();
            $table->text('return_note')->nullable();
            $table->enum('status', ['issued', 'returned'])->default('issued');
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('book_id')->references('id')->on('library_books')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });

        Schema::create('library_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->string('card_number');
            $table->string('prefix')->nullable();
            $table->date('date_issued');
            $table->timestamps();

            $table->unique(['school_id', 'card_number']);
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_cards');
        Schema::dropIfExists('library_book_issues');
        Schema::dropIfExists('library_books');
    }
};
