<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BillableEvent extends Model
{
    use BelongsToCompany, HasUlids;

    protected $table = 'titan_money_billable_events';
    protected $fillable = ['company_id','source_type','source_id','source_version','idempotency_key','customer_id','payload_json','status','occurred_at','available_at','processed_at','invoice_id','attempts','last_error','correlation_id'];
    protected function casts(): array { return ['payload_json'=>'array','occurred_at'=>'datetime','available_at'=>'datetime','processed_at'=>'datetime','attempts'=>'integer']; }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
}
