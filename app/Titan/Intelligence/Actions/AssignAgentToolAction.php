<?php

declare(strict_types=1);

namespace App\Titan\Intelligence\Actions;

use App\Domains\WorkCore\System\Actions\BusinessActionRegistry;
use App\Domains\WorkCore\System\ReadModels\ReadModelRegistry;
use App\Titan\Capabilities\CapabilityRegistry;
use App\Titan\Intelligence\Contracts\IntelligenceRepository;
use App\Titan\Tenancy\ActiveCompanyContext;
use InvalidArgumentException;

final class AssignAgentToolAction
{
    public function __construct(
        private readonly IntelligenceRepository $repository,
        private readonly ActiveCompanyContext $context,
        private readonly CapabilityRegistry $capabilities,
        private readonly BusinessActionRegistry $actions,
        private readonly ReadModelRegistry $reads,
    ) {}

    public function execute(array $input): array
    {
        $tool = trim((string) ($input['tool_id'] ?? ''));
        if ($tool === '' || (! $this->capabilities->has($tool) && ! $this->actions->has($tool) && ! $this->reads->has($tool))) {
            throw new InvalidArgumentException('Agent tools must be registered before assignment.');
        }
        return ['ok' => true, 'data' => $this->repository->assignAgentTool($input, $this->context->companyId, $this->context->userId)];
    }
}
