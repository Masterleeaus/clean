<?php

declare(strict_types=1);

it('rejects domain extensions and duplicate capabilities', function (): void {
    $registry = app(App\Titan\Extensions\ExtensionRegistry::class);

    expect(fn () => $registry->validateManifest(['type' => 'domain']))->toThrow(InvalidArgumentException::class);
});

it('stores Vault values encrypted', function (): void {
    $source = file_get_contents(base_path('app/Titan/Vault/DatabaseVault.php'));

    expect($source)->toContain('Crypt::encryptString')
        ->and($source)->toContain('Crypt::decryptString')
        ->and($source)->not->toContain("'value' => \$value");
});
