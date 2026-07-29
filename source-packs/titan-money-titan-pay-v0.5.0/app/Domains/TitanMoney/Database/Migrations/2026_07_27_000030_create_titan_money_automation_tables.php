<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('titan_money_automation_policies', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->ulid('company_id')->index();
            $table->string('agent_key');
            $table->boolean('enabled')->default(false)->index();
            $table->string('autonomy_level')->default('observe');
            $table->bigInteger('max_auto_issue_minor')->default(0);
            $table->json('settings_json')->nullable();
            $table->json('channels_json')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable()->index();
            $table->foreignId('updated_by_user_id')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'agent_key']);
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('titan_money_billable_events', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->ulid('company_id')->index();
            $table->string('source_type');
            $table->string('source_id');
            $table->string('source_version')->nullable();
            $table->string('idempotency_key');
            $table->ulid('customer_id');
            $table->json('payload_json');
            $table->string('status')->default('pending')->index();
            $table->timestamp('occurred_at');
            $table->timestamp('available_at')->index();
            $table->timestamp('processed_at')->nullable();
            $table->ulid('invoice_id')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->ulid('correlation_id')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'idempotency_key']);
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('titan_money_customers')->restrictOnDelete();
            $table->foreign('invoice_id')->references('id')->on('titan_money_invoices')->nullOnDelete();
        });

        Schema::create('titan_money_agent_runs', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->ulid('company_id')->index();
            $table->ulid('automation_policy_id')->nullable();
            $table->string('agent_key')->index();
            $table->string('trigger_type');
            $table->string('status')->default('running')->index();
            $table->boolean('dry_run')->default(false);
            $table->json('trigger_json')->nullable();
            $table->json('decision_json')->nullable();
            $table->json('result_json')->nullable();
            $table->unsignedInteger('items_examined')->default(0);
            $table->unsignedInteger('items_acted')->default(0);
            $table->unsignedInteger('items_escalated')->default(0);
            $table->text('error_message')->nullable();
            $table->ulid('correlation_id')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('automation_policy_id')->references('id')->on('titan_money_automation_policies')->nullOnDelete();
        });

        Schema::create('titan_money_agent_approval_requests', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->ulid('company_id')->index();
            $table->ulid('agent_run_id')->nullable();
            $table->string('agent_key')->index();
            $table->string('action_key');
            $table->string('resource_type');
            $table->string('resource_id');
            $table->string('status')->default('pending')->index();
            $table->json('payload_json');
            $table->text('reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('decided_by_user_id')->nullable();
            $table->text('decision_notes')->nullable();
            $table->timestamps();
            $table->index(['resource_type', 'resource_id']);
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('agent_run_id')->references('id')->on('titan_money_agent_runs')->nullOnDelete();
        });

        Schema::create('titan_money_invoice_follow_ups', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->ulid('company_id')->index();
            $table->ulid('invoice_id');
            $table->ulid('agent_run_id')->nullable();
            $table->string('stage_key');
            $table->integer('days_overdue');
            $table->string('status')->default('planned')->index();
            $table->string('tone')->default('friendly');
            $table->json('channels_json');
            $table->timestamp('scheduled_at');
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
            $table->unique(['invoice_id', 'stage_key']);
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('invoice_id')->references('id')->on('titan_money_invoices')->restrictOnDelete();
            $table->foreign('agent_run_id')->references('id')->on('titan_money_agent_runs')->nullOnDelete();
        });

        Schema::create('titan_money_channel_deliveries', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->ulid('company_id')->index();
            $table->ulid('invoice_id')->nullable();
            $table->ulid('follow_up_id')->nullable();
            $table->string('channel');
            $table->string('recipient_type');
            $table->string('recipient_id')->nullable();
            $table->string('recipient_address')->nullable();
            $table->string('template_key');
            $table->string('idempotency_key')->unique();
            $table->string('status')->default('queued')->index();
            $table->json('payload_json');
            $table->timestamp('scheduled_at')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('handed_off_at')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('invoice_id')->references('id')->on('titan_money_invoices')->nullOnDelete();
            $table->foreign('follow_up_id')->references('id')->on('titan_money_invoice_follow_ups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        foreach ([
            'titan_money_channel_deliveries',
            'titan_money_invoice_follow_ups',
            'titan_money_agent_approval_requests',
            'titan_money_agent_runs',
            'titan_money_billable_events',
            'titan_money_automation_policies',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
