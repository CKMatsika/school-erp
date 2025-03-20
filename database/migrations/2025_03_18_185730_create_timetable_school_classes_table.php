<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('level');
            $table->integer('capacity')->nullable();
            $table->string('home_room')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('school_id')->constrained(); // Added school_id
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_school_classes');
    }
};