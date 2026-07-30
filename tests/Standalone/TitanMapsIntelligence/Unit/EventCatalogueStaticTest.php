<?php

declare(strict_types=1);

return static function (): void {
    $root = dirname(__DIR__, 4).'/app/Extensions/TitanMapsIntelligence/Events';
    foreach ([
        'SearchCreated', 'SearchStarted', 'SearchProgressed', 'SearchCompleted', 'SearchFailed', 'SearchCancelled',
        'ExternalPlaceObserved', 'CandidateCreated', 'CandidateClassified', 'CandidateMatched',
        'CandidateApproved', 'CandidateRejected', 'CandidatePromotionRequested', 'CandidatePromoted', 'CandidatePromotionFailed',
        'TerritoryAnalysisCompleted', 'ProviderRateLimited', 'ProviderConnectionFailed', 'ProviderUsageRecorded',
    ] as $event) {
        assert_true(is_file($root.'/'.$event.'.php'), 'Missing event '.$event);
    }
    $base = (string) file_get_contents($root.'/MapsDomainEvent.php');
    foreach (['companyId', 'userId', 'agentId', 'conversationId', 'correlationId', 'causationId', 'occurredAt'] as $field) {
        assert_true(str_contains($base, $field), 'Event envelope missing '.$field);
    }
};
