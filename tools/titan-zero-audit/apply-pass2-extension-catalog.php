<?php

declare(strict_types=1);

$path = dirname(__DIR__, 2) . '/app/Domains/Marketplace/MarketplaceServiceProvider.php';
$contents = file_get_contents($path);
if (! is_string($contents)) {
    throw new RuntimeException("Unable to read {$path}.");
}

$replacements = [
    [
        'search' => "use App\\Support\\TitanZero\\TitanZeroFeatureFlags;\n",
        'replace' => '',
    ],
    [
        'search' => <<<'PHP'
    public function register(): void
    {
        if ($this->featureFlags()->extensionDiscoveryEnabled()) {
            $this->extensionProviderRegister();
        }
    }
PHP,
        'replace' => <<<'PHP'
    public function register(): void
    {
        // Extension providers are registered by App\Providers\ExtensionServiceProvider
        // after manifest validation and explicit enablement.
    }
PHP,
    ],
    [
        'search' => <<<'PHP'
    private function featureFlags(): TitanZeroFeatureFlags
    {
        if ($this->app->bound(TitanZeroFeatureFlags::class)) {
            return $this->app->make(TitanZeroFeatureFlags::class);
        }

        return TitanZeroFeatureFlags::fromArray((array) config('titan-zero', []));
    }

    public function extensionProviderRegister(): void
    {
        foreach (static::$extensionProviders as $provider) {
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }

PHP,
        'replace' => '',
    ],
];

foreach ($replacements as $replacement) {
    if (str_contains($contents, $replacement['replace']) && $replacement['replace'] !== '') {
        continue;
    }
    if (! str_contains($contents, $replacement['search'])) {
        if ($replacement['replace'] === '' && ! str_contains($contents, $replacement['search'])) {
            continue;
        }
        throw new RuntimeException("Expected extension-catalog patch anchor was not found.");
    }
    $contents = str_replace($replacement['search'], $replacement['replace'], $contents, $count);
    if ($count !== 1) {
        throw new RuntimeException("Extension-catalog patch anchor occurred {$count} times; expected once.");
    }
}

file_put_contents($path, $contents);
fwrite(STDOUT, "Pass 2 extension catalog wiring applied.\n");
