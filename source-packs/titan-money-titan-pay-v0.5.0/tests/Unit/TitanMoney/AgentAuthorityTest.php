<?php

use App\Domains\TitanMoney\AI\AgentAuthority;

it('does nothing while invoice automation is disabled', function (): void {
    expect((new AgentAuthority())->invoiceDecision(['enabled'=>false], 55000, 'completed_job'))->toBe('skip');
});

it('auto issues only an approved source inside the company authority limit', function (): void {
    $policy = [
        'enabled'=>true,
        'autonomy_level'=>'bounded',
        'max_auto_issue_minor'=>100000,
        'settings'=>['auto_issue'=>true,'allowed_source_types'=>['completed_job']],
    ];

    expect((new AgentAuthority())->invoiceDecision($policy, 55000, 'completed_job'))->toBe('issue')
        ->and((new AgentAuthority())->invoiceDecision($policy, 150000, 'completed_job'))->toBe('approval')
        ->and((new AgentAuthority())->invoiceDecision($policy, 55000, 'manual'))->toBe('approval');
});
