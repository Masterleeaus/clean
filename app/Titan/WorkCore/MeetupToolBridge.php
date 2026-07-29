<?php

declare(strict_types=1);

namespace App\Titan\WorkCore;

use App\Domains\WorkCore\System\Actions\BusinessActionRegistry;
use App\Domains\WorkCore\System\Host\Contracts\ToolBridgeContract;
use App\Domains\WorkCore\System\ReadModels\ReadModelRegistry;

final class MeetupToolBridge implements ToolBridgeContract
{
    public function __construct(
        private readonly BusinessActionRegistry $actions,
        private readonly ReadModelRegistry $readModels,
    ) {}

    public function tools(): array
    {
        $tools = [];
        foreach ($this->actions->all() as $key => $definition) {
            $tools[$key] = [
                'kind' => 'action',
                'risk' => $definition->risk,
                'requires_confirmation' => $definition->requiresConfirmation,
                'capability' => $definition->capability,
                'permission' => $definition->permission,
                'metadata' => $definition->metadata,
            ];
        }
        foreach ($this->readModels->all() as $key => $definition) {
            $tools[$key] = [
                'kind' => 'read_model',
                'capability' => $definition->capability,
                'permission' => $definition->permission,
                'metadata' => $definition->metadata,
            ];
        }
        ksort($tools);

        return $tools;
    }
}
