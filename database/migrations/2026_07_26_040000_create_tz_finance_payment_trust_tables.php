<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tz_finance_quotes', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('tz_customers')->nullOnDelete();
            $table->foreignId('premises_id')->nullable()->constrained('tz_premises')->nullOnDelete();
            $table->string('quote_number', 80); $table->string('status', 30)->default('draft')->index();
            $table->char('currency', 3)->default('AUD');
            $table->bigInteger('subtotal_minor')->default(0); $table->bigInteger('discount_minor')->default(0);
            $table->bigInteger('tax_minor')->default(0); $table->bigInteger('total_minor')->default(0);
            $table->date('valid_until')->nullable(); $table->dateTime('issued_at')->nullable();
            $table->dateTime('accepted_at')->nullable(); $table->dateTime('rejected_at')->nullable();
            $table->text('notes')->nullable(); $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(); $table->softDeletes();
            $table->unique(['company_id', 'quote_number'], 'tz_fin_quote_number_unique');
            $table->index(['company_id', 'status', 'valid_until'], 'tz_fin_quote_scope_idx');
        });

        Schema::create('tz_finance_quote_lines', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('quote_id')->constrained('tz_finance_quotes')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1); $table->string('description', 500);
            $table->bigInteger('unit_price_minor'); $table->unsignedInteger('quantity_milli')->default(1000);
            $table->bigInteger('discount_minor')->default(0); $table->unsignedInteger('tax_rate_basis_points')->default(0);
            $table->bigInteger('subtotal_minor'); $table->bigInteger('tax_minor')->default(0); $table->bigInteger('total_minor');
            $table->char('currency', 3)->default('AUD'); $table->json('metadata')->nullable(); $table->timestamps();
            $table->unique(['quote_id', 'position'], 'tz_fin_quote_line_position_unique');
        });

        Schema::create('tz_finance_invoices', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('tz_customers')->nullOnDelete();
            $table->foreignId('premises_id')->nullable()->constrained('tz_premises')->nullOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained('tz_finance_quotes')->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('tz_accommodation_reservations')->nullOnDelete();
            $table->foreignId('source_folio_charge_id')->nullable()->constrained('tz_accommodation_charges')->nullOnDelete();
            $table->string('invoice_number', 80); $table->string('status', 30)->default('draft')->index();
            $table->char('currency', 3)->default('AUD');
            $table->bigInteger('subtotal_minor')->default(0); $table->bigInteger('discount_minor')->default(0);
            $table->bigInteger('tax_minor')->default(0); $table->bigInteger('total_minor')->default(0);
            $table->bigInteger('allocated_minor')->default(0); $table->bigInteger('balance_minor')->default(0);
            $table->date('issue_date')->nullable(); $table->date('due_date')->nullable()->index();
            $table->dateTime('issued_at')->nullable(); $table->dateTime('viewed_at')->nullable();
            $table->dateTime('paid_at')->nullable(); $table->dateTime('voided_at')->nullable();
            $table->text('notes')->nullable(); $table->json('billing_snapshot')->nullable(); $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(); $table->softDeletes();
            $table->unique(['company_id', 'invoice_number'], 'tz_fin_invoice_number_unique');
            $table->index(['company_id', 'status', 'due_date'], 'tz_fin_invoice_scope_idx');
        });

        Schema::create('tz_finance_invoice_lines', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('tz_finance_invoices')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1); $table->string('description', 500);
            $table->bigInteger('unit_price_minor'); $table->unsignedInteger('quantity_milli')->default(1000);
            $table->bigInteger('discount_minor')->default(0); $table->unsignedInteger('tax_rate_basis_points')->default(0);
            $table->bigInteger('subtotal_minor'); $table->bigInteger('tax_minor')->default(0); $table->bigInteger('total_minor');
            $table->char('currency', 3)->default('AUD'); $table->json('source_snapshot')->nullable(); $table->timestamps();
            $table->unique(['invoice_id', 'position'], 'tz_fin_invoice_line_position_unique');
        });

        Schema::create('tz_finance_credit_notes', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('tz_customers')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('tz_finance_invoices')->nullOnDelete();
            $table->string('credit_number', 80); $table->string('status', 30)->default('draft')->index();
            $table->char('currency', 3)->default('AUD'); $table->bigInteger('subtotal_minor')->default(0);
            $table->bigInteger('tax_minor')->default(0); $table->bigInteger('total_minor')->default(0);
            $table->bigInteger('allocated_minor')->default(0); $table->date('issue_date')->nullable();
            $table->text('reason')->nullable(); $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(); $table->softDeletes();
            $table->unique(['company_id', 'credit_number'], 'tz_fin_credit_number_unique');
        });

        Schema::create('tz_finance_credit_note_lines', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('credit_note_id')->constrained('tz_finance_credit_notes')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1); $table->string('description', 500);
            $table->bigInteger('unit_price_minor'); $table->unsignedInteger('quantity_milli')->default(1000);
            $table->unsignedInteger('tax_rate_basis_points')->default(0); $table->bigInteger('subtotal_minor');
            $table->bigInteger('tax_minor')->default(0); $table->bigInteger('total_minor');
            $table->char('currency', 3)->default('AUD'); $table->timestamps();
        });

        Schema::create('tz_finance_credit_allocations', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('credit_note_id')->constrained('tz_finance_credit_notes')->restrictOnDelete();
            $table->foreignId('invoice_id')->constrained('tz_finance_invoices')->restrictOnDelete();
            $table->bigInteger('amount_minor'); $table->char('currency', 3)->default('AUD');
            $table->foreignId('reversal_of_allocation_id')->nullable()->constrained('tz_finance_credit_allocations')->nullOnDelete();
            $table->text('reason')->nullable(); $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(); $table->index(['company_id', 'invoice_id'], 'tz_fin_credit_alloc_invoice_idx');
        });

        Schema::create('tz_finance_expenses', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('tz_suppliers')->nullOnDelete();
            $table->foreignId('premises_id')->nullable()->constrained('tz_premises')->nullOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained('tz_work_orders')->nullOnDelete();
            $table->string('expense_number', 80); $table->string('status', 30)->default('draft')->index();
            $table->string('category', 100)->nullable()->index(); $table->char('currency', 3)->default('AUD');
            $table->bigInteger('subtotal_minor')->default(0); $table->bigInteger('tax_minor')->default(0); $table->bigInteger('total_minor')->default(0);
            $table->date('incurred_on')->index(); $table->date('due_on')->nullable(); $table->text('description')->nullable();
            $table->json('receipt_evidence')->nullable(); $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable(); $table->timestamps(); $table->softDeletes();
            $table->unique(['company_id', 'expense_number'], 'tz_fin_expense_number_unique');
        });

        Schema::create('tz_finance_receivable_allocations', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('tz_finance_invoices')->restrictOnDelete();
            $table->string('source_type', 60); $table->char('source_public_id', 26)->nullable();
            $table->bigInteger('amount_minor'); $table->char('currency', 3)->default('AUD');
            $table->foreignId('reversal_of_allocation_id')->nullable()->constrained('tz_finance_receivable_allocations')->nullOnDelete();
            $table->dateTime('allocated_at'); $table->text('reason')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
            $table->index(['company_id', 'invoice_id', 'allocated_at'], 'tz_fin_receivable_invoice_idx');
        });

        Schema::create('tz_finance_accounting_periods', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->string('name', 120); $table->date('starts_on'); $table->date('ends_on');
            $table->string('status', 30)->default('open')->index(); $table->dateTime('closed_at')->nullable();
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
            $table->unique(['company_id', 'starts_on', 'ends_on'], 'tz_fin_period_dates_unique');
        });

        Schema::create('tz_finance_accounts', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->string('code', 40); $table->string('name', 191); $table->string('type', 40)->index();
            $table->char('currency', 3)->default('AUD'); $table->boolean('active')->default(true)->index();
            $table->boolean('is_control_account')->default(false); $table->json('metadata')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'code'], 'tz_fin_account_code_unique');
        });

        Schema::create('tz_finance_journals', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('accounting_period_id')->nullable()->constrained('tz_finance_accounting_periods')->restrictOnDelete();
            $table->string('journal_number', 80); $table->string('source_type', 60); $table->char('source_public_id', 26)->nullable();
            $table->string('status', 30)->default('posted')->index(); $table->char('currency', 3)->default('AUD');
            $table->bigInteger('debit_minor'); $table->bigInteger('credit_minor'); $table->dateTime('posted_at');
            $table->text('description')->nullable(); $table->foreignId('reversal_of_journal_id')->nullable()->constrained('tz_finance_journals')->nullOnDelete();
            $table->foreignId('posted_by_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
            $table->unique(['company_id', 'journal_number'], 'tz_fin_journal_number_unique');
        });

        Schema::create('tz_finance_journal_lines', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('journal_id')->constrained('tz_finance_journals')->restrictOnDelete();
            $table->foreignId('account_id')->constrained('tz_finance_accounts')->restrictOnDelete();
            $table->unsignedInteger('position'); $table->bigInteger('debit_minor')->default(0); $table->bigInteger('credit_minor')->default(0);
            $table->char('currency', 3)->default('AUD'); $table->string('memo', 500)->nullable(); $table->json('dimensions')->nullable();
            $table->timestamps(); $table->unique(['journal_id', 'position'], 'tz_fin_journal_line_position_unique');
        });

        Schema::create('tz_payment_provider_connections', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->string('provider', 60); $table->string('display_name', 120); $table->string('credential_reference', 255)->nullable();
            $table->boolean('enabled')->default(false)->index(); $table->json('capabilities')->nullable(); $table->json('configuration')->nullable();
            $table->dateTime('last_validated_at')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'provider'], 'tz_pay_provider_company_unique');
        });

        Schema::create('tz_payment_sessions', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('tz_finance_invoices')->nullOnDelete();
            $table->string('payable_type', 60); $table->char('payable_public_id', 26)->nullable();
            $table->string('method', 40)->index(); $table->string('provider', 60)->nullable()->index();
            $table->string('status', 30)->default('pending')->index(); $table->bigInteger('amount_minor');
            $table->char('currency', 3)->default('AUD'); $table->string('reference', 120)->nullable()->index();
            $table->char('session_token_hash', 64)->unique(); $table->dateTime('expires_at')->nullable();
            $table->json('metadata')->nullable(); $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(); $table->index(['company_id', 'status', 'expires_at'], 'tz_pay_session_scope_idx');
        });

        Schema::create('tz_payment_attempts', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('tz_payment_sessions')->restrictOnDelete();
            $table->string('provider', 60)->nullable(); $table->string('status', 30)->default('started')->index();
            $table->string('external_reference', 191)->nullable(); $table->bigInteger('amount_minor'); $table->bigInteger('fee_minor')->default(0);
            $table->char('currency', 3)->default('AUD'); $table->string('failure_code', 100)->nullable();
            $table->text('safe_failure_message')->nullable(); $table->char('response_hash', 64)->nullable(); $table->json('metadata')->nullable();
            $table->dateTime('completed_at')->nullable(); $table->timestamps();
        });

        Schema::create('tz_payment_observations', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->string('provider', 60); $table->string('external_event_id', 191); $table->string('observation_type', 60)->index();
            $table->bigInteger('amount_minor'); $table->char('currency', 3)->default('AUD');
            $table->string('reference', 191)->nullable()->index(); $table->string('payer_name', 191)->nullable();
            $table->dateTime('observed_at')->index(); $table->char('payload_hash', 64); $table->json('safe_payload')->nullable();
            $table->string('status', 30)->default('unmatched')->index(); $table->timestamps();
            $table->unique(['company_id', 'provider', 'external_event_id'], 'tz_pay_observation_event_unique');
        });

        Schema::create('tz_payment_matches', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('observation_id')->constrained('tz_payment_observations')->restrictOnDelete();
            $table->foreignId('invoice_id')->constrained('tz_finance_invoices')->restrictOnDelete();
            $table->unsignedTinyInteger('score'); $table->json('reasons'); $table->string('status', 30)->default('proposed')->index();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->dateTime('reviewed_at')->nullable();
            $table->timestamps(); $table->unique(['observation_id', 'invoice_id'], 'tz_pay_match_observation_invoice_unique');
        });

        Schema::create('tz_payment_reconciliations', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('observation_id')->constrained('tz_payment_observations')->restrictOnDelete();
            $table->foreignId('invoice_id')->constrained('tz_finance_invoices')->restrictOnDelete();
            $table->foreignId('match_id')->nullable()->constrained('tz_payment_matches')->nullOnDelete();
            $table->bigInteger('amount_minor'); $table->char('currency', 3)->default('AUD');
            $table->char('receivable_allocation_public_id', 26)->nullable(); $table->string('status', 30)->default('posted')->index();
            $table->foreignId('reconciled_by_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->dateTime('reconciled_at');
            $table->timestamps(); $table->unique(['company_id', 'observation_id', 'invoice_id'], 'tz_pay_reconcile_unique');
        });

        Schema::create('tz_trust_accounts', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->string('name', 191); $table->string('account_type', 30)->default('trust')->index();
            $table->char('currency', 3)->default('AUD'); $table->string('bank_reference', 191)->nullable();
            $table->string('credential_reference', 255)->nullable(); $table->boolean('is_operating_account')->default(false);
            $table->unsignedTinyInteger('required_approvals')->default(2); $table->boolean('active')->default(true)->index();
            $table->json('metadata')->nullable(); $table->timestamps();
        });

        Schema::create('tz_trust_matters', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('trust_account_id')->constrained('tz_trust_accounts')->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('tz_customers')->nullOnDelete();
            $table->foreignId('premises_id')->nullable()->constrained('tz_premises')->nullOnDelete();
            $table->foreignId('agreement_id')->nullable()->constrained('tz_premises_agreements')->nullOnDelete();
            $table->string('matter_number', 80); $table->string('matter_type', 60)->index(); $table->string('status', 30)->default('open')->index();
            $table->string('display_name', 191); $table->json('metadata')->nullable(); $table->timestamps();
            $table->unique(['company_id', 'matter_number'], 'tz_trust_matter_number_unique');
        });

        Schema::create('tz_trust_receipts', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('trust_account_id')->constrained('tz_trust_accounts')->restrictOnDelete();
            $table->foreignId('matter_id')->nullable()->constrained('tz_trust_matters')->nullOnDelete();
            $table->string('receipt_number', 80); $table->bigInteger('amount_minor'); $table->char('currency', 3)->default('AUD');
            $table->string('payer_name', 191); $table->string('reference', 191)->nullable(); $table->dateTime('received_at');
            $table->string('status', 30)->default('received')->index(); $table->char('source_observation_public_id', 26)->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
            $table->unique(['company_id', 'receipt_number'], 'tz_trust_receipt_number_unique');
        });

        Schema::create('tz_trust_allocations', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('receipt_id')->constrained('tz_trust_receipts')->restrictOnDelete();
            $table->foreignId('matter_id')->constrained('tz_trust_matters')->restrictOnDelete();
            $table->bigInteger('amount_minor'); $table->char('currency', 3)->default('AUD');
            $table->foreignId('reversal_of_allocation_id')->nullable()->constrained('tz_trust_allocations')->nullOnDelete();
            $table->text('purpose')->nullable(); $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
        });

        Schema::create('tz_trust_disbursements', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('trust_account_id')->constrained('tz_trust_accounts')->restrictOnDelete();
            $table->foreignId('matter_id')->constrained('tz_trust_matters')->restrictOnDelete();
            $table->string('disbursement_number', 80); $table->bigInteger('amount_minor'); $table->char('currency', 3)->default('AUD');
            $table->string('payee_name', 191); $table->string('status', 30)->default('pending_approval')->index();
            $table->unsignedTinyInteger('required_approvals')->default(2); $table->text('purpose');
            $table->string('destination_reference', 191)->nullable(); $table->dateTime('released_at')->nullable();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('released_by_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
            $table->unique(['company_id', 'disbursement_number'], 'tz_trust_disbursement_number_unique');
        });

        Schema::create('tz_trust_approvals', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('disbursement_id')->constrained('tz_trust_disbursements')->restrictOnDelete();
            $table->foreignId('approved_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('decision', 20); $table->text('reason')->nullable(); $table->dateTime('decided_at'); $table->timestamps();
            $table->unique(['disbursement_id', 'approved_by_user_id'], 'tz_trust_approval_actor_unique');
        });

        Schema::create('tz_trust_ledger_entries', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('trust_account_id')->constrained('tz_trust_accounts')->restrictOnDelete();
            $table->foreignId('matter_id')->nullable()->constrained('tz_trust_matters')->nullOnDelete();
            $table->string('entry_type', 40)->index(); $table->string('source_type', 60); $table->char('source_public_id', 26)->nullable();
            $table->bigInteger('amount_minor'); $table->char('currency', 3)->default('AUD');
            $table->foreignId('reversal_of_entry_id')->nullable()->constrained('tz_trust_ledger_entries')->nullOnDelete();
            $table->dateTime('posted_at'); $table->text('description')->nullable();
            $table->foreignId('posted_by_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
            $table->index(['company_id', 'trust_account_id', 'posted_at'], 'tz_trust_ledger_account_idx');
            $table->index(['company_id', 'matter_id', 'posted_at'], 'tz_trust_ledger_matter_idx');
        });

        Schema::create('tz_trust_reconciliations', function (Blueprint $table): void {
            $table->id(); $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('trust_account_id')->constrained('tz_trust_accounts')->restrictOnDelete();
            $table->date('statement_date'); $table->bigInteger('statement_balance_minor'); $table->bigInteger('ledger_balance_minor');
            $table->bigInteger('difference_minor'); $table->char('currency', 3)->default('AUD');
            $table->string('status', 30)->default('draft')->index(); $table->dateTime('completed_at')->nullable();
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
            $table->unique(['company_id', 'trust_account_id', 'statement_date'], 'tz_trust_reconcile_date_unique');
        });

        Schema::create('tz_trust_reconciliation_lines', function (Blueprint $table): void {
            $table->id(); $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('reconciliation_id')->constrained('tz_trust_reconciliations')->restrictOnDelete();
            $table->foreignId('ledger_entry_id')->nullable()->constrained('tz_trust_ledger_entries')->nullOnDelete();
            $table->string('line_type', 40); $table->bigInteger('amount_minor'); $table->char('currency', 3)->default('AUD');
            $table->string('reference', 191)->nullable(); $table->boolean('matched')->default(false); $table->json('metadata')->nullable(); $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'tz_trust_reconciliation_lines', 'tz_trust_reconciliations', 'tz_trust_ledger_entries', 'tz_trust_approvals',
            'tz_trust_disbursements', 'tz_trust_allocations', 'tz_trust_receipts', 'tz_trust_matters', 'tz_trust_accounts',
            'tz_payment_reconciliations', 'tz_payment_matches', 'tz_payment_observations', 'tz_payment_attempts',
            'tz_payment_sessions', 'tz_payment_provider_connections', 'tz_finance_journal_lines', 'tz_finance_journals',
            'tz_finance_accounts', 'tz_finance_accounting_periods', 'tz_finance_receivable_allocations', 'tz_finance_expenses',
            'tz_finance_credit_allocations', 'tz_finance_credit_note_lines', 'tz_finance_credit_notes',
            'tz_finance_invoice_lines', 'tz_finance_invoices', 'tz_finance_quote_lines', 'tz_finance_quotes',
        ] as $table) Schema::dropIfExists($table);
    }
};
