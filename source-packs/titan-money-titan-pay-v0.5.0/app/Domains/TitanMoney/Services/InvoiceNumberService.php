<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Support\CurrentCompany;
use Illuminate\Support\Facades\DB;

final class InvoiceNumberService
{
    public function __construct(private readonly CurrentCompany $currentCompany) {}

    public function next(string $series = 'INV'): string
    {
        $companyId = $this->currentCompany->require()->id;
        $series = strtoupper(trim($series));
        $year = now()->format('Y');

        return DB::transaction(function () use ($companyId, $series, $year): string {
            DB::table('titan_money_number_sequences')->insertOrIgnore([
                'company_id' => $companyId,
                'series' => $series,
                'period' => $year,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $row = DB::table('titan_money_number_sequences')
                ->where('company_id', $companyId)
                ->where('series', $series)
                ->where('period', $year)
                ->lockForUpdate()
                ->first();

            $next = ((int) $row->last_number) + 1;
            DB::table('titan_money_number_sequences')
                ->where('id', $row->id)
                ->update(['last_number' => $next, 'updated_at' => now()]);

            return sprintf('%s-%s-%06d', $series, $year, $next);
        });
    }
}
