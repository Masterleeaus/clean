<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maps_private_exports', function (Blueprint $table): void {
            $table->uuid('reference')->primary();
            $table->foreignId('company_id')->constrained('tz_companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('disk', 64);
            $table->string('path', 500);
            $table->string('filename', 255);
            $table->string('mime_type', 120);
            $table->timestamp('expires_at')->index();
            $table->timestamps();
            $table->index(['company_id', 'user_id', 'expires_at'], 'maps_private_export_scope');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maps_private_exports');
    }
};
