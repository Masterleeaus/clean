<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class GatewayLog extends Model
{
    use BelongsToCompany, HasUlids;
    protected $table='titanpay_gateway_logs';
    protected $fillable=['company_id','collection_session_id','provider','direction','operation','status','request_json','response_json','http_status','duration_ms','error_message'];
    protected function casts(): array { return ['request_json'=>'array','response_json'=>'array','http_status'=>'integer','duration_ms'=>'integer']; }
}
