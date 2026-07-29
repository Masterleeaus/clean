<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'is_platform_admin')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('is_platform_admin')->default(false)->index()->after('active_company_id');
            });
        }

        $legacyAdminEmail = trim((string) config('app.admin_email', ''));
        if ($legacyAdminEmail !== '') {
            DB::table('users')
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($legacyAdminEmail)])
                ->update(['is_platform_admin' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_platform_admin')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('is_platform_admin');
            });
        }
    }
};
