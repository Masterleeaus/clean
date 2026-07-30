<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (! $condition) throw new RuntimeException($message);
};

$migration = $root.'/database/migrations/2026_07_26_030000_create_tz_property_accommodation_tables.php';
$expect(is_file($migration), 'Property/accommodation migration is missing.');
$content = is_file($migration) ? file_get_contents($migration) : '';
foreach ([
    'tz_premises_party_roles', 'tz_premises_agreements', 'tz_premises_occupancies', 'tz_premises_access_authorisations',
    'tz_accommodation_rate_plans', 'tz_accommodation_reservations', 'tz_accommodation_reservation_spaces',
    'tz_accommodation_guests', 'tz_accommodation_stays', 'tz_accommodation_housekeeping_tasks',
    'tz_accommodation_charges', 'tz_accommodation_channel_references',
] as $table) {
    $expect(str_contains($content, "Schema::create('{$table}'"), "Missing table {$table}.");
}
$expect(substr_count($content, "foreignId('company_id')") >= 12, 'Every new table must be company scoped.');
$expect(str_contains($content, "managed_by_accommodation"), 'Managed-record protection flag is missing.');
$expect(str_contains($content, "reversal_of_charge_id"), 'Folio correction chain is missing.');
$expect(str_contains($content, "default('AUD')"), 'Accommodation money must default to AUD.');
$expect(! str_contains($content, "Schema::create('tz_invoices'"), 'Accommodation must not create invoice authority.');
$expect(! str_contains($content, "Schema::create('tz_payments'"), 'Accommodation must not create payment authority.');

$contract = $root.'/app/Domains/WorkCore/System/Modules/Premises/Contracts/PropertyAccommodationRepositoryContract.php';
$repository = $root.'/app/Domains/WorkCore/System/Modules/Premises/Repositories/EloquentPropertyAccommodationRepository.php';
$expect(is_file($contract), 'Repository contract is missing.');
$expect(is_file($repository), 'Repository implementation is missing.');
$repo = is_file($repository) ? file_get_contents($repository) : '';
foreach (['createAgreement', 'allocateOccupancy', 'endOccupancy', 'createReservation', 'checkIn', 'checkOut', 'availability', 'folio'] as $method) {
    $expect(str_contains($repo, "function {$method}"), "Repository method {$method} is missing.");
}
$expect(str_contains($repo, 'where(\'company_id\', $companyId)'), 'Repository must apply company scope explicitly.');
$expect(str_contains($repo, 'managed_by_accommodation'), 'Checkout must distinguish accommodation-managed records.');
$expect(str_contains($repo, '$key !== \'public_id\''), 'Snapshot scrubbing must preserve public_id.');

fwrite(STDOUT, "Property/accommodation persistence: {$checks} checks passed.\n");
