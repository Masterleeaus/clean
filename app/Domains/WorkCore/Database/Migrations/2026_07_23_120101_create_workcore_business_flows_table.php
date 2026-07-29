<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workcore_business_flows', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('flow_type', 120);
            $table->string('idempotency_key', 190);
            $table->string('status', 32)->default('running');
            $table->json('request');
            $table->json('result')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'idempotency_key'], 'workcore_flows_company_idempotency_unique');
            $table->index(['company_id', 'flow_type', 'status'], 'workcore_flows_company_type_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workcore_business_flows');
    }
};
