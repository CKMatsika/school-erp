<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCapexProjectsTable extends Migration
{
    public function up()
    {
        Schema::create('capex_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools');
            $table->foreignId('budget_id')->constrained('budgets');
            $table->string('name');
            $table->string('project_code')->unique();
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('expected_completion_date');
            $table->date('actual_completion_date')->nullable();
            $table->decimal('total_budget', 15, 2);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->decimal('remaining_budget', 15, 2)->default(0);
            $table->enum('status', ['planned', 'in_progress', 'on_hold', 'completed', 'cancelled'])->default('planned');
            $table->foreignId('project_manager_id')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('capex_projects');
    }
}