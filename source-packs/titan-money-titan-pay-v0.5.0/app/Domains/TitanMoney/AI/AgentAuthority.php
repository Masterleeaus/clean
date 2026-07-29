<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\AI;

final class AgentAuthority
{
    /**
     * @param array<string,mixed> $policy
     * @return 'skip'|'observe'|'draft'|'issue'|'approval'
     */
    public function invoiceDecision(array $policy, int $amountMinor, string $sourceType): string
    {
        if (! (bool) ($policy['enabled'] ?? false)) {
            return 'skip';
        }

        $level = (string) ($policy['autonomy_level'] ?? 'observe');
        $settings = is_array($policy['settings'] ?? null) ? $policy['settings'] : [];

        if ($level === 'observe') {
            return 'observe';
        }

        if ($level === 'draft' || ! (bool) ($settings['auto_issue'] ?? false)) {
            return 'draft';
        }

        $allowedSources = array_values(array_filter(
            is_array($settings['allowed_source_types'] ?? null) ? $settings['allowed_source_types'] : [],
            'is_string'
        ));

        if ($allowedSources !== [] && ! in_array($sourceType, $allowedSources, true)) {
            return 'approval';
        }

        $limit = max(0, (int) ($policy['max_auto_issue_minor'] ?? 0));
        if ($limit === 0 || $amountMinor > $limit) {
            return 'approval';
        }

        return in_array($level, ['bounded', 'full'], true) ? 'issue' : 'approval';
    }
}
