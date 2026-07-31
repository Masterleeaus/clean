<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\Actions;

use App\Domains\WorkCore\System\Actions\{ActionHandlerResult, ActionRequest, PendingDomainEvent};
use App\Domains\WorkCore\System\Actions\Contracts\BusinessActionHandlerContract;
use App\Domains\WorkCore\System\Modules\Finance\Contracts\FinanceRepositoryContract;
use App\Domains\WorkCore\System\References\TypedReference;
use InvalidArgumentException;

final class AllocateFinanceCredit implements BusinessActionHandlerContract
{
    public function __construct(private FinanceRepositoryContract $repository) {}

    public function handle(ActionRequest $request): ActionHandlerResult
    {
        $credit = (string) ($request->payload['credit_note_public_id'] ?? ''); $invoice = (string) ($request->payload['invoice_public_id'] ?? ''); if ($credit === '' || $invoice === '') throw new InvalidArgumentException('Credit note and invoice are required.'); $record = $this->repository->allocateCredit($credit, $invoice, $request->payload, $request->companyId, $request->actorId);
        return new ActionHandlerResult(
            data: $record,
            aggregate: new TypedReference('finance_receivable_allocation', (string) ($record['allocation']['public_id'] ?? '')),
            events: [new PendingDomainEvent('workcore.finance.credit.allocated', 1, ['record' => $record])],
        );
    }
}
