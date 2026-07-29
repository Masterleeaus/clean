<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Http\Controllers;

use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\Customer;
use App\Domains\TitanMoney\Models\Product;
use App\Domains\TitanMoney\Models\RecurringInvoice;
use App\Domains\TitanMoney\Support\DecimalParser;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class RecurringInvoiceController extends Controller
{
    public function index(){return view('titanmoney::recurring.index',['records'=>RecurringInvoice::with('customer')->latest()->paginate(20)]);}
    public function create(){return view('titanmoney::recurring.form',['record'=>new RecurringInvoice,'customers'=>Customer::orderBy('name')->get(),'products'=>Product::orderBy('name')->get()]);}
    public function store(Request $r){[$data,$lines]=$this->payload($r);$record=DB::transaction(function()use($data,$lines){$record=RecurringInvoice::create($data);$record->lines()->createMany($lines);return $record;});return redirect()->route('titanmoney.recurring.show',$record)->with('success','Recurring invoice created.');}
    public function show(RecurringInvoice $recurring){return view('titanmoney::recurring.show',['record'=>$recurring->load(['customer','lines','invoices'])]);}
    public function edit(RecurringInvoice $recurring){return view('titanmoney::recurring.form',['record'=>$recurring->load('lines'),'customers'=>Customer::orderBy('name')->get(),'products'=>Product::orderBy('name')->get()]);}
    public function update(Request $r,RecurringInvoice $recurring){[$data,$lines]=$this->payload($r);DB::transaction(function()use($recurring,$data,$lines){$recurring->update($data);$recurring->lines()->delete();$recurring->lines()->createMany($lines);});return redirect()->route('titanmoney.recurring.show',$recurring)->with('success','Recurring invoice updated.');}
    public function pause(RecurringInvoice $recurring){$recurring->update(['status'=>'paused']);return back()->with('success','Recurring invoice paused.');}
    public function resume(RecurringInvoice $recurring){$recurring->update(['status'=>'active']);return back()->with('success','Recurring invoice resumed.');}

    private function payload(Request $r):array
    {
        $companyId=app(CurrentCompany::class)->require()->id;
        $d=$r->validate([
            'customer_id'=>['required','string',Rule::exists('titan_money_customers','id')->where(fn($q)=>$q->where('company_id',$companyId))],
            'service_agreement_id'=>'nullable|string|max:191',
            'frequency'=>'required|in:daily,weekly,fortnightly,monthly,quarterly,semiannual,six_monthly,annual',
            'interval_count'=>'required|integer|min:1|max:365','billing_timing'=>'required|in:advance,arrears','start_date'=>'required|date','end_date'=>'nullable|date|after_or_equal:start_date','next_generation_date'=>'required|date|after_or_equal:start_date','stop_after'=>'nullable|integer|min:1','currency_code'=>'required|string|size:3','tax_inclusive'=>'nullable|boolean','payment_terms_code'=>'required|string|max:30','notes'=>'nullable|string','items'=>'required|array|min:1','items.*.description'=>'required|string','items.*.quantity'=>'required|decimal:0,4|min:0.0001','items.*.unit_price'=>'required|decimal:0,2|min:0','items.*.discount'=>'nullable|decimal:0,2|min:0','items.*.tax_rate'=>'nullable|decimal:0,2|min:0|max:100',
            'items.*.product_id'=>['nullable','string',Rule::exists('titan_money_products','id')->where(fn($q)=>$q->where('company_id',$companyId))],
        ]);
        $lines=[];
        foreach($d['items'] as $i=>$x)$lines[]=['description'=>$x['description'],'quantity'=>(string)$x['quantity'],'unit_price_minor'=>DecimalParser::minorUnits((string)$x['unit_price']),'discount_minor'=>DecimalParser::minorUnits((string)($x['discount']??0)),'tax_rate_basis_points'=>DecimalParser::basisPoints((string)($x['tax_rate']??0)),'product_id'=>$x['product_id']??null,'display_order'=>$i];
        unset($d['items']);$d['status']=$d['status']??'active';$d['currency_code']=strtoupper($d['currency_code']);$d['tax_inclusive']=(bool)($d['tax_inclusive']??false);$d['created_by_user_id']=auth()->id();return[$d,$lines];
    }
}
