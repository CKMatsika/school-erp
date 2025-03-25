<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
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
    }

    public function down()
    {
        Schema::dropIfExists('hostel_houses');
    }
};