<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

/**
 * @param list<array{search:string,replace:string}> $replacements
 */
function patchFile(string $path, array $replacements): void
{
    $contents = file_get_contents($path);
    if (! is_string($contents)) {
        throw new RuntimeException("Unable to read {$path}.");
    }

    foreach ($replacements as $replacement) {
        if (str_contains($contents, $replacement['replace'])) {
            continue;
        }

        if (! str_contains($contents, $replacement['search'])) {
            throw new RuntimeException("Expected patch anchor was not found in {$path}.");
        }

        $contents = str_replace($replacement['search'], $replacement['replace'], $contents, $count);
        if ($count !== 1) {
            throw new RuntimeException("Patch anchor occurred {$count} times in {$path}; expected exactly once.");
        }
    }

    file_put_contents($path, $contents);
}

patchFile($root . '/config/app.php', [
    [
        'search' => "        App\\Domains\\WorkCore\\WorkCoreServiceProvider::class,\n",
        'replace' => "        App\\Providers\\TitanZeroServiceProvider::class,\n",
    ],
]);

patchFile($root . '/app/Domains/Marketplace/MarketplaceServiceProvider.php', [
    [
        'search' => "use Illuminate\\Routing\\Router;\n",
        'replace' => "use App\\Support\\TitanZero\\TitanZeroFeatureFlags;\nuse Illuminate\\Routing\\Router;\n",
    ],
    [
        'search' => <<<'PHP'
    public function register(): void
    {
        $this->extensionProviderRegister();
    }
PHP,
        'replace' => <<<'PHP'
    public function register(): void
    {
        if ($this->featureFlags()->extensionDiscoveryEnabled()) {
            $this->extensionProviderRegister();
        }
    }
PHP,
    ],
    [
        'search' => <<<'PHP'
    public function extensionProviderRegister(): void
    {
PHP,
        'replace' => <<<'PHP'
    private function featureFlags(): TitanZeroFeatureFlags
    {
        if ($this->app->bound(TitanZeroFeatureFlags::class)) {
            return $this->app->make(TitanZeroFeatureFlags::class);
        }

        return TitanZeroFeatureFlags::fromArray((array) config('titan-zero', []));
    }

    public function extensionProviderRegister(): void
    {
PHP,
    ],
]);

patchFile($root . '/app/Extensions/Chatbot/System/ChatbotServiceProvider.php', [
    [
        'search' => "use App\\Http\\Middleware\\CheckTemplateTypeAndPlan;\n",
        'replace' => "use App\\Http\\Middleware\\CheckTemplateTypeAndPlan;\nuse App\\Support\\TitanZero\\TitanZeroFeatureFlags;\n",
    ],
    [
        'search' => <<<'PHP'
        $this->registerTitanAIProviders();
        $this->app->register(WorkCoreAppsIntegrationServiceProvider::class);
PHP,
        'replace' => <<<'PHP'
        $flags = $this->featureFlags();
        $this->registerTitanAIProviders($flags);
        if ($flags->workCoreEnabled()) {
            $this->app->register(WorkCoreAppsIntegrationServiceProvider::class);
        }
PHP,
    ],
    [
        'search' => <<<'PHP'
    private function registerTitanAIProviders(): void
    {
        $providers = [
            \App\Domains\WorkCore\System\AI\Providers\WorkCoreAIServiceProvider::class,
            \App\Services\AI\Integration\WorkCore\TitanAIWorkCoreRuntimeServiceProvider::class,
            \App\Extensions\TitanAIGovernance\System\TitanAIGovernanceServiceProvider::class,
            TitanAIRuntimeServiceProvider::class,
        ];
PHP,
        'replace' => <<<'PHP'
    private function registerTitanAIProviders(TitanZeroFeatureFlags $flags): void
    {
        $providers = [
            \App\Extensions\TitanAIGovernance\System\TitanAIGovernanceServiceProvider::class,
            TitanAIRuntimeServiceProvider::class,
        ];

        if ($flags->workCoreEnabled()) {
            array_unshift(
                $providers,
                \App\Domains\WorkCore\System\AI\Providers\WorkCoreAIServiceProvider::class,
                \App\Services\AI\Integration\WorkCore\TitanAIWorkCoreRuntimeServiceProvider::class,
            );
        }
PHP,
    ],
    [
        'search' => <<<'PHP'
    public function boot(Kernel $kernel): void
    {
PHP,
        'replace' => <<<'PHP'
    private function featureFlags(): TitanZeroFeatureFlags
    {
        if ($this->app->bound(TitanZeroFeatureFlags::class)) {
            return $this->app->make(TitanZeroFeatureFlags::class);
        }

        return TitanZeroFeatureFlags::fromArray((array) config('titan-zero', []));
    }

    public function boot(Kernel $kernel): void
    {
PHP,
    ],
]);

fwrite(STDOUT, "Pass 2 feature-flag wiring applied.\n");
