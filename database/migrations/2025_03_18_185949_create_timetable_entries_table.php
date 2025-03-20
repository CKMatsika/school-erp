<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_template_id')->constrained('timetable_templates')->onDelete('cascade');
            $table->foreignId('school_class_id')->constrained('timetable_school_classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('timetable_subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('timetable_teachers')->onDelete('cascade');
            $table->foreignId('period_id')->constrained('timetable_periods')->onDelete('cascade');
            $table->tinyInteger('day_of_week');
            $table->string('room')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraints to prevent conflicts
            $table->unique(['timetable_template_id', 'school_class_id', 'period_id', 'day_of_week'], 'unique_class_slot');
            $table->unique(['timetable_template_id', 'teacher_id', 'period_id', 'day_of_week'], 'unique_teacher_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
    }
};