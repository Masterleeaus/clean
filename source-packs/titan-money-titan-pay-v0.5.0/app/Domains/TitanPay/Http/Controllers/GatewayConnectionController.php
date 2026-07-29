<?php

declare(strict_types=1);
namespace App\Domains\TitanPay\Http\Controllers;
use App\Domains\TitanPay\Models\GatewayConnection;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
final class GatewayConnectionController extends Controller
{
 public function index(){return view('titanpay::gateways.index',['connections'=>GatewayConnection::orderBy('provider')->get()]);}
 public function store(Request $r){GatewayConnection::updateOrCreate(['provider'=>$r->validate(['provider'=>'required|in:cash,payid,bank_transfer,stripe,paypal'])['provider'],'merchant_account_reference'=>$r->input('merchant_account_reference')],$this->data($r));return back()->with('success','Titan Pay gateway saved.');}
 public function update(Request $r,GatewayConnection $gateway){$gateway->update($this->data($r));return back()->with('success','Titan Pay gateway updated.');}
 private function data(Request $r):array{return $r->validate(['display_name'=>'required|string|max:255','vault_secret_reference'=>'nullable|string|max:255','merchant_account_reference'=>'nullable|string|max:255','environment'=>'required|in:sandbox,live','currency_code'=>'required|string|size:3','minimum_amount_minor'=>'nullable|integer|min:0','maximum_amount_minor'=>'nullable|integer|min:0','is_active'=>'nullable|boolean']);}
}
