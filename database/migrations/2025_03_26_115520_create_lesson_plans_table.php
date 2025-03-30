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
        Schema::disableForeignKeyConstraints();

        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('class_id');
            $table->foreignId('scheme_of_work_id')->nullable()->constrained('scheme_of_works')->onDelete('set null');
            $table->string('title');
            $table->text('objectives');
            $table->text('resources')->nullable();
            $table->text('activities');
            $table->text('assessment')->nullable();
            $table->date('lesson_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['draft', 'ready', 'conducted', 'cancelled'])->default('draft');
            $table->text('reflection')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add foreign keys with explicit references to the correct schemas
            $table->foreign('subject_id')->references('id')->on('school_timetable.subjects')->onDelete('cascade');
            
            // Assuming classes are also in school_timetable schema
            // If they're in a different schema, update this accordingly
            $table->foreign('class_id')->references('id')->on('school_timetable.classes')->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_plans');
    }
};