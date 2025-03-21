<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgeAnalysisSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('age_analysis_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools');
            $table->string('name');
            $table->boolean('is_student_specific')->default(false);
            $table->integer('current_days')->default(30);
            $table->integer('period_1_days')->default(60);
            $table->integer('period_2_days')->default(90);
            $table->integer('period_3_days')->default(120);
            $table->string('period_1_label')->default('31-60 Days');
            $table->string('period_2_label')->default('61-90 Days');
            $table->string('period_3_label')->default('91-120 Days');
            $table->string('period_4_label')->default('120+ Days');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('age_analysis_settings');
    }
}