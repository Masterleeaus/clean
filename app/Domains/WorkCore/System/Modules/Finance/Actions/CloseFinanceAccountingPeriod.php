<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\Actions;

use App\Domains\WorkCore\System\Actions\{ActionHandlerResult, ActionRequest, PendingDomainEvent};
use App\Domains\WorkCore\System\Actions\Contracts\BusinessActionHandlerContract;
use App\Domains\WorkCore\System\Modules\Finance\Contracts\FinanceRepositoryContract;
use App\Domains\WorkCore\System\References\TypedReference;
use InvalidArgumentException;

final class CloseFinanceAccountingPeriod implements BusinessActionHandlerContract
{
    public function __construct(private FinanceRepositoryContract $repository) {}

    public function handle(ActionRequest $request): ActionHandlerResult
    {
        $publicId = (string) ($request->payload['accounting_period_public_id'] ?? ''); if ($publicId === '') throw new InvalidArgumentException('Accounting period is required.'); $record = $this->repository->closeAccountingPeriod($publicId, $request->payload, $request->companyId, $request->actorId);
        return new ActionHandlerResult(
            data: $record,
            aggregate: new TypedReference('finance_accounting_period', (string) ($record['public_id'] ?? '')),
            events: [new PendingDomainEvent('workcore.finance.period.closed', 1, ['record' => $record])],
        );
    }
}
