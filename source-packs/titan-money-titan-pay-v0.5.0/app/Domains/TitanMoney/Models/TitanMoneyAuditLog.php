<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class TitanMoneyAuditLog extends Model
{
    use BelongsToCompany, HasUlids;
    public $timestamps = false;
    protected $table = 'titan_money_audit_logs';
    protected $fillable = ['company_id','entity_type','entity_id','action','actor_user_id','actor_agent_id','device_id','conversation_id','correlation_id','causation_id','reason','before_state_json','after_state_json','changes_json','signature','created_at'];
    protected function casts(): array { return ['before_state_json'=>'array','after_state_json'=>'array','changes_json'=>'array','created_at'=>'datetime']; }
}
