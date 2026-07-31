<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maps_suppressions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('company_id')->index();
            $table->string('branch_id')->nullable()->index();
            $table->string('workspace_id')->nullable()->index();
            $table->string('suppression_type', 40)->index();
            $table->string('normalised_value', 512)->index();
            $table->text('reason')->nullable();
            $table->string('created_by_user_id')->nullable()->index();
            $table->timestampTz('expires_at')->nullable();
            $table->timestampsTz();
            $table->unique(['company_id', 'suppression_type', 'normalised_value'], 'maps_suppression_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('maps_suppressions'); }
};
