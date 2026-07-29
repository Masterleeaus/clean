<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use App\Domains\TitanMoney\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Payment extends Model
{
    use BelongsToCompany, HasUlids;
    protected $table = 'titan_money_payments';
    protected $fillable = ['company_id','payer_type','payer_id','payment_method','currency_code','amount_minor','unallocated_minor','received_at','gateway_provider','gateway_account_id','gateway_transaction_id','bank_transaction_id','reference','status','verified_at','reversed_at','metadata_json','created_by_user_id','created_by_agent_id','device_id','conversation_id','correlation_id'];
    protected function casts(): array { return ['amount_minor'=>'integer','unallocated_minor'=>'integer','received_at'=>'datetime','status'=>PaymentStatus::class,'verified_at'=>'datetime','reversed_at'=>'datetime','metadata_json'=>'array']; }
    public function allocations(): HasMany { return $this->hasMany(PaymentAllocation::class); }
}
