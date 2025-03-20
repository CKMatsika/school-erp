<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('color_code', 7)->default('#3490dc');
            $table->boolean('active')->default(true);
            $table->foreignId('school_id')->constrained(); // Added school_id
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['code', 'school_id']); // Modified to be unique per school
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_subjects');
    }
};