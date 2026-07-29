<?php

declare(strict_types=1);
namespace App\Domains\TitanMoney\Http\Controllers;
use App\Domains\TitanMoney\Models\Product;
use App\Domains\TitanMoney\Support\DecimalParser;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
final class ProductController extends Controller
{
 public function index(Request $request){$products=Product::query()->when($request->q,fn($q,$v)=>$q->where('name','like',"%{$v}%"))->latest()->paginate(20);return view('titanmoney::products.index',compact('products'));}
 public function create(){return view('titanmoney::products.form',['product'=>new Product]);}
 public function store(Request $r){$data=$this->validated($r);$data['unit_price_minor']=$this->minor($data['unit_price']);unset($data['unit_price']);$p=Product::create($data);return redirect()->route('titanmoney.products.index')->with('success','Product created.');}
 public function edit(Product $product){return view('titanmoney::products.form',compact('product'));}
 public function update(Request $r,Product $product){$data=$this->validated($r);$data['unit_price_minor']=$this->minor($data['unit_price']);unset($data['unit_price']);$product->update($data);return redirect()->route('titanmoney.products.index')->with('success','Product updated.');}
 private function validated(Request $r):array{return $r->validate(['sku'=>'nullable|string|max:100','name'=>'required|string|max:255','description'=>'nullable|string','unit'=>'required|string|max:50','unit_price'=>'required|decimal:0,2|min:0','currency_code'=>'required|string|size:3','stock_quantity'=>'nullable|numeric','stock_alert_quantity'=>'nullable|numeric','is_active'=>'nullable|boolean']);}
 private function minor(mixed $v):int{return DecimalParser::minorUnits((string)$v);}
}
