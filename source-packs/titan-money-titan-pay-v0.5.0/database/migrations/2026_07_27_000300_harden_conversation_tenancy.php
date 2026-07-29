<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table): void {
            $table->string('type', 32)->default('p2p')->change();
        });

        Schema::table('messages', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
        });
        Schema::table('messages', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('conversations', function (Blueprint $table): void {
            $table->ulid('company_id')->nullable()->after('id')->index();
            $table->foreignId('created_by_user_id')->nullable()->after('company_id')->constrained('users')->nullOnDelete();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });

        DB::table('conversations')
            ->orderBy('id')
            ->eachById(function (object $conversation): void {
                $participantIds = DB::table('participants')
                    ->where('conversation_id', $conversation->id)
                    ->orderBy('id')
                    ->pluck('user_id');

                if ($participantIds->isEmpty()) {
                    return;
                }

                $commonCompanyIds = DB::table('company_memberships')
                    ->select('company_id')
                    ->whereIn('user_id', $participantIds->all())
                    ->where('is_active', true)
                    ->groupBy('company_id')
                    ->havingRaw('COUNT(DISTINCT user_id) = ?', [$participantIds->count()])
                    ->orderBy('company_id')
                    ->pluck('company_id');

                $preferredCompanyId = DB::table('users')
                    ->where('id', $participantIds->first())
                    ->value('active_company_id');
                $companyId = $preferredCompanyId && $commonCompanyIds->contains($preferredCompanyId)
                    ? $preferredCompanyId
                    : $commonCompanyIds->first();

                DB::table('conversations')
                    ->where('id', $conversation->id)
                    ->update([
                        'created_by_user_id' => $participantIds->first(),
                        // No shared active membership means the legacy conversation stays quarantined.
                        'company_id' => $companyId,
                    ]);
            }, 'id');
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table): void {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn(['company_id', 'created_by_user_id']);
        });
    }
};
