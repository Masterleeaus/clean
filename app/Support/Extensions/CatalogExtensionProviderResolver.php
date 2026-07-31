<?php

declare(strict_types=1);

namespace App\Support\Extensions;

use Closure;
use Illuminate\Support\ServiceProvider;

final class CatalogExtensionProviderResolver implements ExtensionProviderResolver
{
    /** @var Closure(string):bool */
    private Closure $providerValidator;

    /** @var list<string> */
    private array $issues = [];

    /** @param null|Closure(string):bool $providerValidator */
    public function __construct(
        private readonly ExtensionCatalog $catalog,
        ?Closure $providerValidator = null,
    ) {
        $this->providerValidator = $providerValidator ?? static fn (string $provider): bool =>
            class_exists($provider) && is_subclass_of($provider, ServiceProvider::class);
    }

    public function resolveEnabledProviders(): array
    {
        $this->issues = [];
        $providers = [];

        foreach ($this->catalog->manifests() as $manifest) {
            if (! $manifest->enabled || ! $this->catalog->isValid($manifest)) {
                continue;
            }

            if ($manifest->provider === null) {
                $this->issues[] = "{$manifest->directory}: enabled extension has no provider.";
                continue;
            }

            if (! ($this->providerValidator)($manifest->provider)) {
                $this->issues[] = "{$manifest->directory}: provider class is not loadable [{$manifest->provider}].";
                continue;
            }

            $providers[$manifest->provider] = $manifest->provider;
        }

        return array_values($providers);
    }

    /** @return list<string> */
    public function issues(): array
    {
        return $this->issues;
    }
}
