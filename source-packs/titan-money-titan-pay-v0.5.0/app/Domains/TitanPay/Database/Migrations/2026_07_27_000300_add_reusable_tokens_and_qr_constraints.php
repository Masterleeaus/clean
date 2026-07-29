<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('titanpay_collection_sessions', function (Blueprint $table): void {
            if (! Schema::hasColumn('titanpay_collection_sessions', 'public_token_ciphertext')) {
                $table->text('public_token_ciphertext')->nullable()->after('public_token_hash');
            }
            $table->index(['company_id', 'invoice_id', 'status', 'expires_at'], 'titanpay_sessions_reuse_index');
        });

        DB::table('titanpay_qr_codes')
            ->select('collection_session_id')
            ->groupBy('collection_session_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('collection_session_id')
            ->each(function (string $sessionId): void {
                $ids = DB::table('titanpay_qr_codes')
                    ->where('collection_session_id', $sessionId)
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->pluck('id');
                if ($ids->count() > 1) {
                    DB::table('titanpay_qr_codes')->whereIn('id', $ids->slice(1)->all())->delete();
                }
            });

        Schema::table('titanpay_qr_codes', function (Blueprint $table): void {
            $table->unique('collection_session_id', 'titanpay_qr_session_unique');
        });
    }

    public function down(): void
    {
        Schema::table('titanpay_qr_codes', function (Blueprint $table): void {
            $table->dropUnique('titanpay_qr_session_unique');
        });
        Schema::table('titanpay_collection_sessions', function (Blueprint $table): void {
            $table->dropIndex('titanpay_sessions_reuse_index');
            if (Schema::hasColumn('titanpay_collection_sessions', 'public_token_ciphertext')) {
                $table->dropColumn('public_token_ciphertext');
            }
        });
    }
};
