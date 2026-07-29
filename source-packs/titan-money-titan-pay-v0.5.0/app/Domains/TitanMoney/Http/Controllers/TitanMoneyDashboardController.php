<?php

declare(strict_types=1);
namespace App\Domains\TitanMoney\Http\Controllers;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\Models\Payment;
use App\Domains\TitanMoney\Services\TitanMoneyAnalyticsService;
use Illuminate\Routing\Controller;
final class TitanMoneyDashboardController extends Controller
{
 public function __invoke(TitanMoneyAnalyticsService $analytics){return view('titanmoney::dashboard',['metrics'=>$analytics->dashboard(),'recentInvoices'=>Invoice::with('customer')->latest()->limit(8)->get(),'recentPayments'=>Payment::latest('received_at')->limit(8)->get()]);}
}
