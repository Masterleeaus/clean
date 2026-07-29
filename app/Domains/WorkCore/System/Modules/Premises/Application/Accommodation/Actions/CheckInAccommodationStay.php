<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Premises\Application\Accommodation\Actions;

use App\Domains\WorkCore\System\Actions\{ActionHandlerResult, ActionRequest, PendingDomainEvent};
use App\Domains\WorkCore\System\Actions\Contracts\BusinessActionHandlerContract;
use App\Domains\WorkCore\System\Modules\Premises\Contracts\PropertyAccommodationRepositoryContract;
use App\Domains\WorkCore\System\References\TypedReference;
use InvalidArgumentException;

final class CheckInAccommodationStay implements BusinessActionHandlerContract
{
    public function __construct(private PropertyAccommodationRepositoryContract $repository) {}

    public function handle(ActionRequest $request): ActionHandlerResult
    {
        $record = $this->repository->checkIn($this->required($request, 'reservation_public_id'), $request->payload, $request->companyId, $request->actorId);
        return new ActionHandlerResult(
            data: $record,
            aggregate: new TypedReference('accommodation_stay', (string) ($record['public_id'] ?? '')),
            events: [new PendingDomainEvent('workcore.accommodation.stay.checked_in', 1, ['record' => $record])],
        );
    }

    private function required(ActionRequest $request, string $key): string
    {
        $value = trim((string) ($request->payload[$key] ?? ''));
        if ($value === '') { throw new InvalidArgumentException("{$key} is required."); }
        return $value;
    }
}
