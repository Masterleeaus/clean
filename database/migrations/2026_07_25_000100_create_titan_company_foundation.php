<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tz_companies', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 36)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status', 32)->default('active')->index();
            $table->string('timezone', 64)->default('Australia/Melbourne');
            $table->string('currency', 3)->default('AUD');
            $table->string('country_code', 2)->default('AU');
            $table->json('settings')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('tz_company_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('key', 64);
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->unique(['company_id', 'key']);
        });

        Schema::create('tz_company_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('tz_company_roles')->nullOnDelete();
            $table->string('role_key', 64)->default('member');
            $table->string('status', 32)->default('active')->index();
            $table->boolean('is_owner')->default(false);
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'user_id']);
        });

        Schema::create('tz_company_role_permissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('tz_company_roles')->cascadeOnDelete();
            $table->string('permission', 160);
            $table->string('access_level', 16)->default('all');
            $table->unique(['company_id', 'role_id', 'permission'], 'tz_role_permission_unique');
        });

        Schema::create('tz_company_member_permissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('membership_id')->constrained('tz_company_memberships')->cascadeOnDelete();
            $table->string('permission', 160);
            $table->string('access_level', 16)->default('all');
            $table->unique(['company_id', 'membership_id', 'permission'], 'tz_member_permission_unique');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('active_company_id')
                ->nullable()
                ->after('id')
                ->constrained('tz_companies')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('active_company_id');
        });
        Schema::dropIfExists('tz_company_member_permissions');
        Schema::dropIfExists('tz_company_role_permissions');
        Schema::dropIfExists('tz_company_memberships');
        Schema::dropIfExists('tz_company_roles');
        Schema::dropIfExists('tz_companies');
    }
};
