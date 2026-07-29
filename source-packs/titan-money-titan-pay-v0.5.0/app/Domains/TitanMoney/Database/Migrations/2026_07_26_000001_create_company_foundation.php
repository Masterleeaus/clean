<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('trading_name')->nullable();
            $table->string('abn', 20)->nullable();
            $table->boolean('gst_registered')->default(false);
            $table->char('currency_code', 3)->default('AUD');
            $table->string('timezone')->default('Australia/Sydney');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('address_json')->nullable();
            $table->json('settings_json')->nullable();
            $table->timestamps();
        });

        Schema::create('company_memberships', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->ulid('company_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member');
            $table->json('permissions_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id','user_id']);
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->ulid('active_company_id')->nullable()->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('active_company_id'));
        Schema::dropIfExists('company_memberships');
        Schema::dropIfExists('companies');
    }
};
