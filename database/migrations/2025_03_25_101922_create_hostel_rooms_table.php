<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
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
    }

    public function down()
    {
        Schema::dropIfExists('hostel_rooms');
    }
};