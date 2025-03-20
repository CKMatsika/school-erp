<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modules table
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_core')->default(false);
            $table->json('dependencies')->nullable(); // Other modules this depends on
            $table->string('version')->default('1.0.0');
            $table->timestamps();
        });

        // School-specific module settings
        Schema::create('school_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // School-specific module settings
            $table->timestamps();
            
            // Each school can have a module only once
            $table->unique(['school_id', 'module_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_modules');
        Schema::dropIfExists('modules');
    }
};
