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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools');
            $table->foreignId('contact_id')->constrained('accounting_contacts');
            $table->string('invoice_number')->unique();
            $table->string('reference')->nullable();
            $table->date('issue_date');
            $table->date('due_date');
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->string('status'); // draft, sent, partial, paid, cancelled
            $table->string('type')->default('standard'); // standard, recurring, fee
            $table->foreignId('created_by')->constrained('users');
            $table->string('fee_schedule_id')->nullable(); // For student fee invoices
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};