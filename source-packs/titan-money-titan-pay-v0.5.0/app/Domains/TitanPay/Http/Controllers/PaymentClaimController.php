<?php

declare(strict_types=1);
namespace App\Domains\TitanPay\Http\Controllers;
use App\Domains\TitanPay\Models\PaymentClaim;
use App\Domains\TitanPay\Services\PaymentClaimService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
final class PaymentClaimController extends Controller
{
 public function index(){return view('titanpay::claims.index',['claims'=>PaymentClaim::with(['session.invoice.customer','evidence'])->latest()->paginate(20)]);}
 public function approve(PaymentClaim $claim,PaymentClaimService $service){$service->approve($claim,(int)auth()->id());return back()->with('success','Payment claim approved and allocated.');}
 public function reject(Request $r,PaymentClaim $claim,PaymentClaimService $service){$data=$r->validate(['reason'=>'required|string|max:1000']);$service->reject($claim,(int)auth()->id(),$data['reason']);return back()->with('success','Payment claim rejected.');}
 public function evidence(PaymentClaim $claim,string $evidence){$item=$claim->evidence()->findOrFail($evidence);return \Illuminate\Support\Facades\Storage::disk($item->disk)->download($item->path,$item->original_name);}
}
