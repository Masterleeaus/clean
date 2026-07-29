<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class BankAccount extends Model
{
    use BelongsToCompany, HasUlids;
    protected $table='titanpay_bank_accounts';
    protected $fillable=['company_id','display_name','account_name','bsb_masked','account_number_masked','payid_type','payid_value_masked','vault_secret_reference','currency_code','is_default','is_active','metadata_json'];
    protected function casts(): array { return ['is_default'=>'boolean','is_active'=>'boolean','metadata_json'=>'array']; }
}
