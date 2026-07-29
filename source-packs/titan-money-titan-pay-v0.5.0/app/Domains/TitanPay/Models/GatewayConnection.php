<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class GatewayConnection extends Model
{
    use BelongsToCompany, HasUlids;
    protected $table = 'titanpay_gateway_connections';
    protected $fillable = ['company_id','provider','display_name','vault_secret_reference','merchant_account_reference','environment','configuration_json','minimum_amount_minor','maximum_amount_minor','currency_code','is_active'];
    protected function casts(): array { return ['configuration_json'=>'array','minimum_amount_minor'=>'integer','maximum_amount_minor'=>'integer','is_active'=>'boolean']; }
}
