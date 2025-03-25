<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for hostel houses.
     */
    public function up(): void
    {
        // Houses/Dormitories
        Schema::create('hostel_houses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('gender', ['male', 'female', 'mixed']);
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('warden_name')->nullable();
            $table->string('warden_contact')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('capacity')->default(0);
            $table->integer('floor_count')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        // Rooms in houses
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_house_id')->constrained('hostel_houses')->onDelete('cascade');
            $table->string('room_number');
            $table->string('floor')->nullable();
            $table->string('room_type')->nullable()->comment('standard, executive, etc');
            $table->integer('capacity')->default(4);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Ensure room numbers are unique within a house
            $table->unique(['hostel_house_id', 'room_number']);
        });

        // Beds in rooms
        Schema::create('hostel_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_room_id')->constrained('hostel_rooms')->onDelete('cascade');
            $table->string('bed_number');
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Ensure bed numbers are unique within a room
            $table->unique(['hostel_room_id', 'bed_number']);
        });

        // Bed allocations to students
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

        // Add boarding-related fields to student_profiles table if not already added
        Schema::table('student_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('student_profiles', 'is_boarder')) {
                $table->boolean('is_boarder')->default(false);
            }
            if (!Schema::hasColumn('student_profiles', 'boarding_status')) {
                $table->enum('boarding_status', ['pending', 'allocated', 'day_scholar'])->nullable();
            }
            if (!Schema::hasColumn('student_profiles', 'current_hostel_allocation_id')) {
                $table->foreignId('current_hostel_allocation_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns from student_profiles
        Schema::table('student_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('student_profiles', 'current_hostel_allocation_id')) {
                $table->dropColumn('current_hostel_allocation_id');
            }
            if (Schema::hasColumn('student_profiles', 'boarding_status')) {
                $table->dropColumn('boarding_status');
            }
        });

        // Drop the created tables
        Schema::dropIfExists('hostel_allocations');
        Schema::dropIfExists('hostel_beds');
        Schema::dropIfExists('hostel_rooms');
        Schema::dropIfExists('hostel_houses');
    }
};