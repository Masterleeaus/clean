<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AgentApprovalRequest extends Model
{
    use BelongsToCompany, HasUlids;

    protected $table = 'titan_money_agent_approval_requests';
    protected $fillable = ['company_id','agent_run_id','agent_key','action_key','resource_type','resource_id','status','payload_json','reason','expires_at','decided_at','decided_by_user_id','decision_notes'];
    protected function casts(): array { return ['payload_json'=>'array','expires_at'=>'datetime','decided_at'=>'datetime']; }
    public function run(): BelongsTo { return $this->belongsTo(AgentRun::class, 'agent_run_id'); }
}
