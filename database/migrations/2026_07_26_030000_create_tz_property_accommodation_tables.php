<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tz_premises_party_roles', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('premises_id')->constrained('tz_premises')->cascadeOnDelete();
            $table->foreignId('space_id')->nullable()->constrained('tz_premises_spaces')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('tz_customer_contacts')->nullOnDelete();
            $table->string('role_type', 60)->index();
            $table->string('display_name', 191);
            $table->string('email')->nullable();
            $table->string('phone', 80)->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->dateTime('effective_from')->nullable();
            $table->dateTime('effective_until')->nullable();
            $table->boolean('managed_by_accommodation')->default(false)->index();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'premises_id', 'role_type', 'status'], 'tz_party_role_scope_idx');
        });

        Schema::create('tz_premises_agreements', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('premises_id')->constrained('tz_premises')->cascadeOnDelete();
            $table->foreignId('primary_space_id')->nullable()->constrained('tz_premises_spaces')->nullOnDelete();
            $table->foreignId('primary_party_role_id')->nullable()->constrained('tz_premises_party_roles')->nullOnDelete();
            $table->string('agreement_type', 60)->index();
            $table->string('agreement_number', 120)->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('signed_at')->nullable();
            $table->char('currency', 3)->default('AUD');
            $table->decimal('periodic_amount', 14, 4)->nullable();
            $table->string('periodic_basis', 30)->nullable();
            $table->decimal('bond_amount', 14, 4)->nullable();
            $table->string('external_reference', 191)->nullable();
            $table->boolean('managed_by_accommodation')->default(false)->index();
            $table->json('terms')->nullable();
            $table->json('metadata')->nullable();
            $table->text('termination_reason')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['company_id', 'agreement_number'], 'tz_agreement_number_unique');
            $table->index(['company_id', 'premises_id', 'status', 'starts_at'], 'tz_agreement_scope_idx');
        });

        Schema::create('tz_premises_occupancies', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('premises_id')->constrained('tz_premises')->cascadeOnDelete();
            $table->foreignId('space_id')->constrained('tz_premises_spaces')->cascadeOnDelete();
            $table->foreignId('party_role_id')->constrained('tz_premises_party_roles')->cascadeOnDelete();
            $table->foreignId('agreement_id')->nullable()->constrained('tz_premises_agreements')->nullOnDelete();
            $table->string('occupancy_type', 60)->default('resident')->index();
            $table->string('status', 30)->default('reserved')->index();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->unsignedInteger('capacity_used')->default(1);
            $table->boolean('exclusive')->default(true);
            $table->boolean('managed_by_accommodation')->default(false)->index();
            $table->text('end_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ended_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'space_id', 'status', 'starts_at'], 'tz_occupancy_space_idx');
            $table->index(['company_id', 'party_role_id', 'status'], 'tz_occupancy_party_idx');
        });

        Schema::create('tz_premises_access_authorisations', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('premises_id')->constrained('tz_premises')->cascadeOnDelete();
            $table->foreignId('space_id')->nullable()->constrained('tz_premises_spaces')->nullOnDelete();
            $table->foreignId('party_role_id')->constrained('tz_premises_party_roles')->cascadeOnDelete();
            $table->foreignId('occupancy_id')->nullable()->constrained('tz_premises_occupancies')->nullOnDelete();
            $table->foreignId('agreement_id')->nullable()->constrained('tz_premises_agreements')->nullOnDelete();
            $table->string('authorisation_type', 60)->default('occupancy')->index();
            $table->string('status', 30)->default('active')->index();
            $table->dateTime('valid_from');
            $table->dateTime('valid_until')->nullable();
            $table->string('vault_secret_reference', 255)->nullable();
            $table->boolean('managed_by_accommodation')->default(false)->index();
            $table->text('revocation_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'premises_id', 'status', 'valid_from'], 'tz_access_auth_scope_idx');
        });

        Schema::create('tz_accommodation_rate_plans', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('premises_id')->nullable()->constrained('tz_premises')->nullOnDelete();
            $table->string('code', 100);
            $table->string('name', 191);
            $table->string('pricing_basis', 40)->default('night')->index();
            $table->char('currency', 3)->default('AUD');
            $table->decimal('base_amount', 14, 4)->default(0);
            $table->unsignedInteger('minimum_nights')->default(1);
            $table->unsignedInteger('maximum_nights')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->json('restrictions')->nullable();
            $table->json('cancellation_policy')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['company_id', 'code'], 'tz_accom_rate_code_unique');
            $table->index(['company_id', 'premises_id', 'active'], 'tz_accom_rate_scope_idx');
        });

        Schema::create('tz_accommodation_reservations', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('premises_id')->constrained('tz_premises')->cascadeOnDelete();
            $table->foreignId('primary_space_id')->nullable()->constrained('tz_premises_spaces')->nullOnDelete();
            $table->foreignId('occupancy_id')->nullable()->constrained('tz_premises_occupancies')->nullOnDelete();
            $table->foreignId('agreement_id')->nullable()->constrained('tz_premises_agreements')->nullOnDelete();
            $table->foreignId('rate_plan_id')->nullable()->constrained('tz_accommodation_rate_plans')->nullOnDelete();
            $table->string('reservation_type', 60)->default('short_stay')->index();
            $table->string('status', 40)->default('draft')->index();
            $table->string('source_channel', 60)->default('direct')->index();
            $table->string('external_reference', 191)->nullable();
            $table->string('primary_guest_name', 191);
            $table->string('primary_guest_email')->nullable();
            $table->string('primary_guest_phone', 80)->nullable();
            $table->dateTime('arrival_at')->index();
            $table->dateTime('departure_at')->index();
            $table->dateTime('booked_at')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);
            $table->unsignedInteger('capacity')->default(1);
            $table->char('currency', 3)->default('AUD');
            $table->decimal('subtotal_amount', 14, 4)->default(0);
            $table->decimal('tax_amount', 14, 4)->default(0);
            $table->decimal('total_amount', 14, 4)->default(0);
            $table->decimal('deposit_amount', 14, 4)->default(0);
            $table->decimal('balance_amount', 14, 4)->default(0);
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['company_id', 'source_channel', 'external_reference'], 'tz_accom_external_unique');
            $table->index(['company_id', 'premises_id', 'status', 'arrival_at'], 'tz_accom_arrival_idx');
            $table->index(['company_id', 'premises_id', 'status', 'departure_at'], 'tz_accom_departure_idx');
        });

        Schema::create('tz_accommodation_reservation_spaces', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained('tz_accommodation_reservations')->cascadeOnDelete();
            $table->foreignId('space_id')->constrained('tz_premises_spaces')->restrictOnDelete();
            $table->foreignId('rate_plan_id')->nullable()->constrained('tz_accommodation_rate_plans')->nullOnDelete();
            $table->dateTime('arrival_at')->index();
            $table->dateTime('departure_at')->index();
            $table->unsignedInteger('capacity')->default(1);
            $table->decimal('unit_amount', 14, 4)->default(0);
            $table->decimal('tax_amount', 14, 4)->default(0);
            $table->decimal('total_amount', 14, 4)->default(0);
            $table->string('status', 40)->default('reserved')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['reservation_id', 'space_id'], 'tz_accom_res_space_unique');
            $table->index(['company_id', 'space_id', 'status', 'arrival_at'], 'tz_accom_space_arrival_idx');
        });

        Schema::create('tz_accommodation_guests', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained('tz_accommodation_reservations')->cascadeOnDelete();
            $table->foreignId('party_role_id')->nullable()->constrained('tz_premises_party_roles')->nullOnDelete();
            $table->foreignId('occupancy_id')->nullable()->constrained('tz_premises_occupancies')->nullOnDelete();
            $table->string('guest_type', 60)->default('guest')->index();
            $table->string('display_name', 191);
            $table->string('email')->nullable();
            $table->string('phone', 80)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->boolean('is_primary')->default(false)->index();
            $table->text('accessibility_requirements')->nullable();
            $table->json('emergency_contact')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'reservation_id', 'is_primary'], 'tz_accom_guest_primary_idx');
        });

        Schema::create('tz_accommodation_stays', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained('tz_accommodation_reservations')->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('tz_accommodation_guests')->restrictOnDelete();
            $table->foreignId('premises_id')->constrained('tz_premises')->restrictOnDelete();
            $table->foreignId('space_id')->constrained('tz_premises_spaces')->restrictOnDelete();
            $table->foreignId('occupancy_id')->nullable()->constrained('tz_premises_occupancies')->nullOnDelete();
            $table->foreignId('agreement_id')->nullable()->constrained('tz_premises_agreements')->nullOnDelete();
            $table->foreignId('access_authorisation_id')->nullable()->constrained('tz_premises_access_authorisations')->nullOnDelete();
            $table->string('status', 40)->default('expected')->index();
            $table->dateTime('expected_arrival_at');
            $table->dateTime('expected_departure_at');
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checked_out_at')->nullable();
            $table->text('check_in_notes')->nullable();
            $table->text('check_out_notes')->nullable();
            $table->foreignId('checked_in_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_out_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['reservation_id', 'space_id'], 'tz_accom_stay_res_space_unique');
            $table->unique(['reservation_id', 'guest_id'], 'tz_accom_stay_res_guest_unique');
            $table->index(['company_id', 'space_id', 'status', 'expected_arrival_at'], 'tz_accom_stay_space_idx');
        });

        Schema::create('tz_accommodation_housekeeping_tasks', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('tz_accommodation_reservations')->nullOnDelete();
            $table->foreignId('stay_id')->nullable()->constrained('tz_accommodation_stays')->nullOnDelete();
            $table->foreignId('premises_id')->constrained('tz_premises')->cascadeOnDelete();
            $table->foreignId('space_id')->constrained('tz_premises_spaces')->cascadeOnDelete();
            $table->foreignId('assigned_worker_id')->nullable()->constrained('tz_workers')->nullOnDelete();
            $table->string('task_type', 60)->default('turnover')->index();
            $table->string('status', 40)->default('dirty')->index();
            $table->dateTime('due_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('inspected_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('evidence_references')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'premises_id', 'status', 'due_at'], 'tz_accom_housekeeping_idx');
        });

        Schema::create('tz_accommodation_charges', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained('tz_accommodation_reservations')->cascadeOnDelete();
            $table->foreignId('stay_id')->nullable()->constrained('tz_accommodation_stays')->nullOnDelete();
            $table->foreignId('reversal_of_charge_id')->nullable()->constrained('tz_accommodation_charges')->nullOnDelete();
            $table->string('charge_type', 60)->index();
            $table->string('description', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->char('currency', 3)->default('AUD');
            $table->decimal('unit_amount', 14, 4);
            $table->decimal('tax_amount', 14, 4)->default(0);
            $table->decimal('total_amount', 14, 4);
            $table->string('status', 30)->default('posted')->index();
            $table->dateTime('posted_at');
            $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['company_id', 'reservation_id', 'status', 'posted_at'], 'tz_accom_charge_folio_idx');
        });

        Schema::create('tz_accommodation_channel_references', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('premises_id')->constrained('tz_premises')->cascadeOnDelete();
            $table->foreignId('space_id')->nullable()->constrained('tz_premises_spaces')->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('tz_accommodation_reservations')->nullOnDelete();
            $table->string('channel', 60)->index();
            $table->string('entity_type', 40)->default('reservation');
            $table->string('external_id', 191);
            $table->string('external_url')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->dateTime('last_synced_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'channel', 'entity_type', 'external_id'], 'tz_accom_channel_unique');
        });
    }

    public function down(): void
    {
        foreach ([
            'tz_accommodation_channel_references', 'tz_accommodation_charges', 'tz_accommodation_housekeeping_tasks',
            'tz_accommodation_stays', 'tz_accommodation_guests', 'tz_accommodation_reservation_spaces',
            'tz_accommodation_reservations', 'tz_accommodation_rate_plans', 'tz_premises_access_authorisations',
            'tz_premises_occupancies', 'tz_premises_agreements', 'tz_premises_party_roles',
        ] as $table) Schema::dropIfExists($table);
    }
};
