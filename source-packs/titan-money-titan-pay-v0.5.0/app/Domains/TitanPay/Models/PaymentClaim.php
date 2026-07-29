<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PaymentClaim extends Model
{
    use BelongsToCompany, HasUlids;
    protected $table = 'titanpay_payment_claims';
    protected $fillable = ['company_id','collection_session_id','method','amount_minor','currency_code','reference','status','payer_name','payer_email','notes','submitted_at','reviewed_at','reviewed_by_user_id','rejection_reason','metadata_json'];
    protected function casts(): array { return ['amount_minor'=>'integer','submitted_at'=>'datetime','reviewed_at'=>'datetime','metadata_json'=>'array']; }
    public function session(): BelongsTo { return $this->belongsTo(CollectionSession::class,'collection_session_id'); }
    public function evidence(): HasMany { return $this->hasMany(PaymentEvidence::class,'payment_claim_id'); }
}
