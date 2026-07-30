<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Payments\Contracts;

interface PaymentRepositoryContract
{
    public function createSession(array $data, int $companyId, int $actorId): array;
    public function recordAttempt(string $sessionPublicId, array $data, int $companyId, int $actorId): array;
    public function recordObservation(array $data, int $companyId, int $actorId): array;
    public function proposeMatches(string $observationPublicId, int $companyId): array;
    public function reconcilePayment(string $observationPublicId, string $invoicePublicId, array $data, int $companyId, int $actorId): array;
    public function paymentSummary(int $companyId, array $filters = []): array;
    public function paymentSession(string $publicId, int $companyId): array;
}
