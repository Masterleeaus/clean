<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Payments\Actions;

use App\Domains\WorkCore\System\Actions\{ActionHandlerResult, ActionRequest, PendingDomainEvent};
use App\Domains\WorkCore\System\Actions\Contracts\BusinessActionHandlerContract;
use App\Domains\WorkCore\System\Modules\Payments\Contracts\PaymentRepositoryContract;
use App\Domains\WorkCore\System\References\TypedReference;
use InvalidArgumentException;

final class ProposePaymentMatches implements BusinessActionHandlerContract
{
    public function __construct(private PaymentRepositoryContract $repository) {}

    public function handle(ActionRequest $request): ActionHandlerResult
    {
        $observation = (string) ($request->payload['observation_public_id'] ?? ''); if ($observation === '') throw new InvalidArgumentException('Payment observation is required.'); $matches = $this->repository->proposeMatches($observation, $request->companyId); $record = ['public_id' => $observation, 'matches' => $matches];
        return new ActionHandlerResult(
            data: $record,
            aggregate: new TypedReference('payment_observation', (string) ($record['public_id'] ?? '')),
            events: [new PendingDomainEvent('workcore.payment.matches.proposed', 1, ['record' => $record])],
        );
    }
}
