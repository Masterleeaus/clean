<?php

declare(strict_types=1);

it('installs WorkCore only as a first-party domain', function (): void {
    expect(is_file(base_path('app/Domains/WorkCore/WorkCoreServiceProvider.php')))->toBeTrue()
        ->and(is_file(base_path('app/Extensions/WorkCore/extension.json')))->toBeFalse();

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('app/Domains/WorkCore'), FilesystemIterator::SKIP_DOTS)
    );

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $source = file_get_contents($file->getPathname());
            expect($source)->not->toContain('App\\Extensions\\WorkCore');
        }
    }
});

it('keeps donor and broken WorkCore systems outside runtime autoload', function (): void {
    expect(is_dir(base_path('app/Domains/WorkCore/IntegratedSources')))->toBeFalse()
        ->and(is_dir(base_path('app/Domains/WorkCore/System/Rewind')))->toBeFalse()
        ->and(is_dir(base_path('app/Domains/WorkCore/System/Intelligence')))->toBeFalse();
});
