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
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools');
            $table->string('reference');
            $table->date('journal_date');
            $table->text('description')->nullable();
            $table->string('status'); // draft, posted, reversed
            $table->foreignId('created_by')->constrained('users');
            $table->string('document_type')->nullable(); // invoice, payment, manual
            $table->unsignedBigInteger('document_id')->nullable(); // ID of related document
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};