<?php

declare(strict_types=1);

namespace App\Domains\TitanZero\Chat;

use App\Domains\Company\Models\Company;
use App\Domains\TitanZero\Workflows\CompleteJobAndInvoiceResult;
use App\Domains\TitanZero\Workflows\CompleteJobAndInvoiceWorkflow;
use App\Models\User;

final class TitanOperationalToolRouter
{
    public function __construct(
        private readonly OperationalIntentParser $parser,
        private readonly CompleteJobAndInvoiceWorkflow $completeJobAndInvoice,
    ) {}

    public function handle(Company $company, User $user, int|string $conversationId, string $message): ?CompleteJobAndInvoiceResult
    {
        $intent = $this->parser->parse($message);
        if (! $intent) {
            return null;
        }

        return match ($intent->action) {
            'complete_job_and_invoice' => $this->completeJobAndInvoice->execute($company, $user, $conversationId, $intent),
            default => null,
        };
    }
}
