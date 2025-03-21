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
        Schema::create('fee_structure_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_structure_id')->constrained('fee_structures')->onDelete('cascade');
            $table->foreignId('fee_category_id')->constrained('fee_categories');
            $table->string('grade_level')->nullable(); // For grade-specific fees
            $table->decimal('amount', 15, 2);
            $table->string('frequency'); // once, term, monthly, annually
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_structure_items');
    }
};