<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('messages', 'metadata_json')) {
            Schema::table('messages', function (Blueprint $table): void {
                $table->json('metadata_json')->nullable()->after('message');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('messages', 'metadata_json')) {
            Schema::table('messages', fn (Blueprint $table) => $table->dropColumn('metadata_json'));
        }
    }
};
