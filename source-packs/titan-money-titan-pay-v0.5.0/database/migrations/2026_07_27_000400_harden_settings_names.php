<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->text('value')->nullable()->change();
        });

        DB::table('settings')
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name')
            ->each(function (string $name): void {
                $keep = DB::table('settings')->where('name', $name)->latest('id')->value('id');
                DB::table('settings')->where('name', $name)->where('id', '!=', $keep)->delete();
            });

        Schema::table('settings', function (Blueprint $table): void {
            $table->unique('name');
        });

        $apiKey = DB::table('settings')->where('name', 'ai_api_key')->value('value');
        if (is_string($apiKey) && $apiKey !== '' && ! str_starts_with($apiKey, 'encrypted:')) {
            DB::table('settings')->where('name', 'ai_api_key')->update([
                'value' => 'encrypted:'.Crypt::encryptString($apiKey),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropUnique(['name']);
        });
    }
};
