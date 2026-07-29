<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('titan_money_channel_deliveries', function (Blueprint $table): void {
            $table->string('provider')->nullable()->after('channel');
            $table->string('external_message_id')->nullable()->after('recipient_address');
            $table->json('receipt_json')->nullable()->after('payload_json');
            $table->timestamp('delivered_at')->nullable()->after('sent_at');
            $table->timestamp('failed_at')->nullable()->after('delivered_at');
        });

        DB::table('titan_money_automation_policies')
            ->where('agent_key', 'invoice_generation')
            ->where('enabled', false)
            ->whereNull('updated_by_user_id')
            ->update([
                'enabled' => true,
                'autonomy_level' => 'bounded',
                'max_auto_issue_minor' => 100000,
                'settings_json' => json_encode([
                    'auto_issue' => true,
                    'send_on_issue' => true,
                    'payment_terms_days' => 14,
                    'max_events_per_run' => 100,
                    'allowed_source_types' => ['completed_job','approved_quote','recurring_obligation','service_agreement'],
                ], JSON_THROW_ON_ERROR),
                'channels_json' => json_encode(['customer_app','sms','email'], JSON_THROW_ON_ERROR),
            ]);

        DB::table('titan_money_automation_policies')
            ->where('agent_key', 'receivables_follow_up')
            ->where('enabled', false)
            ->whereNull('updated_by_user_id')
            ->update(['enabled' => true, 'autonomy_level' => 'bounded']);
    }

    public function down(): void
    {
        Schema::table('titan_money_channel_deliveries', function (Blueprint $table): void {
            $table->dropColumn(['provider','external_message_id','receipt_json','delivered_at','failed_at']);
        });
    }
};
