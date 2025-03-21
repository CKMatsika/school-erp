<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReceiptDetailsToPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('receipt_number')->nullable()->after('payment_number');
            $table->foreignId('pos_terminal_id')->nullable()->after('receipt_number')->constrained('pos_terminals');
            $table->foreignId('pos_session_id')->nullable()->after('pos_terminal_id')->constrained('pos_sessions');
            $table->string('cashier_name')->nullable()->after('pos_session_id');
            $table->enum('receipt_type', ['standard', 'duplicate', 'proforma', 'credit_note'])->nullable()->after('cashier_name');
            $table->boolean('is_printed')->default(false)->after('receipt_type');
            $table->timestamp('printed_at')->nullable()->after('is_printed');
            $table->string('customer_copy_given')->nullable()->after('printed_at');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['pos_terminal_id']);
            $table->dropForeign(['pos_session_id']);
            $table->dropColumn([
                'receipt_number',
                'pos_terminal_id',
                'pos_session_id',
                'cashier_name',
                'receipt_type',
                'is_printed',
                'printed_at',
                'customer_copy_given'
            ]);
        });
    }
}