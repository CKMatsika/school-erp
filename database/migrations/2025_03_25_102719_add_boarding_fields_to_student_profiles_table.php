<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
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

    public function down()
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('student_profiles', 'current_hostel_allocation_id')) {
                $table->dropColumn('current_hostel_allocation_id');
            }
            if (Schema::hasColumn('student_profiles', 'boarding_status')) {
                $table->dropColumn('boarding_status');
            }
            if (Schema::hasColumn('student_profiles', 'is_boarder')) {
                $table->dropColumn('is_boarder');
            }
        });
    }
};