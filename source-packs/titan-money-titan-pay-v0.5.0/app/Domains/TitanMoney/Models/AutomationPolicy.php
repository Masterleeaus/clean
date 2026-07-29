<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Models;

use App\Domains\Company\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AutomationPolicy extends Model
{
    use BelongsToCompany, HasUlids;

    protected $table = 'titan_money_automation_policies';
    protected $fillable = ['company_id','agent_key','enabled','autonomy_level','max_auto_issue_minor','settings_json','channels_json','last_run_at','next_run_at','updated_by_user_id'];
    protected function casts(): array { return ['enabled'=>'boolean','max_auto_issue_minor'=>'integer','settings_json'=>'array','channels_json'=>'array','last_run_at'=>'datetime','next_run_at'=>'datetime']; }
    public function runs(): HasMany { return $this->hasMany(AgentRun::class); }
}
