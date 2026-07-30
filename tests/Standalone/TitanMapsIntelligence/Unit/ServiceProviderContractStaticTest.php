<?php

declare(strict_types=1);

return static function (): void {
    $provider = (string) file_get_contents(dirname(__DIR__, 4).'/app/Extensions/TitanMapsIntelligence/TitanMapsIntelligenceServiceProvider.php');
    foreach ([
        'AuthorisedCompanyContext::class',
        'PermissionAuthorizer::class',
        'SecretResolver::class',
        'AuditRecorder::class',
        'CapabilityRegistrar::class',
        'WorkCoreCandidateGateway::class',
        'PrivateExportStore::class',
    ] as $contract) {
        assert_true(str_contains($provider, $contract), 'Required host contract not enforced: '.$contract);
    }
    assert_true(str_contains($provider, 'RequiredHostContract'));
    assert_true(str_contains($provider, 'registerProvider'));
    assert_true(str_contains($provider, 'CapabilityRegistrar'));
    assert_true(! str_contains($provider, 'NullAuthorizer'));
    assert_true(! str_contains($provider, 'NullSecret'));
};
