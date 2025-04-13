<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: This migration assumes the 'schools' and 'account_types' tables
     * already exist before this migration is run.
     */
    public function up(): void
    {
        // The --create=accounts flag should have generated this line:
        Schema::create('accounts', function (Blueprint $table) {
            $table->id(); // Primary key

            // Foreign key linking to the school (assuming multi-tenant)
            $table->foreignId('school_id')
                  ->constrained('schools') // Assumes 'schools' table exists
                  ->onDelete('cascade'); // Delete accounts if school is deleted

            // Foreign key linking to the account type
            $table->foreignId('account_type_id')
                  ->constrained('account_types') // Assumes 'account_types' table exists
                  ->onDelete('restrict'); // Prevent deleting type if accounts use it

            $table->string('name'); // The human-readable name of the account (e.g., "Cash", "Accounts Receivable")
            $table->string('account_code')->nullable()->unique(); // Optional standard accounting code (e.g., 1010, 4000) - make unique if used
            $table->text('description')->nullable(); // Optional longer description of the account's purpose
            $table->boolean('is_active')->default(true); // Flag to activate/deactivate the account
            $table->timestamps(); // Adds created_at and updated_at columns

            // Optional: Add indices for performance on foreign keys
            $table->index('school_id');
            $table->index('account_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         // The --create=accounts flag should have generated this line:
        Schema::dropIfExists('accounts');
    }
};