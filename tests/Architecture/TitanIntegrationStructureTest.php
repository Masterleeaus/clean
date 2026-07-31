<?php

declare(strict_types=1);

it('keeps WorkCore as a first-party domain', function (): void {
    expect(is_file(base_path('app/Domains/WorkCore/WorkCoreServiceProvider.php')))->toBeTrue();
    expect(is_file(base_path('app/Extensions/WorkCore/extension.json')))->toBeFalse();
});

it('installs extensions through the Titan registry', function (): void {
    expect(class_exists(App\Titan\Extensions\ExtensionRegistry::class))->toBeTrue();
});
