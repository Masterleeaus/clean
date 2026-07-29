<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PaymentEvidence extends Model
{
    use BelongsToCompany, HasUlids;
    protected $table = 'titanpay_payment_evidence';
    protected $fillable = ['company_id','payment_claim_id','disk','path','original_name','mime_type','size_bytes','sha256','metadata_json','created_by_user_id'];
    protected function casts(): array { return ['size_bytes'=>'integer','metadata_json'=>'array']; }
    public function claim(): BelongsTo { return $this->belongsTo(PaymentClaim::class,'payment_claim_id'); }
}
