<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Premises\Actions;

use App\Domains\WorkCore\System\Actions\{ActionHandlerResult, ActionRequest, PendingDomainEvent};
use App\Domains\WorkCore\System\Actions\Contracts\BusinessActionHandlerContract;
use App\Domains\WorkCore\System\Modules\Premises\Contracts\PropertyAccommodationRepositoryContract;
use App\Domains\WorkCore\System\References\TypedReference;
use InvalidArgumentException;

final class CreatePremisesAgreement implements BusinessActionHandlerContract
{
    public function __construct(private PropertyAccommodationRepositoryContract $repository) {}

    public function handle(ActionRequest $request): ActionHandlerResult
    {
        $record = $this->repository->createAgreement($request->payload, $request->companyId, $request->actorId);
        return new ActionHandlerResult(
            data: $record,
            aggregate: new TypedReference('premises_agreement', (string) ($record['public_id'] ?? '')),
            events: [new PendingDomainEvent('workcore.premises.agreement.created', 1, ['record' => $record])],
        );
    }
}
