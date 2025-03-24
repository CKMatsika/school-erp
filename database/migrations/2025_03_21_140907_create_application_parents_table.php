<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('application_parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('relationship', ['father', 'mother', 'guardian', 'other']);
            $table->string('email')->nullable();
            $table->string('phone_primary');
            $table->string('phone_secondary')->nullable();
            $table->text('address')->nullable();
            $table->string('occupation')->nullable();
            $table->boolean('is_primary_contact')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('application_parents');
    }
};