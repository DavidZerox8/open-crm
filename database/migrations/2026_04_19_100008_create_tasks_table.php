<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->nullableMorphs('subject');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('priority', 16)->default('medium');
            $table->string('status', 16)->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'assigned_to', 'status'], 'tasks_account_assignee_status_idx');
            $table->index(['account_id', 'due_at']);
            $table->index(['account_id', 'subject_type', 'subject_id'], 'tasks_account_subject_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
