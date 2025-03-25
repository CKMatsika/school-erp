<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hostel_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->onDelete('cascade');
            $table->foreignId('hostel_bed_id')->constrained('hostel_beds')->onDelete('cascade');
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years');
            $table->date('allocation_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_current')->default(true);
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // A student can only have one active bed allocation at a time
            $table->unique(['student_profile_id', 'is_current'], 'unique_active_allocation');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hostel_allocations');
    }
};