<?php

use App\Domains\TitanMoney\AI\FollowUpStageResolver;

it('selects the latest eligible follow-up without repeating completed stages', function (): void {
    $stages = [
        ['key'=>'due_today','days_overdue'=>0],
        ['key'=>'overdue_3','days_overdue'=>3],
        ['key'=>'overdue_7','days_overdue'=>7],
    ];
    $resolver = new FollowUpStageResolver();

    expect($resolver->resolve(9, $stages, []))->toBe('overdue_7')
        ->and($resolver->resolve(9, $stages, ['overdue_7']))->toBeNull()
        ->and($resolver->resolve(9, $stages, ['overdue_3']))->toBe('overdue_7')
        ->and($resolver->resolve(-1, $stages, []))->toBeNull();
});
