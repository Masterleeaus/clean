<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\Actions;

use App\Domains\WorkCore\System\Actions\{ActionHandlerResult, ActionRequest, PendingDomainEvent};
use App\Domains\WorkCore\System\Actions\Contracts\BusinessActionHandlerContract;
use App\Domains\WorkCore\System\Modules\Finance\Contracts\FinanceRepositoryContract;
use App\Domains\WorkCore\System\References\TypedReference;
use InvalidArgumentException;

final class ChangeFinanceQuoteStatus implements BusinessActionHandlerContract
{
    public function __construct(private FinanceRepositoryContract $repository) {}

    public function handle(ActionRequest $request): ActionHandlerResult
    {
        $publicId = (string) ($request->payload['quote_public_id'] ?? ''); $status = (string) ($request->payload['status'] ?? ''); if ($publicId === '' || $status === '') throw new InvalidArgumentException('Quote and status are required.'); $record = $this->repository->transitionQuote($publicId, $status, $request->payload, $request->companyId, $request->actorId);
        return new ActionHandlerResult(
            data: $record,
            aggregate: new TypedReference('finance_quote', (string) ($record['public_id'] ?? '')),
            events: [new PendingDomainEvent('workcore.finance.quote.status_changed', 1, ['record' => $record])],
        );
    }
}
