<?php

declare(strict_types=1);
namespace App\Domains\TitanMoney\Http\Controllers;
use App\Domains\TitanMoney\Models\Payment;
use Illuminate\Routing\Controller;
final class PaymentController extends Controller{public function index(){return view('titanmoney::payments.index',['payments'=>Payment::with('allocations.invoice')->latest('received_at')->paginate(20)]);}}
