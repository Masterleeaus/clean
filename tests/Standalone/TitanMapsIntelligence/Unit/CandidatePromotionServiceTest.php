<?php

declare(strict_types=1);

use App\Extensions\TitanMapsIntelligence\Contracts\AuditRecorder;
use App\Extensions\TitanMapsIntelligence\Contracts\CandidateRepository;
use App\Extensions\TitanMapsIntelligence\Contracts\PermissionAuthorizer;
use App\Extensions\TitanMapsIntelligence\Contracts\WorkCoreCandidateGateway;
use App\Extensions\TitanMapsIntelligence\Exceptions\MapsIntelligenceException;
use App\Extensions\TitanMapsIntelligence\Services\CandidatePromotionService;

return static function (): void {
    $repository = new class implements CandidateRepository {
        public array $recorded = [];
        public function findForCompany(string $companyId, string $candidateId): ?array
        {
            return $candidateId === 'candidate-1' ? [
                'id' => 'candidate-1',
                'company_id' => $companyId,
                'status' => 'approved',
                'review_status' => 'approved',
                'unresolved_match' => false,
                'external_place_id' => 'place-1',
                'name' => 'Northside Plumbing',
                'phone' => '0390000000',
                'website' => 'https://northside.example',
                'public_email' => 'info@northside.example',
                'address' => '1 High St',
                'categories' => ['plumber'],
            ] : null;
        }
        public function recordPromotion(array $promotion): void { $this->recorded[] = $promotion; }
    };
    $authorizer = new class implements PermissionAuthorizer {
        public array $calls = [];
        public function authorize(string $userId, string $companyId, string $permission, array $context = []): void
        {
            $this->calls[] = compact('userId', 'companyId', 'permission', 'context');
            if ($userId === 'denied') {
                throw MapsIntelligenceException::fromCode('MAPS_PERMISSION_DENIED', 'Permission denied.');
            }
        }
    };
    $gateway = new class implements WorkCoreCandidateGateway {
        public array $calls = [];
        public function promote(string $companyId, string $targetType, array $fields, array $context): array
        {
            $this->calls[] = compact('companyId', 'targetType', 'fields', 'context');
            return ['entity_type' => $targetType, 'entity_id' => 'provider-99', 'command_id' => 'cmd-1', 'event_id' => 'evt-1'];
        }
    };
    $audit = new class implements AuditRecorder {
        public array $records = [];
        public function record(array $record): void { $this->records[] = $record; }
    };

    $service = new CandidatePromotionService($repository, $authorizer, $gateway, $audit);
    $result = $service->promote('company-1', 'candidate-1', 'provider', ['name', 'phone', 'website'], [
        'user_id' => 'user-1',
        'conversation_id' => 'conversation-1',
        'correlation_id' => 'correlation-1',
    ]);
    assert_same('provider-99', $result['entity_id']);
    assert_same('titan-maps-intelligence.candidate.promote', $authorizer->calls[0]['permission']);
    assert_same(['name' => 'Northside Plumbing', 'phone' => '0390000000', 'website' => 'https://northside.example'], $gateway->calls[0]['fields']);
    assert_same('candidate.promoted', $audit->records[0]['type']);
    assert_same('evt-1', $repository->recorded[0]['workcore_event_id']);

    assert_throws(
        fn () => $service->promote('company-1', 'candidate-1', 'provider', ['name'], ['user_id' => 'denied']),
        MapsIntelligenceException::class,
        'MAPS_PERMISSION_DENIED'
    );
};
