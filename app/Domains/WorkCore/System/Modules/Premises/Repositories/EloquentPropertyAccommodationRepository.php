<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Premises\Repositories;

use App\Domains\WorkCore\System\Contracts\TenantContextContract;
use App\Domains\WorkCore\System\Modules\Premises\Contracts\PropertyAccommodationRepositoryContract;
use App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation\AccommodationDateWindow;
use App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation\AccommodationRateCalculator;
use App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation\AccommodationReservationState;
use App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation\AccommodationStayState;
use App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation\HousekeepingState;
use App\Domains\WorkCore\System\Modules\Premises\Domain\Property\AgreementState;
use App\Domains\WorkCore\System\Modules\Premises\Domain\Property\OccupancyWindow;
use App\Domains\WorkCore\System\Persistence\TenantScopedRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class EloquentPropertyAccommodationRepository extends TenantScopedRepository implements PropertyAccommodationRepositoryContract
{
    public function __construct(ConnectionInterface $db, TenantContextContract $tenant)
    {
        parent::__construct($db, $tenant);
    }

    public function createPartyRole(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $premises = $this->record('tz_premises', $companyId, $this->required($data, 'premises_public_id'));
        $space = $this->optionalRecord('tz_premises_spaces', $companyId, $data['space_public_id'] ?? null);
        if ($space && (int) $space->premises_id !== (int) $premises->id) throw new InvalidArgumentException('Space does not belong to the selected premises.');
        $contact = $this->optionalRecord('tz_customer_contacts', $companyId, $data['contact_public_id'] ?? null);
        $name = trim((string) ($data['display_name'] ?? ($contact->name ?? '')));
        if ($name === '') throw new InvalidArgumentException('display_name is required.');
        $publicId = (string) Str::ulid();
        $this->db->table('tz_premises_party_roles')->insert([
            'public_id' => $publicId, 'company_id' => $companyId, 'premises_id' => $premises->id,
            'space_id' => $space?->id, 'contact_id' => $contact?->id,
            'role_type' => strtolower((string) ($data['role_type'] ?? 'occupant')),
            'display_name' => $name, 'email' => $data['email'] ?? ($contact->email ?? null),
            'phone' => $data['phone'] ?? ($contact->phone ?? null), 'status' => $data['status'] ?? 'active',
            'effective_from' => $data['effective_from'] ?? now(), 'effective_until' => $data['effective_until'] ?? null,
            'managed_by_accommodation' => (bool) ($data['managed_by_accommodation'] ?? false),
            'metadata' => $this->json($data['metadata'] ?? null), 'created_by_user_id' => $actorId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return $this->snapshot('tz_premises_party_roles', $companyId, $publicId);
    }

    public function createAgreement(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $premises = $this->record('tz_premises', $companyId, $this->required($data, 'premises_public_id'));
        $space = $this->optionalRecord('tz_premises_spaces', $companyId, $data['space_public_id'] ?? null);
        if ($space && (int) $space->premises_id !== (int) $premises->id) throw new InvalidArgumentException('Space does not belong to the selected premises.');
        $party = $this->optionalRecord('tz_premises_party_roles', $companyId, $data['party_role_public_id'] ?? null);
        if ($party && (int) $party->premises_id !== (int) $premises->id) throw new InvalidArgumentException('Party role does not belong to the selected premises.');
        $startsAt = $this->required($data, 'starts_at');
        $endsAt = $this->nullable($data['ends_at'] ?? null);
        OccupancyWindow::contains($startsAt, $endsAt, $startsAt);
        $status = strtolower((string) ($data['status'] ?? 'draft'));
        if (! in_array($status, ['draft', 'pending', 'active'], true)) throw new InvalidArgumentException('Invalid initial agreement state.');
        $publicId = (string) Str::ulid();
        $number = $this->nullable($data['agreement_number'] ?? null) ?? 'AGR-'.now()->format('ymd').'-'.Str::upper(substr($publicId, -6));
        $this->db->table('tz_premises_agreements')->insert([
            'public_id' => $publicId, 'company_id' => $companyId, 'premises_id' => $premises->id,
            'primary_space_id' => $space?->id, 'primary_party_role_id' => $party?->id,
            'agreement_type' => strtolower((string) ($data['agreement_type'] ?? 'residency')),
            'agreement_number' => $number, 'status' => $status, 'starts_at' => $startsAt, 'ends_at' => $endsAt,
            'signed_at' => $data['signed_at'] ?? null, 'currency' => strtoupper((string) ($data['currency'] ?? 'AUD')),
            'periodic_amount' => $data['periodic_amount'] ?? null, 'periodic_basis' => $data['periodic_basis'] ?? null,
            'bond_amount' => $data['bond_amount'] ?? null, 'external_reference' => $data['external_reference'] ?? null,
            'managed_by_accommodation' => (bool) ($data['managed_by_accommodation'] ?? false),
            'terms' => $this->json($data['terms'] ?? null), 'metadata' => $this->json($data['metadata'] ?? null),
            'created_by_user_id' => $actorId, 'updated_by_user_id' => $actorId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return $this->snapshot('tz_premises_agreements', $companyId, $publicId);
    }

    public function transitionAgreement(string $publicId, string $status, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        return $this->db->transaction(function () use ($publicId, $status, $data, $companyId, $actorId): array {
            $agreement = $this->locked('tz_premises_agreements', $companyId, $publicId);
            $status = strtolower(trim($status));
            if (! AgreementState::canTransition((string) $agreement->status, $status)) throw new InvalidArgumentException('Invalid agreement state transition.');
            $updates = ['status' => $status, 'updated_by_user_id' => $actorId, 'updated_at' => now()];
            if ($status === 'active') $updates['signed_at'] = $data['signed_at'] ?? ($agreement->signed_at ?? now());
            if (in_array($status, ['ended', 'cancelled'], true)) {
                $updates['ends_at'] = $data['ends_at'] ?? now();
                $updates['termination_reason'] = $data['reason'] ?? null;
            }
            $this->db->table('tz_premises_agreements')->where('id', $agreement->id)->update($updates);
            return $this->snapshot('tz_premises_agreements', $companyId, $publicId);
        }, 3);
    }

    public function allocateOccupancy(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $premises = $this->record('tz_premises', $companyId, $this->required($data, 'premises_public_id'));
        $space = $this->record('tz_premises_spaces', $companyId, $this->required($data, 'space_public_id'));
        $party = $this->record('tz_premises_party_roles', $companyId, $this->required($data, 'party_role_public_id'));
        $agreement = $this->optionalRecord('tz_premises_agreements', $companyId, $data['agreement_public_id'] ?? null);
        if ((int) $space->premises_id !== (int) $premises->id || (int) $party->premises_id !== (int) $premises->id) throw new InvalidArgumentException('Occupancy references must belong to the same premises.');
        if ($agreement && (int) $agreement->premises_id !== (int) $premises->id) throw new InvalidArgumentException('Agreement does not belong to the selected premises.');
        $startsAt = $this->required($data, 'starts_at');
        $endsAt = $this->nullable($data['ends_at'] ?? null);
        OccupancyWindow::contains($startsAt, $endsAt, $startsAt);
        $capacityUsed = max(1, (int) ($data['capacity_used'] ?? 1));
        $this->assertSpaceCapacity($companyId, (int) $space->id, $startsAt, $endsAt, $capacityUsed);
        $publicId = (string) Str::ulid();
        $this->db->table('tz_premises_occupancies')->insert([
            'public_id' => $publicId, 'company_id' => $companyId, 'premises_id' => $premises->id,
            'space_id' => $space->id, 'party_role_id' => $party->id, 'agreement_id' => $agreement?->id,
            'occupancy_type' => strtolower((string) ($data['occupancy_type'] ?? 'resident')),
            'status' => strtolower((string) ($data['status'] ?? 'active')), 'starts_at' => $startsAt, 'ends_at' => $endsAt,
            'capacity_used' => $capacityUsed, 'exclusive' => (bool) ($data['exclusive'] ?? true),
            'managed_by_accommodation' => (bool) ($data['managed_by_accommodation'] ?? false),
            'metadata' => $this->json($data['metadata'] ?? null), 'created_by_user_id' => $actorId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return $this->snapshot('tz_premises_occupancies', $companyId, $publicId);
    }

    public function endOccupancy(string $publicId, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        return $this->db->transaction(function () use ($publicId, $data, $companyId, $actorId): array {
            $occupancy = $this->locked('tz_premises_occupancies', $companyId, $publicId);
            if (! in_array($occupancy->status, ['reserved', 'active', 'suspended'], true)) throw new InvalidArgumentException('Occupancy is not open.');
            $this->db->table('tz_premises_occupancies')->where('id', $occupancy->id)->update([
                'status' => 'ended', 'ends_at' => $data['ends_at'] ?? now(), 'end_reason' => $data['reason'] ?? null,
                'ended_by_user_id' => $actorId, 'updated_at' => now(),
            ]);
            $this->db->table('tz_premises_access_authorisations')->where('company_id', $companyId)
                ->where('occupancy_id', $occupancy->id)->where('status', 'active')->update([
                    'status' => 'revoked', 'valid_until' => $data['ends_at'] ?? now(),
                    'revocation_reason' => $data['reason'] ?? 'Occupancy ended', 'updated_at' => now(),
                ]);
            return $this->snapshot('tz_premises_occupancies', $companyId, $publicId);
        }, 3);
    }

    public function upsertRatePlan(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $premises = $this->optionalRecord('tz_premises', $companyId, $data['premises_public_id'] ?? null);
        $code = strtoupper($this->required($data, 'code'));
        $basis = strtolower((string) ($data['pricing_basis'] ?? 'night'));
        AccommodationRateCalculator::total((float) ($data['base_amount'] ?? 0), $basis, 1, 1, 1);
        $existing = $this->db->table('tz_accommodation_rate_plans')->where('company_id', $companyId)
            ->where('code', $code)->whereNull('deleted_at')->first();
        $values = [
            'premises_id' => $premises?->id, 'code' => $code, 'name' => $this->required($data, 'name'),
            'pricing_basis' => $basis, 'currency' => strtoupper((string) ($data['currency'] ?? 'AUD')),
            'base_amount' => (float) ($data['base_amount'] ?? 0), 'minimum_nights' => max(1, (int) ($data['minimum_nights'] ?? 1)),
            'maximum_nights' => $data['maximum_nights'] ?? null, 'active' => (bool) ($data['active'] ?? true),
            'restrictions' => $this->json($data['restrictions'] ?? null),
            'cancellation_policy' => $this->json($data['cancellation_policy'] ?? null),
            'metadata' => $this->json($data['metadata'] ?? null), 'updated_at' => now(),
        ];
        if ($existing) {
            $this->db->table('tz_accommodation_rate_plans')->where('id', $existing->id)->update($values);
            return $this->snapshot('tz_accommodation_rate_plans', $companyId, (string) $existing->public_id);
        }
        $publicId = (string) Str::ulid();
        $this->db->table('tz_accommodation_rate_plans')->insert($values + [
            'public_id' => $publicId, 'company_id' => $companyId, 'created_at' => now(),
        ]);
        return $this->snapshot('tz_accommodation_rate_plans', $companyId, $publicId);
    }

    public function createReservation(array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $premises = $this->record('tz_premises', $companyId, $this->required($data, 'premises_public_id'));
        $arrival = $this->required($data, 'arrival_at');
        $departure = $this->required($data, 'departure_at');
        $nights = AccommodationDateWindow::nights($arrival, $departure);
        $spaceIds = array_values(array_filter((array) ($data['space_public_ids'] ?? []), 'is_string'));
        if ($spaceIds === [] && is_string($data['primary_space_public_id'] ?? null)) $spaceIds[] = $data['primary_space_public_id'];
        if ($spaceIds === []) throw new InvalidArgumentException('At least one accommodation space is required.');
        $capacity = max(1, (int) ($data['capacity'] ?? ((int) ($data['adults'] ?? 1) + (int) ($data['children'] ?? 0))));
        $spaces = [];
        foreach (array_unique($spaceIds) as $spacePublicId) {
            $space = $this->record('tz_premises_spaces', $companyId, $spacePublicId);
            if ((int) $space->premises_id !== (int) $premises->id) throw new InvalidArgumentException('Reserved space does not belong to the selected premises.');
            if (! $this->spaceAvailable($companyId, (int) $space->id, $arrival, $departure)) throw new InvalidArgumentException("Space {$spacePublicId} is not available.");
            $spaces[] = $space;
        }
        $ratePlan = $this->optionalRecord('tz_accommodation_rate_plans', $companyId, $data['rate_plan_public_id'] ?? null);
        $subtotal = array_key_exists('subtotal_amount', $data)
            ? (float) $data['subtotal_amount']
            : ($ratePlan ? AccommodationRateCalculator::total((float) $ratePlan->base_amount, (string) $ratePlan->pricing_basis, $nights, count($spaces), $capacity) : 0.0);
        $tax = (float) ($data['tax_amount'] ?? 0);
        $total = round($subtotal + $tax, 4);
        if (array_key_exists('total_amount', $data) && abs((float) $data['total_amount'] - $total) > 0.0001) throw new InvalidArgumentException('Reservation total must equal subtotal plus tax.');
        $status = strtolower((string) ($data['status'] ?? 'draft'));
        if (! in_array($status, ['draft', 'tentative', 'confirmed'], true)) throw new InvalidArgumentException('Invalid initial reservation state.');

        return $this->db->transaction(function () use ($data, $companyId, $actorId, $premises, $spaces, $arrival, $departure, $capacity, $ratePlan, $subtotal, $tax, $total, $status): array {
            $publicId = (string) Str::ulid();
            $reservationId = $this->db->table('tz_accommodation_reservations')->insertGetId([
                'public_id' => $publicId, 'company_id' => $companyId, 'premises_id' => $premises->id,
                'primary_space_id' => $spaces[0]->id, 'rate_plan_id' => $ratePlan?->id,
                'reservation_type' => strtolower((string) ($data['reservation_type'] ?? 'short_stay')),
                'status' => $status, 'source_channel' => strtolower((string) ($data['source_channel'] ?? 'direct')),
                'external_reference' => $data['external_reference'] ?? null,
                'primary_guest_name' => $this->required($data, 'primary_guest_name'),
                'primary_guest_email' => $data['primary_guest_email'] ?? null, 'primary_guest_phone' => $data['primary_guest_phone'] ?? null,
                'arrival_at' => $arrival, 'departure_at' => $departure, 'booked_at' => $data['booked_at'] ?? now(),
                'confirmed_at' => $status === 'confirmed' ? now() : null,
                'adults' => max(1, (int) ($data['adults'] ?? 1)), 'children' => max(0, (int) ($data['children'] ?? 0)),
                'capacity' => $capacity, 'currency' => strtoupper((string) ($data['currency'] ?? 'AUD')),
                'subtotal_amount' => $subtotal, 'tax_amount' => $tax, 'total_amount' => $total,
                'deposit_amount' => (float) ($data['deposit_amount'] ?? 0),
                'balance_amount' => (float) ($data['balance_amount'] ?? $total),
                'notes' => $data['notes'] ?? null, 'metadata' => $this->json($data['metadata'] ?? null),
                'created_by_user_id' => $actorId, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $perSpaceSubtotal = round($subtotal / count($spaces), 4);
            $perSpaceTax = round($tax / count($spaces), 4);
            foreach ($spaces as $space) {
                $this->db->table('tz_accommodation_reservation_spaces')->insert([
                    'company_id' => $companyId, 'reservation_id' => $reservationId, 'space_id' => $space->id,
                    'rate_plan_id' => $ratePlan?->id, 'arrival_at' => $arrival, 'departure_at' => $departure,
                    'capacity' => max(1, (int) ceil($capacity / count($spaces))), 'unit_amount' => $perSpaceSubtotal,
                    'tax_amount' => $perSpaceTax, 'total_amount' => round($perSpaceSubtotal + $perSpaceTax, 4),
                    'status' => in_array($status, ['tentative', 'confirmed'], true) ? $status : 'draft',
                    'metadata' => $this->json(null), 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $guests = (array) ($data['guests'] ?? []);
            if ($guests === []) $guests = [[
                'display_name' => $data['primary_guest_name'], 'email' => $data['primary_guest_email'] ?? null,
                'phone' => $data['primary_guest_phone'] ?? null, 'is_primary' => true,
            ]];
            foreach ($guests as $index => $guest) {
                $this->db->table('tz_accommodation_guests')->insert([
                    'public_id' => (string) Str::ulid(), 'company_id' => $companyId, 'reservation_id' => $reservationId,
                    'guest_type' => strtolower((string) ($guest['guest_type'] ?? 'guest')),
                    'display_name' => trim((string) ($guest['display_name'] ?? $data['primary_guest_name'])),
                    'email' => $guest['email'] ?? null, 'phone' => $guest['phone'] ?? null,
                    'date_of_birth' => $guest['date_of_birth'] ?? null, 'is_primary' => (bool) ($guest['is_primary'] ?? $index === 0),
                    'accessibility_requirements' => $guest['accessibility_requirements'] ?? null,
                    'emergency_contact' => $this->json($guest['emergency_contact'] ?? null),
                    'metadata' => $this->json($guest['metadata'] ?? null), 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            if ($this->nullable($data['external_reference'] ?? null)) {
                $this->db->table('tz_accommodation_channel_references')->insert([
                    'public_id' => (string) Str::ulid(), 'company_id' => $companyId, 'premises_id' => $premises->id,
                    'space_id' => $spaces[0]->id, 'reservation_id' => $reservationId,
                    'channel' => strtolower((string) ($data['source_channel'] ?? 'direct')), 'entity_type' => 'reservation',
                    'external_id' => (string) $data['external_reference'], 'external_url' => $data['external_url'] ?? null,
                    'status' => 'active', 'metadata' => $this->json(null), 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            return $this->reservationSnapshot($companyId, $publicId);
        }, 3);
    }

    public function transitionReservation(string $publicId, string $status, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        return $this->db->transaction(function () use ($publicId, $status, $data, $companyId): array {
            $reservation = $this->locked('tz_accommodation_reservations', $companyId, $publicId);
            $status = strtolower(trim($status));
            if (! AccommodationReservationState::canTransition((string) $reservation->status, $status)) throw new InvalidArgumentException('Invalid reservation state transition.');
            $updates = ['status' => $status, 'updated_at' => now()];
            if ($status === 'confirmed') $updates['confirmed_at'] = $data['confirmed_at'] ?? now();
            if ($status === 'cancelled') { $updates['cancelled_at'] = now(); $updates['cancellation_reason'] = $data['reason'] ?? null; }
            $this->db->table('tz_accommodation_reservations')->where('id', $reservation->id)->update($updates);
            $spaceStatus = AccommodationReservationState::isCapacityConsuming($status) ? $status : 'released';
            $this->db->table('tz_accommodation_reservation_spaces')->where('reservation_id', $reservation->id)->update(['status' => $spaceStatus, 'updated_at' => now()]);
            return $this->reservationSnapshot($companyId, $publicId);
        }, 3);
    }

    public function checkIn(string $reservationPublicId, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        return $this->db->transaction(function () use ($reservationPublicId, $data, $companyId, $actorId): array {
            $reservation = $this->locked('tz_accommodation_reservations', $companyId, $reservationPublicId);
            if (! in_array($reservation->status, ['confirmed', 'checked_in'], true)) throw new InvalidArgumentException('Reservation must be confirmed before check-in.');
            $space = isset($data['space_public_id'])
                ? $this->record('tz_premises_spaces', $companyId, (string) $data['space_public_id'])
                : $this->db->table('tz_premises_spaces')->where('id', $reservation->primary_space_id)->first();
            if (! $space || (int) $space->premises_id !== (int) $reservation->premises_id) throw new InvalidArgumentException('Check-in space is invalid.');
            $ready = in_array((string) $space->status, ['active', 'available', 'ready'], true);
            if (! $ready && ! ((bool) ($data['override_not_ready'] ?? false) && trim((string) ($data['override_reason'] ?? '')) !== '')) {
                throw new InvalidArgumentException('Space is not ready for check-in.');
            }
            $guest = isset($data['guest_public_id'])
                ? $this->record('tz_accommodation_guests', $companyId, (string) $data['guest_public_id'])
                : $this->db->table('tz_accommodation_guests')->where('company_id', $companyId)->where('reservation_id', $reservation->id)
                    ->where('is_primary', true)->whereNull('deleted_at')->first();
            if (! $guest || (int) $guest->reservation_id !== (int) $reservation->id) throw new InvalidArgumentException('Reservation guest is invalid.');
            $existingStay = $this->db->table('tz_accommodation_stays')->where('company_id', $companyId)
                ->where('reservation_id', $reservation->id)->where('space_id', $space->id)->whereNull('deleted_at')->first();
            if ($existingStay) return $this->snapshot('tz_accommodation_stays', $companyId, (string) $existingStay->public_id);

            $partyPublicId = (string) Str::ulid();
            $partyId = $this->db->table('tz_premises_party_roles')->insertGetId([
                'public_id' => $partyPublicId, 'company_id' => $companyId, 'premises_id' => $reservation->premises_id,
                'space_id' => $space->id, 'role_type' => 'guest', 'display_name' => $guest->display_name,
                'email' => $guest->email, 'phone' => $guest->phone, 'status' => 'active',
                'effective_from' => $data['checked_in_at'] ?? now(), 'effective_until' => $reservation->departure_at,
                'managed_by_accommodation' => true, 'metadata' => $this->json(['reservation_public_id' => $reservationPublicId]),
                'created_by_user_id' => $actorId, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $agreementPublicId = (string) Str::ulid();
            $agreementId = $this->db->table('tz_premises_agreements')->insertGetId([
                'public_id' => $agreementPublicId, 'company_id' => $companyId, 'premises_id' => $reservation->premises_id,
                'primary_space_id' => $space->id, 'primary_party_role_id' => $partyId,
                'agreement_type' => 'short_stay', 'agreement_number' => 'STAY-'.Str::upper(substr($agreementPublicId, -8)),
                'status' => 'active', 'starts_at' => $data['checked_in_at'] ?? now(), 'ends_at' => $reservation->departure_at,
                'signed_at' => $data['checked_in_at'] ?? now(), 'currency' => $reservation->currency,
                'periodic_amount' => null, 'periodic_basis' => 'stay', 'bond_amount' => null,
                'managed_by_accommodation' => true, 'terms' => $this->json(null),
                'metadata' => $this->json(['reservation_public_id' => $reservationPublicId]),
                'created_by_user_id' => $actorId, 'updated_by_user_id' => $actorId,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $occupancyPublicId = (string) Str::ulid();
            $occupancyId = $this->db->table('tz_premises_occupancies')->insertGetId([
                'public_id' => $occupancyPublicId, 'company_id' => $companyId, 'premises_id' => $reservation->premises_id,
                'space_id' => $space->id, 'party_role_id' => $partyId, 'agreement_id' => $agreementId,
                'occupancy_type' => 'guest', 'status' => 'active', 'starts_at' => $data['checked_in_at'] ?? now(),
                'ends_at' => $reservation->departure_at, 'capacity_used' => 1, 'exclusive' => true,
                'managed_by_accommodation' => true, 'metadata' => $this->json(['reservation_public_id' => $reservationPublicId]),
                'created_by_user_id' => $actorId, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $accessPublicId = (string) Str::ulid();
            $accessId = $this->db->table('tz_premises_access_authorisations')->insertGetId([
                'public_id' => $accessPublicId, 'company_id' => $companyId, 'premises_id' => $reservation->premises_id,
                'space_id' => $space->id, 'party_role_id' => $partyId, 'occupancy_id' => $occupancyId, 'agreement_id' => $agreementId,
                'authorisation_type' => 'guest_stay', 'status' => 'active', 'valid_from' => $data['checked_in_at'] ?? now(),
                'valid_until' => $reservation->departure_at, 'vault_secret_reference' => $data['vault_secret_reference'] ?? null,
                'managed_by_accommodation' => true, 'metadata' => $this->json(null),
                'created_by_user_id' => $actorId, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $stayPublicId = (string) Str::ulid();
            $this->db->table('tz_accommodation_stays')->insert([
                'public_id' => $stayPublicId, 'company_id' => $companyId, 'reservation_id' => $reservation->id,
                'guest_id' => $guest->id, 'premises_id' => $reservation->premises_id, 'space_id' => $space->id,
                'occupancy_id' => $occupancyId, 'agreement_id' => $agreementId, 'access_authorisation_id' => $accessId,
                'status' => 'in_house', 'expected_arrival_at' => $reservation->arrival_at,
                'expected_departure_at' => $reservation->departure_at, 'checked_in_at' => $data['checked_in_at'] ?? now(),
                'check_in_notes' => $data['notes'] ?? null, 'checked_in_by_user_id' => $actorId,
                'metadata' => $this->json(['override_reason' => $data['override_reason'] ?? null]),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->db->table('tz_accommodation_guests')->where('id', $guest->id)->update(['party_role_id' => $partyId, 'occupancy_id' => $occupancyId, 'updated_at' => now()]);
            $this->db->table('tz_accommodation_reservations')->where('id', $reservation->id)->update([
                'status' => 'checked_in', 'occupancy_id' => $occupancyId, 'agreement_id' => $agreementId, 'updated_at' => now(),
            ]);
            $this->db->table('tz_accommodation_reservation_spaces')->where('reservation_id', $reservation->id)->where('space_id', $space->id)->update(['status' => 'checked_in', 'updated_at' => now()]);
            $this->db->table('tz_premises_spaces')->where('id', $space->id)->update(['status' => 'occupied', 'updated_at' => now()]);
            return $this->snapshot('tz_accommodation_stays', $companyId, $stayPublicId);
        }, 3);
    }

    public function checkOut(string $stayPublicId, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        return $this->db->transaction(function () use ($stayPublicId, $data, $companyId, $actorId): array {
            $stay = $this->locked('tz_accommodation_stays', $companyId, $stayPublicId);
            if (! AccommodationStayState::canTransition((string) $stay->status, 'checked_out')) throw new InvalidArgumentException('Stay is not in house.');
            $checkoutAt = $data['checked_out_at'] ?? now();
            $this->db->table('tz_accommodation_stays')->where('id', $stay->id)->update([
                'status' => 'checked_out', 'checked_out_at' => $checkoutAt, 'check_out_notes' => $data['notes'] ?? null,
                'checked_out_by_user_id' => $actorId, 'updated_at' => now(),
            ]);
            if ($stay->occupancy_id) $this->db->table('tz_premises_occupancies')->where('id', $stay->occupancy_id)
                ->where('company_id', $companyId)->where('managed_by_accommodation', true)->update([
                    'status' => 'ended', 'ends_at' => $checkoutAt, 'end_reason' => 'Accommodation checkout',
                    'ended_by_user_id' => $actorId, 'updated_at' => now(),
                ]);
            if ($stay->agreement_id) $this->db->table('tz_premises_agreements')->where('id', $stay->agreement_id)
                ->where('company_id', $companyId)->where('managed_by_accommodation', true)->update([
                    'status' => 'ended', 'ends_at' => $checkoutAt, 'termination_reason' => 'Accommodation checkout',
                    'updated_by_user_id' => $actorId, 'updated_at' => now(),
                ]);
            if ($stay->access_authorisation_id) $this->db->table('tz_premises_access_authorisations')->where('id', $stay->access_authorisation_id)
                ->where('company_id', $companyId)->where('managed_by_accommodation', true)->update([
                    'status' => 'revoked', 'valid_until' => $checkoutAt, 'revocation_reason' => 'Accommodation checkout', 'updated_at' => now(),
                ]);
            $taskPublicId = (string) Str::ulid();
            $this->db->table('tz_accommodation_housekeeping_tasks')->insert([
                'public_id' => $taskPublicId, 'company_id' => $companyId, 'reservation_id' => $stay->reservation_id,
                'stay_id' => $stay->id, 'premises_id' => $stay->premises_id, 'space_id' => $stay->space_id,
                'task_type' => 'turnover', 'status' => 'dirty', 'due_at' => $data['housekeeping_due_at'] ?? now(),
                'notes' => $data['housekeeping_notes'] ?? null, 'evidence_references' => $this->json(null),
                'metadata' => $this->json(null), 'updated_by_user_id' => $actorId, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->db->table('tz_premises_spaces')->where('id', $stay->space_id)->update(['status' => 'cleaning', 'updated_at' => now()]);
            $this->db->table('tz_accommodation_reservation_spaces')->where('reservation_id', $stay->reservation_id)->where('space_id', $stay->space_id)->update(['status' => 'released', 'updated_at' => now()]);
            $remaining = $this->db->table('tz_accommodation_stays')->where('company_id', $companyId)->where('reservation_id', $stay->reservation_id)
                ->where('status', 'in_house')->where('id', '<>', $stay->id)->count();
            if ($remaining === 0) $this->db->table('tz_accommodation_reservations')->where('id', $stay->reservation_id)->update(['status' => 'checked_out', 'updated_at' => now()]);
            $record = $this->snapshot('tz_accommodation_stays', $companyId, $stayPublicId);
            $record['housekeeping_task_public_id'] = $taskPublicId;
            return $record;
        }, 3);
    }

    public function updateHousekeeping(string $taskPublicId, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        return $this->db->transaction(function () use ($taskPublicId, $data, $companyId, $actorId): array {
            $task = $this->locked('tz_accommodation_housekeeping_tasks', $companyId, $taskPublicId);
            $status = strtolower($this->required($data, 'status'));
            if (! HousekeepingState::canTransition((string) $task->status, $status)) throw new InvalidArgumentException('Invalid housekeeping state transition.');
            $updates = ['status' => $status, 'notes' => $data['notes'] ?? $task->notes, 'updated_by_user_id' => $actorId, 'updated_at' => now()];
            if ($status === 'assigned') $updates['assigned_worker_id'] = $this->optionalRecord('tz_workers', $companyId, $data['worker_public_id'] ?? null)?->id;
            if ($status === 'in_progress') $updates['started_at'] = $data['started_at'] ?? now();
            if (in_array($status, ['inspected', 'ready'], true)) $updates[$status === 'ready' ? 'completed_at' : 'inspected_at'] = now();
            if (isset($data['evidence_references'])) $updates['evidence_references'] = $this->json($data['evidence_references']);
            $this->db->table('tz_accommodation_housekeeping_tasks')->where('id', $task->id)->update($updates);
            if ($status === 'ready') $this->db->table('tz_premises_spaces')->where('id', $task->space_id)->update(['status' => 'ready', 'updated_at' => now()]);
            return $this->snapshot('tz_accommodation_housekeeping_tasks', $companyId, $taskPublicId);
        }, 3);
    }

    public function addCharge(string $reservationPublicId, array $data, int $companyId, int $actorId): array
    {
        $this->guard($companyId, $actorId);
        $reservation = $this->record('tz_accommodation_reservations', $companyId, $reservationPublicId);
        $stay = $this->optionalRecord('tz_accommodation_stays', $companyId, $data['stay_public_id'] ?? null);
        if ($stay && (int) $stay->reservation_id !== (int) $reservation->id) throw new InvalidArgumentException('Stay does not belong to the reservation.');
        $reversal = $this->optionalRecord('tz_accommodation_charges', $companyId, $data['reversal_of_charge_public_id'] ?? null);
        if ($reversal && (int) $reversal->reservation_id !== (int) $reservation->id) throw new InvalidArgumentException('Reversed charge does not belong to the reservation.');
        $quantity = max(1, (int) ($data['quantity'] ?? 1));
        $unitAmount = (float) ($data['unit_amount'] ?? 0);
        $tax = (float) ($data['tax_amount'] ?? 0);
        $total = round($unitAmount * $quantity + $tax, 4);
        if ($reversal) { $unitAmount = -abs((float) $reversal->unit_amount); $tax = -abs((float) $reversal->tax_amount); $total = -abs((float) $reversal->total_amount); }
        $publicId = (string) Str::ulid();
        $this->db->table('tz_accommodation_charges')->insert([
            'public_id' => $publicId, 'company_id' => $companyId, 'reservation_id' => $reservation->id,
            'stay_id' => $stay?->id, 'reversal_of_charge_id' => $reversal?->id,
            'charge_type' => strtolower((string) ($data['charge_type'] ?? ($reversal ? 'reversal' : 'accommodation'))),
            'description' => $this->required($data, 'description'), 'quantity' => $quantity,
            'currency' => strtoupper((string) ($data['currency'] ?? $reservation->currency ?? 'AUD')),
            'unit_amount' => $unitAmount, 'tax_amount' => $tax, 'total_amount' => $total,
            'status' => 'posted', 'posted_at' => $data['posted_at'] ?? now(),
            'metadata' => $this->json($data['metadata'] ?? null), 'created_by_user_id' => $actorId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return $this->snapshot('tz_accommodation_charges', $companyId, $publicId);
    }

    public function agreementProfile(string $publicId, int $companyId): array
    {
        $this->assertCompany($companyId);
        $agreement = $this->record('tz_premises_agreements', $companyId, $publicId);
        $record = $this->snapshot('tz_premises_agreements', $companyId, $publicId);
        $record['party_role'] = $agreement->primary_party_role_id ? $this->publicSnapshotById('tz_premises_party_roles', $companyId, (int) $agreement->primary_party_role_id) : null;
        $record['occupancies'] = $this->rows('tz_premises_occupancies', $companyId, ['agreement_id' => $agreement->id]);
        return $record;
    }

    public function occupancyBoard(int $companyId, array $filters = []): array
    {
        $this->assertCompany($companyId);
        $query = $this->db->table('tz_premises_occupancies as o')
            ->join('tz_premises as p', 'p.id', '=', 'o.premises_id')
            ->join('tz_premises_spaces as s', 's.id', '=', 'o.space_id')
            ->join('tz_premises_party_roles as pr', 'pr.id', '=', 'o.party_role_id')
            ->leftJoin('tz_premises_agreements as a', 'a.id', '=', 'o.agreement_id')
            ->where('o.company_id', $companyId)->whereNull('o.deleted_at');
        foreach (['status', 'occupancy_type'] as $field) if (($filters[$field] ?? null) !== null) $query->where("o.{$field}", (string) $filters[$field]);
        if (($filters['premises_public_id'] ?? null) !== null) $query->where('p.public_id', (string) $filters['premises_public_id']);
        return $query->select([
            'o.public_id', 'o.occupancy_type', 'o.status', 'o.starts_at', 'o.ends_at', 'o.capacity_used', 'o.exclusive', 'o.managed_by_accommodation',
            'p.public_id as premises_public_id', 'p.name as premises_name', 's.public_id as space_public_id', 's.name as space_name',
            'pr.public_id as party_role_public_id', 'pr.display_name', 'pr.role_type', 'a.public_id as agreement_public_id', 'a.agreement_number',
        ])->orderBy('p.name')->orderBy('s.name')->get()->map(static fn ($row) => (array) $row)->all();
    }

    public function availability(int $companyId, string $arrivalAt, string $departureAt, int $capacity = 1, ?string $premisesPublicId = null): array
    {
        $this->assertCompany($companyId);
        AccommodationDateWindow::nights($arrivalAt, $departureAt);
        $query = $this->db->table('tz_premises_spaces as s')->join('tz_premises as p', 'p.id', '=', 's.premises_id')
            ->where('s.company_id', $companyId)->whereNull('s.deleted_at')->whereNull('p.deleted_at')
            ->whereIn('s.status', ['active', 'available', 'ready'])->where(function ($q) use ($capacity): void {
                $q->whereNull('s.capacity')->orWhere('s.capacity', '>=', max(1, $capacity));
            });
        if ($premisesPublicId !== null) $query->where('p.public_id', $premisesPublicId);
        $spaces = $query->select(['s.id', 's.public_id', 's.name', 's.space_type', 's.capacity', 's.status', 'p.public_id as premises_public_id', 'p.name as premises_name'])->orderBy('p.name')->orderBy('s.name')->get();
        $available = [];
        foreach ($spaces as $space) if ($this->spaceAvailable($companyId, (int) $space->id, $arrivalAt, $departureAt)) { unset($space->id); $available[] = (array) $space; }
        return ['arrival_at' => $arrivalAt, 'departure_at' => $departureAt, 'capacity' => max(1, $capacity), 'spaces' => $available, 'count' => count($available)];
    }

    public function accommodationBoard(int $companyId, array $filters = []): array
    {
        $this->assertCompany($companyId);
        $query = $this->db->table('tz_accommodation_reservations as r')
            ->join('tz_premises as p', 'p.id', '=', 'r.premises_id')
            ->leftJoin('tz_premises_spaces as s', 's.id', '=', 'r.primary_space_id')
            ->where('r.company_id', $companyId)->whereNull('r.deleted_at');
        foreach (['status', 'source_channel', 'reservation_type'] as $field) if (($filters[$field] ?? null) !== null) $query->where("r.{$field}", (string) $filters[$field]);
        if (($filters['premises_public_id'] ?? null) !== null) $query->where('p.public_id', (string) $filters['premises_public_id']);
        if (($filters['from'] ?? null) !== null) $query->where('r.departure_at', '>', (string) $filters['from']);
        if (($filters['to'] ?? null) !== null) $query->where('r.arrival_at', '<', (string) $filters['to']);
        return $query->select([
            'r.public_id', 'r.reservation_type', 'r.status', 'r.source_channel', 'r.external_reference',
            'r.primary_guest_name', 'r.arrival_at', 'r.departure_at', 'r.capacity', 'r.currency', 'r.total_amount', 'r.balance_amount',
            'p.public_id as premises_public_id', 'p.name as premises_name', 's.public_id as primary_space_public_id', 's.name as primary_space_name',
        ])->orderBy('r.arrival_at')->get()->map(static fn ($row) => (array) $row)->all();
    }

    public function folio(string $reservationPublicId, int $companyId): array
    {
        $this->assertCompany($companyId);
        $reservation = $this->record('tz_accommodation_reservations', $companyId, $reservationPublicId);
        $charges = $this->db->table('tz_accommodation_charges')->where('company_id', $companyId)
            ->where('reservation_id', $reservation->id)->orderBy('posted_at')->get()->map(function ($row): array {
                $record = (array) $row; unset($record['id'], $record['company_id'], $record['reservation_id'], $record['stay_id'], $record['reversal_of_charge_id']); return $record;
            })->all();
        $postedTotal = round(array_sum(array_map(static fn (array $charge): float => (float) $charge['total_amount'], $charges)), 4);
        return ['reservation' => $this->reservationSnapshot($companyId, $reservationPublicId), 'charges' => $charges, 'posted_total' => $postedTotal, 'currency' => $reservation->currency ?? 'AUD'];
    }

    private function assertSpaceCapacity(int $companyId, int $spaceId, string $startsAt, ?string $endsAt, int $capacityUsed): void
    {
        $space = $this->db->table('tz_premises_spaces')->where('company_id', $companyId)->where('id', $spaceId)->whereNull('deleted_at')->first();
        if (! $space) throw new InvalidArgumentException('Space does not belong to the active company.');
        $existing = $this->db->table('tz_premises_occupancies')->where('company_id', $companyId)->where('space_id', $spaceId)
            ->whereIn('status', ['reserved', 'active', 'suspended'])->whereNull('deleted_at')
            ->where(function ($q) use ($endsAt): void { if ($endsAt !== null) $q->where('starts_at', '<', $endsAt); })
            ->where(function ($q) use ($startsAt): void { $q->whereNull('ends_at')->orWhere('ends_at', '>', $startsAt); })
            ->sum('capacity_used');
        $capacity = max(1, (int) ($space->capacity ?? 1));
        if ((int) $existing + $capacityUsed > $capacity) throw new InvalidArgumentException('Space occupancy capacity would be exceeded.');
        if ($this->db->table('tz_accommodation_reservation_spaces')->where('company_id', $companyId)->where('space_id', $spaceId)
            ->whereIn('status', ['tentative', 'confirmed', 'checked_in'])->where('arrival_at', '<', $endsAt ?? '9999-12-31 23:59:59')
            ->where('departure_at', '>', $startsAt)->exists()) throw new InvalidArgumentException('Space is reserved for accommodation during this period.');
    }

    private function spaceAvailable(int $companyId, int $spaceId, string $arrivalAt, string $departureAt): bool
    {
        $reservationConflict = $this->db->table('tz_accommodation_reservation_spaces')->where('company_id', $companyId)->where('space_id', $spaceId)
            ->whereIn('status', ['tentative', 'confirmed', 'checked_in'])->where('arrival_at', '<', $departureAt)->where('departure_at', '>', $arrivalAt)->exists();
        if ($reservationConflict) return false;
        return ! $this->db->table('tz_premises_occupancies')->where('company_id', $companyId)->where('space_id', $spaceId)
            ->whereIn('status', ['reserved', 'active', 'suspended'])->whereNull('deleted_at')
            ->where('starts_at', '<', $departureAt)->where(function ($q) use ($arrivalAt): void { $q->whereNull('ends_at')->orWhere('ends_at', '>', $arrivalAt); })->exists();
    }

    private function reservationSnapshot(int $companyId, string $publicId): array
    {
        $reservation = $this->record('tz_accommodation_reservations', $companyId, $publicId);
        $record = $this->snapshot('tz_accommodation_reservations', $companyId, $publicId);
        $record['spaces'] = $this->db->table('tz_accommodation_reservation_spaces as rs')->join('tz_premises_spaces as s', 's.id', '=', 'rs.space_id')
            ->where('rs.company_id', $companyId)->where('rs.reservation_id', $reservation->id)
            ->select(['s.public_id as space_public_id', 's.name as space_name', 'rs.arrival_at', 'rs.departure_at', 'rs.capacity', 'rs.unit_amount', 'rs.tax_amount', 'rs.total_amount', 'rs.status'])
            ->get()->map(static fn ($row) => (array) $row)->all();
        $record['guests'] = $this->db->table('tz_accommodation_guests')->where('company_id', $companyId)->where('reservation_id', $reservation->id)->whereNull('deleted_at')
            ->select(['public_id', 'guest_type', 'display_name', 'email', 'phone', 'is_primary', 'accessibility_requirements'])->get()->map(static fn ($row) => (array) $row)->all();
        return $record;
    }

    private function record(string $table, int $companyId, string $publicId): object
    {
        $record = $this->db->table($table)->where('company_id', $companyId)->where('public_id', $publicId)->first();
        if (! $record) throw new InvalidArgumentException('Referenced record does not belong to the active company.');
        return $record;
    }

    private function optionalRecord(string $table, int $companyId, mixed $publicId): ?object
    {
        $value = $this->nullable($publicId);
        return $value === null ? null : $this->record($table, $companyId, $value);
    }

    private function locked(string $table, int $companyId, string $publicId): object
    {
        $record = $this->db->table($table)->where('company_id', $companyId)->where('public_id', $publicId)->lockForUpdate()->first();
        if (! $record) throw new InvalidArgumentException('Referenced record does not belong to the active company.');
        return $record;
    }

    private function snapshot(string $table, int $companyId, string $publicId): array
    {
        $record = (array) $this->record($table, $companyId, $publicId);
        foreach (array_keys($record) as $key) {
            if ($key === 'id' || $key === 'company_id' || ($key !== 'public_id' && str_ends_with($key, '_id'))) unset($record[$key]);
        }
        foreach (['metadata', 'terms', 'restrictions', 'cancellation_policy', 'emergency_contact', 'evidence_references'] as $field) if (array_key_exists($field, $record)) $record[$field] = $this->decode($record[$field]);
        return $record;
    }

    private function publicSnapshotById(string $table, int $companyId, int $id): ?array
    {
        $row = $this->db->table($table)->where('company_id', $companyId)->where('id', $id)->first();
        return $row ? $this->snapshot($table, $companyId, (string) $row->public_id) : null;
    }

    private function rows(string $table, int $companyId, array $where): array
    {
        $query = $this->db->table($table)->where('company_id', $companyId);
        foreach ($where as $key => $value) $query->where($key, $value);
        return $query->get()->map(function ($row) use ($table, $companyId): array { return $this->snapshot($table, $companyId, (string) $row->public_id); })->all();
    }

    private function guard(int $companyId, int $actorId): void
    {
        $this->assertCompany($companyId);
        if ($actorId < 1) throw new InvalidArgumentException('A valid actor is required.');
    }

    private function required(array $data, string $key): string
    {
        $value = trim((string) ($data[$key] ?? ''));
        if ($value === '') throw new InvalidArgumentException("{$key} is required.");
        return $value;
    }

    private function nullable(mixed $value): ?string
    {
        if ($value === null) return null;
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function json(mixed $value): ?string
    {
        if ($value === null || $value === [] || $value === '') return null;
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    private function decode(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') return $value;
        try { return json_decode($value, true, 512, JSON_THROW_ON_ERROR); }
        catch (\Throwable) { return $value; }
    }
}
