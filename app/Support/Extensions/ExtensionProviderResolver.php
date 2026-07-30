<?php

declare(strict_types=1);

namespace App\Support\Extensions;

interface ExtensionProviderResolver
{
    /** @return list<class-string<\Illuminate\Support\ServiceProvider>> */
    public function resolveEnabledProviders(): array;
}
