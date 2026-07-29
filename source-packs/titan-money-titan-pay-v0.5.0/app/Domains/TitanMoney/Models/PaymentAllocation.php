<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PaymentAllocation extends Model
{
    use HasUlids;
    protected $table = 'titan_money_payment_allocations';
    protected $fillable = ['payment_id','invoice_id','amount_minor','allocated_at','reversed_at','metadata_json'];
    protected function casts(): array { return ['amount_minor'=>'integer','allocated_at'=>'datetime','reversed_at'=>'datetime','metadata_json'=>'array']; }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
}
