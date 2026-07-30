<?php

declare(strict_types=1);

it('resolves Maps company context and secrets from host-owned services', function (): void {
    $context = file_get_contents(base_path('app/Titan/Maps/MapsCompanyContext.php'));
    $secrets = file_get_contents(base_path('app/Titan/Maps/MapsCredentialResolver.php'));

    expect($context)->toContain('ActiveCompanyContext')
        ->and($context)->not->toContain("input('company_id'")
        ->and($secrets)->toContain('Vault')
        ->and($secrets)->not->toContain('env(');
});

it('promotes Maps candidates only through governed WorkCore actions', function (): void {
    $gateway = file_get_contents(base_path('app/Titan/Maps/MapsWorkCoreGateway.php'));

    expect($gateway)->toContain('BusinessActionDispatcher')
        ->and($gateway)->toContain('ActionRequest')
        ->and($gateway)->not->toContain("DB::table('tz_")
        ->and($gateway)->not->toContain('->insert(');
});

it('keeps Maps disabled by default', function (): void {
    $manifest = json_decode(file_get_contents(base_path('app/Extensions/TitanMapsIntelligence/extension.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($manifest['type'])->toBe('integration')
        ->and($manifest['enabled_by_default'])->toBeFalse();
});
