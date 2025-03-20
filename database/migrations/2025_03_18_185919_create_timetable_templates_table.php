<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('academic_year');
            $table->string('term')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('days_of_week')->default('[1,2,3,4,5]');
            $table->foreignId('school_id')->constrained(); // Added school_id
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_templates');
    }
};