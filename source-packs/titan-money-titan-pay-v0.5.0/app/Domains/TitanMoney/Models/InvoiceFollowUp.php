<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class InvoiceFollowUp extends Model
{
    use BelongsToCompany, HasUlids;

    protected $table = 'titan_money_invoice_follow_ups';
    protected $fillable = ['company_id','invoice_id','agent_run_id','stage_key','days_overdue','status','tone','channels_json','scheduled_at','queued_at','sent_at','cancelled_at','failure_reason','metadata_json'];
    protected function casts(): array { return ['days_overdue'=>'integer','channels_json'=>'array','scheduled_at'=>'datetime','queued_at'=>'datetime','sent_at'=>'datetime','cancelled_at'=>'datetime','metadata_json'=>'array']; }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function run(): BelongsTo { return $this->belongsTo(AgentRun::class, 'agent_run_id'); }
    public function deliveries(): HasMany { return $this->hasMany(ChannelDelivery::class, 'follow_up_id'); }
}
