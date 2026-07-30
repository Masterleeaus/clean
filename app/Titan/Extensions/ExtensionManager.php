<?php

declare(strict_types=1);

namespace App\Titan\Extensions;

use App\Titan\Capabilities\CapabilityRegistry;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class ExtensionManager
{
    public function __construct(
        private readonly Application $app,
        private readonly ExtensionRegistry $registry,
        private readonly CapabilityRegistry $capabilities,
    ) {}

    /** @return array<string,string> */
    public function bootEnabledExtensions(): array
    {
        $statuses = [];
        foreach ($this->registry->discover() as $definition) {
            $enabled = $this->isEnabled($definition);
            $compatible = $this->isCompatible($definition);
            $status = ! $enabled ? 'disabled' : (! $compatible ? 'incompatible' : 'ready');
            $error = null;

            if ($enabled && $compatible) {
                try {
                    if (! class_exists($definition->provider)) {
                        throw new \RuntimeException("Extension provider class does not exist: {$definition->provider}");
                    }
                    $this->app->register($definition->provider);
                    foreach ($definition->provides as $capability) {
                        if (! $this->capabilities->has($capability)) {
                            $this->capabilities->register([
                                'id' => $capability,
                                'provider' => $definition->id,
                                'version' => $definition->version,
                                'kind' => 'extension',
                            ]);
                        }
                    }
                    $status = 'enabled';
                } catch (Throwable $exception) {
                    $status = 'failed';
                    $error = $exception->getMessage();
                }
            }

            $statuses[$definition->id] = $status;
            $this->persistStatus($definition, $enabled, $compatible, $status, $error);
        }

        return $statuses;
    }

    private function isEnabled(ExtensionDefinition $definition): bool
    {
        if (! Schema::hasTable('titan_extensions')) {
            return $definition->enabledByDefault;
        }

        $stored = DB::table('titan_extensions')->where('id', $definition->id)->value('enabled');
        return $stored === null ? $definition->enabledByDefault : (bool) $stored;
    }

    private function isCompatible(ExtensionDefinition $definition): bool
    {
        $availableHost = array_values((array) config('titan.host_capabilities', []));
        foreach ((array) ($definition->requires['host'] ?? []) as $requirement) {
            if (! in_array($requirement, $availableHost, true)) {
                return false;
            }
        }

        foreach ((array) ($definition->requires['workcore'] ?? []) as $requirement) {
            if (! $this->capabilities->has('workcore.' . $requirement)) {
                return false;
            }
        }

        return true;
    }

    private function persistStatus(
        ExtensionDefinition $definition,
        bool $enabled,
        bool $compatible,
        string $status,
        ?string $error,
    ): void {
        if (! Schema::hasTable('titan_extensions')) {
            return;
        }

        DB::table('titan_extensions')->updateOrInsert(
            ['id' => $definition->id],
            [
                'name' => $definition->name,
                'version' => $definition->version,
                'type' => $definition->type,
                'provider' => $definition->provider,
                'enabled' => $enabled,
                'compatible' => $compatible,
                'status' => $status,
                'manifest_hash' => hash('sha256', json_encode($definition->manifest, JSON_THROW_ON_ERROR)),
                'last_error' => $error,
                'metadata' => json_encode(['path' => $definition->path], JSON_THROW_ON_ERROR),
                'discovered_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }
}
