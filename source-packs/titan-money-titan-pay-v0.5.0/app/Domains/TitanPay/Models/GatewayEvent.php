<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class GatewayEvent extends Model
{
    use BelongsToCompany, HasUlids;
    protected $table='titanpay_gateway_events';
    protected $fillable=['company_id','provider','event_id','event_type','merchant_account_reference','signature_valid','payload_hash','payload_encrypted','received_at','processed_at','processing_status','failure_reason','idempotency_key','metadata_json'];
    protected function casts(): array { return ['signature_valid'=>'boolean','payload_encrypted'=>'encrypted:array','received_at'=>'datetime','processed_at'=>'datetime','metadata_json'=>'array']; }
}
