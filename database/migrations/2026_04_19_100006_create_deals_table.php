<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pipeline_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('pipeline_stages')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->unsignedTinyInteger('probability')->default(0);
            $table->date('expected_close_date')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('status', 16)->default('open');
            $table->string('lost_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'stage_id']);
            $table->index(['account_id', 'owner_id']);
            $table->index(['account_id', 'status']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreign('converted_deal_id')->references('id')->on('deals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['converted_deal_id']);
        });

        Schema::dropIfExists('deals');
    }
};
