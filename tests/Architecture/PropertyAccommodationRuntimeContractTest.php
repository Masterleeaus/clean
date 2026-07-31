<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void { $checks++; if (! $condition) throw new RuntimeException($message); };

$providerPath = $root.'/app/Domains/WorkCore/System/Modules/Premises/Providers/WorkPremisesServiceProvider.php';
$provider = file_get_contents($providerPath);
$expect(str_contains($provider, 'PropertyAccommodationRepositoryContract'), 'Property/accommodation repository is not bound.');
foreach ([
    'workcore.premises.party_role.create', 'workcore.premises.agreement.create', 'workcore.premises.agreement.status',
    'workcore.premises.occupancy.allocate', 'workcore.premises.occupancy.end',
    'workcore.accommodation.rate_plan.upsert', 'workcore.accommodation.reservation.create',
    'workcore.accommodation.reservation.status', 'workcore.accommodation.stay.check_in',
    'workcore.accommodation.stay.check_out', 'workcore.accommodation.housekeeping.update', 'workcore.accommodation.charge.add',
] as $key) $expect(str_contains($provider, "'{$key}'"), "Action registration {$key} is missing.");
foreach ([
    'workcore.premises.agreement.profile', 'workcore.premises.occupancy.board',
    'workcore.accommodation.availability', 'workcore.accommodation.board', 'workcore.accommodation.folio',
] as $key) $expect(str_contains($provider, "'{$key}'"), "Read registration {$key} is missing.");
$expect(str_contains($provider, "'workcore.accommodation'"), 'Accommodation capability is missing from provider registrations.');
$expect(str_contains($provider, "'critical'"), 'Check-in/out or agreement-ending actions must include critical risk handling.');

$config = file_get_contents($root.'/config/workcore.php');
$expect(str_contains($config, "'workcore.accommodation'"), 'Accommodation capability is missing from config.');
foreach (['view', 'manage_rates', 'manage_reservations', 'check_in', 'check_out', 'housekeeping', 'charges'] as $permission) {
    $expect(str_contains($config, "'{$permission}'"), "Accommodation permission {$permission} is missing.");
}
$expect(! str_contains($provider, 'invoice.create'), 'Premises provider must not create invoice actions.');
$expect(! str_contains($provider, 'payment.capture'), 'Premises provider must not create payment actions.');

$required = [
    'Actions/CreatePremisesPartyRole.php', 'Actions/CreatePremisesAgreement.php', 'Actions/ChangePremisesAgreementStatus.php',
    'Actions/AllocatePremisesOccupancy.php', 'Actions/EndPremisesOccupancy.php',
    'Application/Accommodation/Actions/UpsertAccommodationRatePlan.php',
    'Application/Accommodation/Actions/CreateAccommodationReservation.php',
    'Application/Accommodation/Actions/ChangeAccommodationReservationStatus.php',
    'Application/Accommodation/Actions/CheckInAccommodationStay.php',
    'Application/Accommodation/Actions/CheckOutAccommodationStay.php',
    'Application/Accommodation/Actions/UpdateAccommodationHousekeeping.php',
    'Application/Accommodation/Actions/AddAccommodationCharge.php',
    'Application/Accommodation/ReadModels/GetPremisesAgreementProfile.php',
    'Application/Accommodation/ReadModels/GetPremisesOccupancyBoard.php',
    'Application/Accommodation/ReadModels/SearchAccommodationAvailability.php',
    'Application/Accommodation/ReadModels/GetAccommodationBoard.php',
    'Application/Accommodation/ReadModels/GetAccommodationFolio.php',
];
foreach ($required as $file) $expect(is_file($root.'/app/Domains/WorkCore/System/Modules/Premises/'.$file), "Missing runtime file {$file}.");

fwrite(STDOUT, "Property/accommodation runtime: {$checks} checks passed.\n");
