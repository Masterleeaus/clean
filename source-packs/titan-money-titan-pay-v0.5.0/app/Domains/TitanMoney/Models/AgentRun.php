<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AgentRun extends Model
{
    use BelongsToCompany, HasUlids;

    protected $table = 'titan_money_agent_runs';
    protected $fillable = ['company_id','automation_policy_id','agent_key','trigger_type','status','dry_run','trigger_json','decision_json','result_json','items_examined','items_acted','items_escalated','error_message','correlation_id','started_at','completed_at'];
    protected function casts(): array { return ['dry_run'=>'boolean','trigger_json'=>'array','decision_json'=>'array','result_json'=>'array','items_examined'=>'integer','items_acted'=>'integer','items_escalated'=>'integer','started_at'=>'datetime','completed_at'=>'datetime']; }
    public function policy(): BelongsTo { return $this->belongsTo(AutomationPolicy::class, 'automation_policy_id'); }
    public function approvals(): HasMany { return $this->hasMany(AgentApprovalRequest::class); }
    public function followUps(): HasMany { return $this->hasMany(InvoiceFollowUp::class); }
}
