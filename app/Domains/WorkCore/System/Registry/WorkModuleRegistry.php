<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Registry;

use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;

final class WorkModuleRegistry
{
    /** @var array<string,class-string> */
    private array $providers = [];
    public function __construct(private Application $app) {}
    public function register(string $key, string $provider): void
    {
        if ($key === '' || ! class_exists($provider)) { throw new InvalidArgumentException("Invalid WorkCore module [$key]."); }
        if (isset($this->providers[$key])) { throw new InvalidArgumentException("WorkCore module [$key] is already registered."); }
        $this->providers[$key] = $provider;
        $this->app->register($provider);
    }
    /** @return array<string,class-string> */
    public function all(): array { return $this->providers; }
}
