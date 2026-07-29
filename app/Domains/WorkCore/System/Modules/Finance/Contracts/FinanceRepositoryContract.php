<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Finance\Contracts;

interface FinanceRepositoryContract
{
    public function createQuote(array $data, int $companyId, int $actorId): array;
    public function transitionQuote(string $publicId, string $status, array $data, int $companyId, int $actorId): array;
    public function createInvoice(array $data, int $companyId, int $actorId): array;
    public function issueInvoice(string $publicId, array $data, int $companyId, int $actorId): array;
    public function transitionInvoice(string $publicId, string $status, array $data, int $companyId, int $actorId): array;
    public function createCreditNote(array $data, int $companyId, int $actorId): array;
    public function allocateCredit(string $creditPublicId, string $invoicePublicId, array $data, int $companyId, int $actorId): array;
    public function recordExpense(array $data, int $companyId, int $actorId): array;
    public function approveExpense(string $publicId, array $data, int $companyId, int $actorId): array;
    public function allocateReceivable(string $invoicePublicId, array $data, int $companyId, int $actorId): array;
    public function createAccount(array $data, int $companyId, int $actorId): array;
    public function createAccountingPeriod(array $data, int $companyId, int $actorId): array;
    public function closeAccountingPeriod(string $publicId, array $data, int $companyId, int $actorId): array;
    public function postJournal(array $data, int $companyId, int $actorId): array;
    public function financeSummary(int $companyId, array $filters = []): array;
    public function invoiceProfile(string $publicId, int $companyId): array;
    public function quoteProfile(string $publicId, int $companyId): array;
    public function receivables(int $companyId, array $filters = []): array;
}
