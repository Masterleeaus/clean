<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class TitanMoneyOutboxEvent extends Model
{
    use BelongsToCompany, HasUlids;
    protected $table = 'titan_money_outbox_events';
    protected $fillable = ['company_id','event_type','event_version','aggregate_type','aggregate_id','aggregate_version','actor_type','actor_id','conversation_id','correlation_id','causation_id','payload_json','occurred_at','published_at','attempts','last_error'];
    protected function casts(): array { return ['event_version'=>'integer','aggregate_version'=>'integer','payload_json'=>'array','occurred_at'=>'datetime','published_at'=>'datetime','attempts'=>'integer']; }
}
