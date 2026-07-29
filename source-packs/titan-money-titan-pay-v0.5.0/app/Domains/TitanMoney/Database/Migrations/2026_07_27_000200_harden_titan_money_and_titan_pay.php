<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('users', 'active_company_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->foreign('active_company_id', 'users_active_company_id_foreign')
                    ->references('id')
                    ->on('companies')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('titan_money_recurring_invoices', 'service_agreement_id')) {
            Schema::table('titan_money_recurring_invoices', function (Blueprint $table): void {
                $table->ulid('service_agreement_id')->nullable()->index()->after('source_id');
            });
        }

        Schema::table('titanpay_collection_sessions', function (Blueprint $table): void {
            $table->index(['company_id', 'gateway_reference'], 'titanpay_sessions_gateway_reference_index');
        });
    }

    public function down(): void
    {
        Schema::table('titanpay_collection_sessions', function (Blueprint $table): void {
            $table->dropIndex('titanpay_sessions_gateway_reference_index');
        });

        if (Schema::hasColumn('titan_money_recurring_invoices', 'service_agreement_id')) {
            Schema::table('titan_money_recurring_invoices', function (Blueprint $table): void {
                $table->dropColumn('service_agreement_id');
            });
        }

        if (Schema::hasColumn('users', 'active_company_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropForeign('users_active_company_id_foreign');
            });
        }
    }
};
