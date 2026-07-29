<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ChannelDelivery extends Model
{
    use BelongsToCompany, HasUlids;

    protected $table = 'titan_money_channel_deliveries';
    protected $fillable = ['company_id','invoice_id','follow_up_id','channel','provider','recipient_type','recipient_id','recipient_address','external_message_id','template_key','idempotency_key','status','payload_json','receipt_json','scheduled_at','sent_at','delivered_at','failed_at','handed_off_at','attempts','last_error'];
    protected function casts(): array { return ['payload_json'=>'array','receipt_json'=>'array','scheduled_at'=>'datetime','sent_at'=>'datetime','delivered_at'=>'datetime','failed_at'=>'datetime','handed_off_at'=>'datetime','attempts'=>'integer']; }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function followUp(): BelongsTo { return $this->belongsTo(InvoiceFollowUp::class, 'follow_up_id'); }
}
