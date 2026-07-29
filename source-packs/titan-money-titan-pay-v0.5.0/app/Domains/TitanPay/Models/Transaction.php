<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use App\Domains\TitanMoney\Models\Payment;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Transaction extends Model
{
    use BelongsToCompany, HasUlids;
    protected $table = 'titanpay_transactions';
    protected $fillable = ['company_id','collection_session_id','titan_money_payment_id','provider','gateway_transaction_id','idempotency_key','type','status','amount_minor','fee_minor','currency_code','reference','verified_at','settled_at','refunded_at','metadata_json'];
    protected function casts(): array { return ['amount_minor'=>'integer','fee_minor'=>'integer','verified_at'=>'datetime','settled_at'=>'datetime','refunded_at'=>'datetime','metadata_json'=>'array']; }
    public function session(): BelongsTo { return $this->belongsTo(CollectionSession::class,'collection_session_id'); }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class,'titan_money_payment_id'); }
}
