<?php

declare(strict_types=1);
namespace App\Domains\TitanMoney\Http\Controllers;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Enums\InvoiceStatus;
use App\Domains\TitanMoney\Models\Customer;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\Models\Product;
use App\Domains\TitanMoney\Services\InvoiceDocumentService;
use App\Domains\TitanMoney\Services\InvoiceReminderService;
use App\Domains\TitanMoney\Services\InvoiceService;
use App\Domains\TitanMoney\Support\DecimalParser;
use App\Domains\TitanPay\Services\CollectionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
final class InvoiceController extends Controller
{
 public function index(Request $r){$invoices=Invoice::with('customer')->when($r->status,fn($q,$v)=>$q->where('status',$v))->when($r->q,fn($q,$v)=>$q->where(fn($s)=>$s->where('invoice_number','like',"%{$v}%")->orWhereHas('customer',fn($c)=>$c->where('name','like',"%{$v}%"))))->latest()->paginate(20);return view('titanmoney::invoices.index',compact('invoices'));}
 public function create(){return view('titanmoney::invoices.form',['invoice'=>new Invoice,'customers'=>Customer::where('is_active',true)->orderBy('name')->get(),'products'=>Product::where('is_active',true)->orderBy('name')->get()]);}
 public function store(Request $r,InvoiceService $service){[$data,$lines]=$this->validatedPayload($r);$invoice=$service->createDraft($data,$lines);return redirect()->route('titanmoney.invoices.show',$invoice)->with('success','Invoice draft created.');}
 public function show(Invoice $invoice){$invoice->load(['customer','lines','reminders','allocations.payment']);return view('titanmoney::invoices.show',compact('invoice'));}
 public function edit(Invoice $invoice){abort_unless($invoice->isMutable(),409,'Issued invoices cannot be edited.');return view('titanmoney::invoices.form',['invoice'=>$invoice->load('lines'),'customers'=>Customer::where('is_active',true)->orderBy('name')->get(),'products'=>Product::where('is_active',true)->orderBy('name')->get()]);}
 public function update(Request $r,Invoice $invoice,InvoiceService $service){[$data,$lines]=$this->validatedPayload($r);$service->updateDraft($invoice,$data,$lines);return redirect()->route('titanmoney.invoices.show',$invoice)->with('success','Invoice updated.');}
 public function issue(Invoice $invoice,InvoiceService $service,InvoiceDocumentService $documents,InvoiceReminderService $reminders){$invoice=$service->issue($invoice);$documents->snapshot($invoice);$reminders->schedule($invoice,config('titanmoney.reminder_offsets',[-7,0,7,14]));return back()->with('success','Invoice issued and immutable snapshot created.');}
 public function void(Request $r,Invoice $invoice,InvoiceService $service){$data=$r->validate(['reason'=>'required|string|max:1000']);$service->void($invoice,$data['reason']);return back()->with('success','Invoice voided.');}
 public function download(Invoice $invoice,InvoiceDocumentService $documents){return $documents->download($invoice);}
 public function createCollection(Invoice $invoice,CollectionService $collections){$created=$collections->createForInvoice($invoice);return redirect()->route('titanpay.public.show',['token'=>$created['token']])->with('success','Titan Pay collection session created.');}
 private function validatedPayload(Request $r):array{
  $companyId=app(CurrentCompany::class)->require()->id;
  $data=$r->validate(['customer_id'=>['required','string',Rule::exists('titan_money_customers','id')->where(fn($q)=>$q->where('company_id',$companyId))],'currency_code'=>'required|string|size:3','issue_date'=>'nullable|date','due_date'=>'nullable|date|after_or_equal:issue_date','tax_inclusive'=>'nullable|boolean','payment_terms_code'=>'nullable|string|max:30','notes'=>'nullable|string','customer_message'=>'nullable|string','items'=>'required|array|min:1','items.*.description'=>'required|string|max:1000','items.*.quantity'=>'required|decimal:0,4|min:0.0001','items.*.unit_price'=>'required|decimal:0,2|min:0','items.*.discount'=>'nullable|decimal:0,2|min:0','items.*.tax_rate'=>'nullable|decimal:0,2|min:0|max:100','items.*.product_id'=>['nullable','string',Rule::exists('titan_money_products','id')->where(fn($q)=>$q->where('company_id',$companyId))],'items.*.line_type'=>'nullable|string|max:30']);
  $lines=[];foreach($data['items'] as $i=>$item){$lines[]=['line_type'=>$item['line_type']??'service','description'=>$item['description'],'quantity'=>(string)$item['quantity'],'unit_price_minor'=>DecimalParser::minorUnits((string)$item['unit_price']),'discount_minor'=>DecimalParser::minorUnits((string)($item['discount']??0)),'tax_rate_basis_points'=>DecimalParser::basisPoints((string)($item['tax_rate']??0)),'product_id'=>$item['product_id']??null,'display_order'=>$i];}
  unset($data['items']);$data['tax_inclusive']=(bool)($data['tax_inclusive']??false);$data['currency_code']=strtoupper($data['currency_code']);return[$data,$lines];
 }
}
