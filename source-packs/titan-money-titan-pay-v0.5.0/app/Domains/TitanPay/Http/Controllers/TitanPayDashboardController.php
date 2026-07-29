<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Http\Controllers;

use App\Domains\TitanPay\Models\CollectionSession;
use App\Domains\TitanPay\Models\PaymentClaim;
use App\Domains\TitanPay\Models\Transaction;
use Illuminate\Routing\Controller;

final class TitanPayDashboardController extends Controller
{
    public function __invoke()
    {
        $completedByCurrency = Transaction::query()
            ->where('status', 'verified')
            ->select('currency_code')
            ->selectRaw('SUM(amount_minor) AS total_minor')
            ->groupBy('currency_code')
            ->orderBy('currency_code')
            ->get();

        return view('titanpay::dashboard', [
            'sessions' => CollectionSession::with('invoice.customer')->latest()->limit(10)->get(),
            'claims' => PaymentClaim::with('session.invoice')->where('status', 'submitted')->latest()->limit(10)->get(),
            'completedByCurrency' => $completedByCurrency,
            'pending_count' => CollectionSession::whereIn('status', ['pending', 'processing', 'awaiting_evidence'])->count(),
        ]);
    }
}
