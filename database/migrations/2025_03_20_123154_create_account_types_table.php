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
        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('description')->nullable();
            // *** ADD THIS LINE ***
            $table->enum('normal_balance', ['debit', 'credit']); // Use enum for restricted values
            $table->boolean('is_active')->default(true);
            // *** ADD THIS LINE ***
            $table->boolean('is_system')->default(false); // Default to false for user-created
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};