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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools');
            $table->foreignId('template_id')->nullable()->constrained('notification_templates');
            $table->string('event');
            $table->string('recipient_type'); // contact, student, etc.
            $table->unsignedBigInteger('recipient_id');
            $table->string('channel'); // email, sms, whatsapp
            $table->string('to'); // email, phone number
            $table->text('subject')->nullable();
            $table->text('content');
            $table->string('status'); // sent, failed, pending
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};