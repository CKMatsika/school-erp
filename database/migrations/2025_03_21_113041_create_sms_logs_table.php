<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsLogsTable extends Migration
{
    public function up()
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools');
            $table->foreignId('sms_gateway_id')->constrained('sms_gateways');
            $table->foreignId('sms_template_id')->nullable()->constrained('sms_templates');
            $table->string('recipient_number');
            $table->string('recipient_name')->nullable();
            $table->foreignId('student_id')->nullable()->constrained('students');
            $table->string('message_id')->nullable();
            $table->text('message_content');
            $table->enum('related_to', ['invoice', 'payment', 'receipt', 'reminder', 'general', 'fee', 'custom'])->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed'])->default('queued');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_logs');
    }
}