<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_teachers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('specialty')->nullable();
            $table->integer('max_weekly_hours')->default(40);
            $table->string('phone_number')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('school_id')->constrained(); // Added school_id
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['email', 'school_id']); // Modified to be unique per school
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_teachers');
    }
};