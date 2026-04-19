<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->nullableMorphs('subject');
            $table->string('type', 32)->default('note');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'subject_type', 'subject_id'], 'activities_account_subject_idx');
            $table->index(['account_id', 'user_id', 'occurred_at'], 'activities_account_user_occurred_idx');
            $table->index(['account_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
