<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class BankDeposit extends Model
{
    use BelongsToCompany, HasUlids;
    protected $table='titanpay_bank_deposits';
    protected $fillable=['company_id','bank_account_id','external_id','transaction_date','description','reference','amount_minor','currency_code','status','matched_collection_session_id','matched_payment_id','metadata_json'];
    protected function casts(): array { return ['transaction_date'=>'date','amount_minor'=>'integer','metadata_json'=>'array']; }
}
