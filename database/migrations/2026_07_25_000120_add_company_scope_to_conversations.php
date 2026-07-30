<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table): void {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('tz_companies')
                ->nullOnDelete();
            $table->string('type', 32)->default('p2p')->change();
            $table->index(['company_id', 'type'], 'conversations_company_type_index');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table): void {
            $table->dropIndex('conversations_company_type_index');
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
