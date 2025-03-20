<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_entry_id')->constrained('timetable_entries')->onDelete('cascade');
            $table->date('exception_date');
            $table->boolean('is_canceled')->default(true);
            $table->foreignId('replacement_teacher_id')->nullable()->constrained('timetable_teachers')->onDelete('set null');
            $table->string('replacement_room')->nullable();
            $table->string('reason')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['timetable_entry_id', 'exception_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_exceptions');
    }
};