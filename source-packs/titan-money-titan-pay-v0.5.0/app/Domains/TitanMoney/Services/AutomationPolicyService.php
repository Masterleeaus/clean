<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\Company\Models\Company;
use App\Domains\TitanMoney\Models\AutomationPolicy;

final class AutomationPolicyService
{
    /** @return array<string,array<string,mixed>> */
    public function defaults(): array
    {
        return [
            'invoice_generation' => [
                'enabled' => true,
                'autonomy_level' => 'bounded',
                'max_auto_issue_minor' => 100000,
                'channels_json' => ['customer_app','sms','email'],
                'settings_json' => [
                    'auto_issue' => true,
                    'send_on_issue' => true,
                    'payment_terms_days' => 14,
                    'max_events_per_run' => 100,
                    'allowed_source_types' => ['completed_job','approved_quote','recurring_obligation','service_agreement'],
                ],
            ],
            'receivables_follow_up' => [
                'enabled' => true,
                'autonomy_level' => 'bounded',
                'max_auto_issue_minor' => 0,
                'channels_json' => ['customer_app','sms','email'],
                'settings_json' => [
                    'max_invoices_per_run' => 200,
                    'stop_on_dispute' => true,
                    'stages' => [
                        ['key'=>'due_today','days_overdue'=>0,'tone'=>'friendly','channels'=>['customer_app','email'],'template'=>'invoice_due_today'],
                        ['key'=>'overdue_3','days_overdue'=>3,'tone'=>'friendly','channels'=>['customer_app','sms','email'],'template'=>'invoice_overdue_friendly'],
                        ['key'=>'overdue_7','days_overdue'=>7,'tone'=>'firm','channels'=>['customer_app','sms','email'],'template'=>'invoice_overdue_firm'],
                        ['key'=>'overdue_14','days_overdue'=>14,'tone'=>'firm','channels'=>['customer_app','sms','email','internal'],'template'=>'invoice_overdue_escalation','approval_required'=>false,'escalate'=>true],
                        ['key'=>'final_30','days_overdue'=>30,'tone'=>'formal','channels'=>['customer_app','sms','email','internal','voice'],'template'=>'invoice_final_notice','approval_required'=>true,'escalate'=>true],
                    ],
                ],
            ],
            'payment_reconciliation' => [
                'enabled' => false,
                'autonomy_level' => 'observe',
                'max_auto_issue_minor' => 0,
                'channels_json' => ['internal'],
                'settings_json' => ['max_events_per_run' => 100, 'auto_match_exact_references' => false],
            ],
        ];
    }

    /** @return array<string,AutomationPolicy> */
    public function ensureDefaults(Company $company): array
    {
        $policies = [];
        foreach ($this->defaults() as $key => $defaults) {
            $policies[$key] = AutomationPolicy::query()->withoutGlobalScope('company')->firstOrCreate(
                ['company_id' => $company->id, 'agent_key' => $key],
                $defaults,
            );
        }

        return $policies;
    }

    /** @return array<string,mixed> */
    public function authorityPayload(AutomationPolicy $policy): array
    {
        return [
            'enabled' => $policy->enabled,
            'autonomy_level' => $policy->autonomy_level,
            'max_auto_issue_minor' => $policy->max_auto_issue_minor,
            'settings' => $policy->settings_json ?? [],
        ];
    }
}
