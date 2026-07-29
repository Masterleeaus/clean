<?php

declare(strict_types=1);
namespace App\Domains\TitanMoney\Http\Controllers\Api;
use App\Domains\TitanMoney\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
final class InvoiceApiController extends Controller
{
 public function index(Request $r){return response()->json(Invoice::with('customer')->when($r->status,fn($q,$v)=>$q->where('status',$v))->paginate(max(1, min((int)$r->input('per_page',25),100))));}
 public function show(Invoice $invoice){return response()->json($invoice->load(['customer','lines','allocations.payment']));}
}
