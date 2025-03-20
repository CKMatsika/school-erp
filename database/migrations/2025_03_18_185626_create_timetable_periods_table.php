<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('description')->nullable();
            $table->boolean('is_break')->default(false);
            $table->integer('order')->default(0);
            $table->boolean('active')->default(true);
            $table->foreignId('school_id')->constrained(); // Added school_id
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_periods');
    }
};