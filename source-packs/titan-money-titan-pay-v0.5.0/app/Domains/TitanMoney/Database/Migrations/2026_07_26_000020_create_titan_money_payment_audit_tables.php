<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('titan_money_payments', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->ulid('company_id')->index(); $table->string('payer_type')->nullable(); $table->string('payer_id')->nullable(); $table->string('payment_method'); $table->char('currency_code',3)->default('AUD');
            $table->bigInteger('amount_minor'); $table->bigInteger('unallocated_minor'); $table->timestamp('received_at'); $table->string('gateway_provider')->nullable(); $table->string('gateway_account_id')->nullable(); $table->string('gateway_transaction_id')->nullable(); $table->string('bank_transaction_id')->nullable(); $table->string('reference')->nullable(); $table->string('status')->default('pending')->index();
            $table->timestamp('verified_at')->nullable(); $table->timestamp('reversed_at')->nullable(); $table->json('metadata_json')->nullable(); $table->foreignId('created_by_user_id')->nullable(); $table->string('created_by_agent_id')->nullable(); $table->string('device_id')->nullable(); $table->string('conversation_id')->nullable(); $table->ulid('correlation_id')->nullable(); $table->timestamps();
            $table->unique(['company_id','gateway_provider','gateway_transaction_id']); $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('titan_money_payment_allocations', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->ulid('payment_id'); $table->ulid('invoice_id'); $table->bigInteger('amount_minor'); $table->timestamp('allocated_at'); $table->timestamp('reversed_at')->nullable(); $table->json('metadata_json')->nullable(); $table->timestamps();
            $table->foreign('payment_id')->references('id')->on('titan_money_payments')->cascadeOnDelete(); $table->foreign('invoice_id')->references('id')->on('titan_money_invoices')->restrictOnDelete();
        });

        Schema::create('titan_money_credit_notes', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->ulid('company_id')->index(); $table->ulid('invoice_id'); $table->string('credit_number'); $table->string('status')->default('draft'); $table->char('currency_code',3)->default('AUD'); $table->bigInteger('subtotal_minor'); $table->bigInteger('tax_minor'); $table->bigInteger('total_minor'); $table->string('reason_code')->nullable(); $table->text('reason')->nullable(); $table->timestamp('issued_at')->nullable(); $table->foreignId('created_by_user_id')->nullable(); $table->json('metadata_json')->nullable(); $table->timestamps();
            $table->unique(['company_id','credit_number']); $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete(); $table->foreign('invoice_id')->references('id')->on('titan_money_invoices')->restrictOnDelete();
        });

        Schema::create('titan_money_audit_logs', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->ulid('company_id')->index(); $table->string('entity_type'); $table->string('entity_id'); $table->string('action'); $table->foreignId('actor_user_id')->nullable(); $table->string('actor_agent_id')->nullable(); $table->string('device_id')->nullable(); $table->string('conversation_id')->nullable(); $table->ulid('correlation_id')->nullable(); $table->ulid('causation_id')->nullable(); $table->text('reason')->nullable(); $table->json('before_state_json')->nullable(); $table->json('after_state_json')->nullable(); $table->json('changes_json')->nullable(); $table->string('signature',64); $table->timestamp('created_at');
            $table->index(['entity_type','entity_id']); $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('titan_money_outbox_events', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->ulid('company_id')->index(); $table->string('event_type')->index(); $table->unsignedSmallInteger('event_version')->default(1); $table->string('aggregate_type'); $table->string('aggregate_id'); $table->unsignedInteger('aggregate_version')->default(1); $table->string('actor_type')->nullable(); $table->string('actor_id')->nullable(); $table->string('conversation_id')->nullable(); $table->ulid('correlation_id')->nullable(); $table->ulid('causation_id')->nullable(); $table->json('payload_json'); $table->timestamp('occurred_at'); $table->timestamp('published_at')->nullable()->index(); $table->unsignedInteger('attempts')->default(0); $table->text('last_error')->nullable(); $table->timestamps();
            $table->index(['aggregate_type','aggregate_id']); $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['titan_money_outbox_events','titan_money_audit_logs','titan_money_credit_notes','titan_money_payment_allocations','titan_money_payments'] as $table) Schema::dropIfExists($table);
    }
};
