<?php

declare(strict_types=1);
namespace App\Domains\TitanPay\Http\Controllers\Api;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanPay\Models\CollectionSession;
use App\Domains\TitanPay\Services\CollectionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
final class SessionApiController extends Controller
{
 public function index(){return response()->json(CollectionSession::with('invoice')->paginate(25));}
 public function show(CollectionSession $session){return response()->json($session->load(['invoice','transactions','claims']));}
 public function store(Request $r,CollectionService $service,CurrentCompany $context){$companyId=$context->require()->id;$data=$r->validate(['invoice_id'=>['required','string',Rule::exists('titan_money_invoices','id')->where(fn($q)=>$q->where('company_id',$companyId))],'methods'=>'nullable|array','methods.*'=>'in:cash,payid,bank_transfer,stripe,paypal']);$created=$service->createForInvoice(Invoice::findOrFail($data['invoice_id']),$data['methods']??config('titanpay.allowed_methods'));return response()->json(['session'=>$created['session'],'payment_url'=>route('titanpay.public.show',['token'=>$created['token']])],201);}
}
