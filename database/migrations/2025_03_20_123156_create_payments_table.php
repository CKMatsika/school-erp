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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools');
            $table->foreignId('contact_id')->constrained('accounting_contacts');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices');
            $table->string('payment_number')->unique();
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->string('reference')->nullable();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->string('status'); // pending, completed, failed, refunded
            $table->foreignId('account_id')->constrained('chart_of_accounts'); // Deposit account
            $table->string('pos_terminal_id')->nullable(); // For POS integration
            $table->string('transaction_id')->nullable(); // For payment gateway reference
            $table->boolean('is_pos_transaction')->default(false);
            $table->string('payment_receipt')->nullable(); // For receipt upload or generation
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};