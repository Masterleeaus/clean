<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Payments\Actions;

use App\Domains\WorkCore\System\Actions\{ActionHandlerResult, ActionRequest, PendingDomainEvent};
use App\Domains\WorkCore\System\Actions\Contracts\BusinessActionHandlerContract;
use App\Domains\WorkCore\System\Modules\Payments\Contracts\PaymentRepositoryContract;
use App\Domains\WorkCore\System\References\TypedReference;
use InvalidArgumentException;

final class CreatePaymentSession implements BusinessActionHandlerContract
{
    public function __construct(private PaymentRepositoryContract $repository) {}

    public function handle(ActionRequest $request): ActionHandlerResult
    {
        $record = $this->repository->createSession($request->payload, $request->companyId, $request->actorId);
        return new ActionHandlerResult(
            data: $record,
            aggregate: new TypedReference('payment_session', (string) ($record['public_id'] ?? '')),
            events: [new PendingDomainEvent('workcore.payment.session.created', 1, ['record' => $record])],
        );
    }
}
