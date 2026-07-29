<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CreditNote extends Model
{
    use BelongsToCompany, HasUlids;
    protected $table = 'titan_money_credit_notes';
    protected $fillable = ['company_id','invoice_id','credit_number','status','currency_code','subtotal_minor','tax_minor','total_minor','reason_code','reason','issued_at','created_by_user_id','metadata_json'];
    protected function casts(): array { return ['subtotal_minor'=>'integer','tax_minor'=>'integer','total_minor'=>'integer','issued_at'=>'datetime','metadata_json'=>'array']; }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
}
