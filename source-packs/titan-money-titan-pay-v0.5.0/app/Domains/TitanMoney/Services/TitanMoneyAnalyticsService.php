<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\TitanMoney\Enums\InvoiceStatus;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

final class TitanMoneyAnalyticsService
{
    public function dashboard(): array
    {
        $invoiced = $this->sumByCurrency(
            Invoice::query()->whereNotIn('status', [InvoiceStatus::Draft->value, InvoiceStatus::Voided->value]),
            'total_minor',
        );
        $outstanding = $this->sumByCurrency(
            Invoice::query()->whereIn('status', [InvoiceStatus::Issued->value, InvoiceStatus::PartiallyPaid->value, InvoiceStatus::Overdue->value]),
            'balance_due_minor',
        );
        $paid = $this->sumByCurrency(Invoice::query(), 'amount_paid_minor');

        $currencyCodes = array_values(array_unique(array_merge(
            array_keys($invoiced),
            array_keys($outstanding),
            array_keys($paid),
        )));
        sort($currencyCodes);

        $currencies = [];
        foreach ($currencyCodes as $currencyCode) {
            $currencies[] = [
                'currency_code' => $currencyCode,
                'total_invoiced_minor' => $invoiced[$currencyCode] ?? 0,
                'outstanding_minor' => $outstanding[$currencyCode] ?? 0,
                'paid_minor' => $paid[$currencyCode] ?? 0,
            ];
        }

        return [
            'currencies' => $currencies,
            'overdue_count' => Invoice::query()->where('due_date', '<', today())->where('balance_due_minor', '>', 0)->count(),
            'invoice_count' => Invoice::query()->count(),
            'payment_count' => Payment::query()->count(),
        ];
    }

    /** @return array<string,int> */
    private function sumByCurrency(Builder $query, string $column): array
    {
        return $query
            ->select('currency_code')
            ->selectRaw("SUM({$column}) AS aggregate_minor")
            ->groupBy('currency_code')
            ->pluck('aggregate_minor', 'currency_code')
            ->mapWithKeys(static fn (mixed $value, mixed $currency): array => [strtoupper((string) $currency) => (int) $value])
            ->all();
    }
}
