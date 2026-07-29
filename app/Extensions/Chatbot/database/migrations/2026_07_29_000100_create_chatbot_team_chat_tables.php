<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('ext_chatbot_team_conversation_participants')
            && ! Schema::hasColumn('ext_chatbot_team_conversation_participants', 'last_read_message_id')
        ) {
            Schema::table('ext_chatbot_team_conversation_participants', function (Blueprint $table): void {
                $table->unsignedBigInteger('last_read_message_id')->nullable()->after('last_read_at');
            });
        }

        if (
            Schema::hasTable('ext_chatbot_team_messages')
            && ! Schema::hasColumn('ext_chatbot_team_messages', 'metadata')
        ) {
            Schema::table('ext_chatbot_team_messages', function (Blueprint $table): void {
                $table->json('metadata')->nullable()->after('message_type');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('ext_chatbot_team_messages')
            && Schema::hasColumn('ext_chatbot_team_messages', 'metadata')
        ) {
            Schema::table('ext_chatbot_team_messages', function (Blueprint $table): void {
                $table->dropColumn('metadata');
            });
        }

        if (
            Schema::hasTable('ext_chatbot_team_conversation_participants')
            && Schema::hasColumn('ext_chatbot_team_conversation_participants', 'last_read_message_id')
        ) {
            Schema::table('ext_chatbot_team_conversation_participants', function (Blueprint $table): void {
                $table->dropColumn('last_read_message_id');
            });
        }
    }
};
