<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class InvoiceReminder extends Model
{
    use HasUlids;
    protected $table = 'titan_money_invoice_reminders';
    protected $fillable = ['invoice_id','days_offset','type','channel','scheduled_at','status','sent_at','error_message','metadata_json'];
    protected function casts(): array { return ['days_offset'=>'integer','scheduled_at'=>'datetime','sent_at'=>'datetime','metadata_json'=>'array']; }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
}
