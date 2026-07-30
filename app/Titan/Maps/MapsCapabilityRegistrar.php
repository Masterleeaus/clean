<?php

declare(strict_types=1);

namespace App\Titan\Maps;

use App\Extensions\TitanMapsIntelligence\Contracts\CapabilityRegistrar;
use App\Titan\Capabilities\CapabilityRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class MapsCapabilityRegistrar implements CapabilityRegistrar
{
    public function __construct(private readonly CapabilityRegistry $registry) {}

    public function register(array $definition): void
    {
        $id = trim((string) ($definition['id'] ?? ''));
        if ($id === '') {
            throw new \InvalidArgumentException('Maps capabilities require an id.');
        }

        if (! $this->registry->has($id)) {
            $this->registry->register($definition + [
                'provider' => 'titan-maps-intelligence',
                'version' => '1.0.0',
                'kind' => 'quattro-tool',
            ]);
        }

        if (Schema::hasTable('titan_capabilities')) {
            DB::table('titan_capabilities')->updateOrInsert(
                ['id' => $id],
                [
                    'provider' => 'titan-maps-intelligence',
                    'version' => '1.0.0',
                    'enabled' => true,
                    'definition' => json_encode($definition, JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }
}
