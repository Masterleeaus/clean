<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('titan_money_customers', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->ulid('company_id')->index();
            $table->string('external_type')->nullable(); $table->string('external_id')->nullable();
            $table->string('name'); $table->string('email')->nullable(); $table->string('phone')->nullable();
            $table->string('business_name')->nullable(); $table->string('abn',20)->nullable();
            $table->json('billing_address_json')->nullable(); $table->json('service_address_json')->nullable();
            $table->json('metadata_json')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->unique(['company_id','external_type','external_id']);
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('titan_money_tax_profiles', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->ulid('company_id')->index(); $table->string('code',30); $table->string('name');
            $table->integer('rate_basis_points')->default(0); $table->string('classification')->default('taxable');
            $table->boolean('is_inclusive')->default(false); $table->boolean('is_default')->default(false); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->unique(['company_id','code']); $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('titan_money_products', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->ulid('company_id')->index(); $table->string('external_id')->nullable(); $table->string('sku')->nullable();
            $table->string('name'); $table->text('description')->nullable(); $table->string('unit')->default('each');
            $table->bigInteger('unit_price_minor')->default(0); $table->char('currency_code',3)->default('AUD'); $table->ulid('tax_profile_id')->nullable();
            $table->decimal('stock_quantity',16,4)->default(0); $table->decimal('stock_alert_quantity',16,4)->default(0); $table->json('metadata_json')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->unique(['company_id','sku']); $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('tax_profile_id')->references('id')->on('titan_money_tax_profiles')->nullOnDelete();
        });

        Schema::create('titan_money_number_sequences', function (Blueprint $table): void {
            $table->id(); $table->ulid('company_id'); $table->string('series',30); $table->string('period',20); $table->unsignedBigInteger('last_number')->default(0); $table->timestamps();
            $table->unique(['company_id','series','period']); $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('titan_money_recurring_invoices', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->ulid('company_id')->index(); $table->ulid('customer_id');
            $table->string('source_type')->nullable(); $table->string('source_id')->nullable(); $table->string('frequency',30); $table->unsignedInteger('interval_count')->default(1);
            $table->string('billing_timing')->default('advance'); $table->date('start_date'); $table->date('end_date')->nullable(); $table->date('next_generation_date');
            $table->timestamp('last_generated_at')->nullable(); $table->unsignedInteger('stop_after')->nullable(); $table->unsignedInteger('occurrences')->default(0); $table->string('status')->default('active');
            $table->char('currency_code',3)->default('AUD'); $table->boolean('tax_inclusive')->default(false); $table->string('payment_terms_code')->default('NET14');
            $table->text('notes')->nullable(); $table->json('metadata_json')->nullable(); $table->foreignId('created_by_user_id')->nullable(); $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete(); $table->foreign('customer_id')->references('id')->on('titan_money_customers')->restrictOnDelete();
        });

        Schema::create('titan_money_recurring_invoice_lines', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->ulid('recurring_invoice_id'); $table->string('line_type')->default('service'); $table->text('description');
            $table->decimal('quantity',16,4)->default(1); $table->bigInteger('unit_price_minor'); $table->bigInteger('discount_minor')->default(0); $table->string('tax_code')->nullable();
            $table->integer('tax_rate_basis_points')->default(0); $table->ulid('product_id')->nullable(); $table->json('metadata_json')->nullable(); $table->unsignedInteger('display_order')->default(0); $table->timestamps();
            $table->foreign('recurring_invoice_id')->references('id')->on('titan_money_recurring_invoices')->cascadeOnDelete(); $table->foreign('product_id')->references('id')->on('titan_money_products')->nullOnDelete();
        });

        Schema::create('titan_money_invoices', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->ulid('company_id')->index(); $table->ulid('branch_id')->nullable()->index(); $table->ulid('workspace_id')->nullable()->index();
            $table->string('invoice_number'); $table->string('invoice_series')->default('INV'); $table->string('status')->default('draft')->index(); $table->ulid('customer_id'); $table->ulid('contact_id')->nullable();
            foreach (['property_id','unit_id','project_id','job_id','work_order_id','service_agreement_id'] as $column) $table->ulid($column)->nullable()->index();
            $table->ulid('recurring_invoice_id')->nullable(); $table->string('source_type')->nullable(); $table->string('source_id')->nullable(); $table->unsignedInteger('source_version')->nullable(); $table->string('source_idempotency_key')->nullable();
            $table->char('currency_code',3)->default('AUD'); $table->date('issue_date')->nullable(); $table->date('service_period_start')->nullable(); $table->date('service_period_end')->nullable(); $table->date('due_date')->nullable()->index();
            $table->string('payment_terms_code')->default('NET14'); $table->bigInteger('subtotal_minor')->default(0); $table->bigInteger('discount_minor')->default(0); $table->bigInteger('tax_minor')->default(0); $table->bigInteger('total_minor')->default(0);
            $table->bigInteger('amount_paid_minor')->default(0); $table->bigInteger('amount_credited_minor')->default(0); $table->bigInteger('balance_due_minor')->default(0); $table->boolean('tax_inclusive')->default(false);
            $table->json('customer_snapshot_json')->nullable(); $table->json('business_snapshot_json')->nullable(); $table->json('billing_address_snapshot_json')->nullable(); $table->json('service_address_snapshot_json')->nullable();
            $table->text('notes')->nullable(); $table->text('customer_message')->nullable(); $table->text('internal_notes')->nullable(); $table->timestamp('issued_at')->nullable(); $table->timestamp('voided_at')->nullable(); $table->timestamp('cancelled_at')->nullable(); $table->timestamp('paid_at')->nullable();
            $table->string('document_path')->nullable(); $table->string('document_hash',64)->nullable(); $table->string('template_version')->default('1.0'); $table->unsignedInteger('version')->default(1);
            $table->foreignId('created_by_user_id')->nullable(); $table->string('created_by_agent_id')->nullable(); $table->string('device_id')->nullable(); $table->string('conversation_id')->nullable(); $table->ulid('correlation_id')->nullable(); $table->ulid('causation_id')->nullable(); $table->timestamps();
            $table->unique(['company_id','invoice_number']); $table->unique(['company_id','source_idempotency_key']);
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete(); $table->foreign('customer_id')->references('id')->on('titan_money_customers')->restrictOnDelete();
            $table->foreign('recurring_invoice_id')->references('id')->on('titan_money_recurring_invoices')->nullOnDelete();
        });

        Schema::create('titan_money_invoice_lines', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->ulid('invoice_id'); $table->string('line_type')->default('service'); $table->text('description'); $table->decimal('quantity',16,4)->default(1);
            $table->bigInteger('unit_price_minor'); $table->bigInteger('discount_minor')->default(0); $table->string('tax_code')->nullable(); $table->integer('tax_rate_basis_points')->default(0); $table->bigInteger('tax_minor')->default(0); $table->bigInteger('line_total_minor')->default(0);
            $table->ulid('product_id')->nullable(); $table->ulid('service_id')->nullable(); $table->ulid('job_task_id')->nullable(); $table->ulid('inventory_movement_id')->nullable(); $table->date('service_date')->nullable(); $table->json('metadata_json')->nullable(); $table->unsignedInteger('display_order')->default(0); $table->timestamps();
            $table->foreign('invoice_id')->references('id')->on('titan_money_invoices')->cascadeOnDelete(); $table->foreign('product_id')->references('id')->on('titan_money_products')->nullOnDelete();
        });

        Schema::create('titan_money_invoice_reminders', function (Blueprint $table): void {
            $table->ulid('id')->primary(); $table->ulid('invoice_id'); $table->integer('days_offset'); $table->string('type'); $table->string('channel')->default('email'); $table->timestamp('scheduled_at')->index(); $table->string('status')->default('pending')->index(); $table->timestamp('sent_at')->nullable(); $table->text('error_message')->nullable(); $table->json('metadata_json')->nullable(); $table->timestamps();
            $table->foreign('invoice_id')->references('id')->on('titan_money_invoices')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['titan_money_invoice_reminders','titan_money_invoice_lines','titan_money_invoices','titan_money_recurring_invoice_lines','titan_money_recurring_invoices','titan_money_number_sequences','titan_money_products','titan_money_tax_profiles','titan_money_customers'] as $table) Schema::dropIfExists($table);
    }
};
