<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools');
            $table->string('application_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('other_names')->nullable();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('photo')->nullable();
            $table->foreignId('applying_for_class_id')->constrained('timetable_school_classes');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->enum('status', ['submitted', 'under_review', 'pending_documents', 'interview_scheduled', 'accepted', 'rejected', 'waitlisted', 'enrolled'])->default('submitted');
            $table->boolean('is_boarder')->default(false);
            $table->date('submission_date');
            $table->date('decision_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('applications');
    }
};