<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Premises\Contracts;

interface PropertyAccommodationRepositoryContract
{
    public function createPartyRole(array $data, int $companyId, int $actorId): array;
    public function createAgreement(array $data, int $companyId, int $actorId): array;
    public function transitionAgreement(string $publicId, string $status, array $data, int $companyId, int $actorId): array;
    public function allocateOccupancy(array $data, int $companyId, int $actorId): array;
    public function endOccupancy(string $publicId, array $data, int $companyId, int $actorId): array;
    public function upsertRatePlan(array $data, int $companyId, int $actorId): array;
    public function createReservation(array $data, int $companyId, int $actorId): array;
    public function transitionReservation(string $publicId, string $status, array $data, int $companyId, int $actorId): array;
    public function checkIn(string $reservationPublicId, array $data, int $companyId, int $actorId): array;
    public function checkOut(string $stayPublicId, array $data, int $companyId, int $actorId): array;
    public function updateHousekeeping(string $taskPublicId, array $data, int $companyId, int $actorId): array;
    public function addCharge(string $reservationPublicId, array $data, int $companyId, int $actorId): array;
    public function agreementProfile(string $publicId, int $companyId): array;
    public function occupancyBoard(int $companyId, array $filters = []): array;
    public function availability(int $companyId, string $arrivalAt, string $departureAt, int $capacity = 1, ?string $premisesPublicId = null): array;
    public function accommodationBoard(int $companyId, array $filters = []): array;
    public function folio(string $reservationPublicId, int $companyId): array;
}
