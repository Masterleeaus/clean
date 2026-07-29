<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\AI\Actions;

use App\Domains\Company\Models\Company;
use App\Domains\Company\Models\CompanyMembership;
use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CurrentCompany;
use Illuminate\Auth\Access\AuthorizationException;

trait ConcernsResolvesAgentCompany
{
    private function setCompanyForWorkflow(object $workflow, array $config, object $run): Company
    {
        $companyId = (string) ($config['company_id'] ?? '');
        if ($companyId === '') {
            throw new AuthorizationException('A company_id is required for Titan Money agent actions.');
        }

        $allowed = CompanyMembership::query()
            ->where('company_id', $companyId)
            ->where('user_id', $workflow->user_id)
            ->where('is_active', true)
            ->exists();
        if (! $allowed) {
            throw new AuthorizationException('The workflow owner cannot access the requested company.');
        }

        $company = Company::query()->findOrFail($companyId);
        app(CurrentCompany::class)->set($company);
        app(ActorContext::class)->setAgent('ai-agent.workflow.'.$workflow->id, (string) $run->id);

        return $company;
    }
}
