<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Payments\Actions;

use App\Domains\WorkCore\System\Actions\{ActionHandlerResult, ActionRequest, PendingDomainEvent};
use App\Domains\WorkCore\System\Actions\Contracts\BusinessActionHandlerContract;
use App\Domains\WorkCore\System\Modules\Payments\Contracts\PaymentRepositoryContract;
use App\Domains\WorkCore\System\References\TypedReference;
use InvalidArgumentException;

final class ReconcilePayment implements BusinessActionHandlerContract
{
    public function __construct(private PaymentRepositoryContract $repository) {}

    public function handle(ActionRequest $request): ActionHandlerResult
    {
        $observation = (string) ($request->payload['observation_public_id'] ?? ''); $invoice = (string) ($request->payload['invoice_public_id'] ?? ''); if ($observation === '' || $invoice === '') throw new InvalidArgumentException('Observation and invoice are required.'); $record = $this->repository->reconcilePayment($observation, $invoice, $request->payload, $request->companyId, $request->actorId);
        return new ActionHandlerResult(
            data: $record,
            aggregate: new TypedReference('payment_reconciliation', (string) ($record['public_id'] ?? '')),
            events: [new PendingDomainEvent('workcore.payment.reconciled', 1, ['record' => $record])],
        );
    }
}
