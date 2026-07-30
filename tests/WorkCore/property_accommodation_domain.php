<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$files = [
    'app/Domains/WorkCore/System/Modules/Premises/Domain/Property/AgreementState.php',
    'app/Domains/WorkCore/System/Modules/Premises/Domain/Property/OccupancyWindow.php',
    'app/Domains/WorkCore/System/Modules/Premises/Domain/Accommodation/AccommodationDateWindow.php',
    'app/Domains/WorkCore/System/Modules/Premises/Domain/Accommodation/AccommodationRateCalculator.php',
    'app/Domains/WorkCore/System/Modules/Premises/Domain/Accommodation/AccommodationReservationState.php',
    'app/Domains/WorkCore/System/Modules/Premises/Domain/Accommodation/AccommodationStayState.php',
    'app/Domains/WorkCore/System/Modules/Premises/Domain/Accommodation/HousekeepingState.php',
];
foreach ($files as $file) {
    if (! is_file($root.'/'.$file)) {
        throw new RuntimeException("Missing domain policy {$file}");
    }
    require_once $root.'/'.$file;
}

use App\Domains\WorkCore\System\Modules\Premises\Domain\Property\AgreementState;
use App\Domains\WorkCore\System\Modules\Premises\Domain\Property\OccupancyWindow;
use App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation\AccommodationDateWindow;
use App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation\AccommodationRateCalculator;
use App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation\AccommodationReservationState;
use App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation\AccommodationStayState;
use App\Domains\WorkCore\System\Modules\Premises\Domain\Accommodation\HousekeepingState;

$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (! $condition) throw new RuntimeException($message);
};

$expect(AgreementState::canTransition('draft', 'active'), 'Draft agreement should activate.');
$expect(AgreementState::canTransition('active', 'ended'), 'Active agreement should end.');
$expect(! AgreementState::canTransition('ended', 'active'), 'Ended agreement cannot reactivate directly.');
$expect(AgreementState::isCapacityConsuming('active'), 'Active agreement consumes occupancy capacity.');
$expect(! AgreementState::isCapacityConsuming('cancelled'), 'Cancelled agreement does not consume capacity.');

$expect(OccupancyWindow::overlaps('2026-08-01', '2026-08-10', '2026-08-09', '2026-08-12'), 'Occupancy overlap must be detected.');
$expect(! OccupancyWindow::overlaps('2026-08-01', '2026-08-10', '2026-08-10', '2026-08-12'), 'Departure boundary may be reused.');
$expect(OccupancyWindow::contains('2026-08-01', null, '2026-12-01'), 'Open occupancy should contain future dates.');

$expect(AccommodationReservationState::canTransition('draft', 'confirmed'), 'Draft reservation should confirm.');
$expect(AccommodationReservationState::canTransition('confirmed', 'checked_in'), 'Confirmed reservation should check in.');
$expect(! AccommodationReservationState::canTransition('checked_out', 'confirmed'), 'Completed reservation cannot reopen.');
$expect(AccommodationReservationState::isCapacityConsuming('confirmed'), 'Confirmed reservation consumes capacity.');
$expect(! AccommodationReservationState::isCapacityConsuming('cancelled'), 'Cancelled reservation releases capacity.');

$expect(AccommodationStayState::canTransition('expected', 'in_house'), 'Expected stay should check in.');
$expect(AccommodationStayState::canTransition('in_house', 'checked_out'), 'In-house stay should check out.');
$expect(! AccommodationStayState::canTransition('checked_out', 'in_house'), 'Checked-out stay cannot resume.');

$expect(HousekeepingState::canTransition('dirty', 'assigned'), 'Dirty room should be assignable.');
$expect(HousekeepingState::canTransition('inspected', 'ready'), 'Inspected room should become ready.');
$expect(! HousekeepingState::canTransition('ready', 'in_progress'), 'Ready room cannot regress directly.');

$expect(AccommodationDateWindow::overlaps('2026-08-01 15:00:00', '2026-08-04 10:00:00', '2026-08-03 15:00:00', '2026-08-05 10:00:00'), 'Stay overlap must be detected.');
$expect(! AccommodationDateWindow::overlaps('2026-08-01 15:00:00', '2026-08-04 10:00:00', '2026-08-04 10:00:00', '2026-08-05 10:00:00'), 'Same-time turnover boundary must be available.');
$expect(AccommodationDateWindow::nights('2026-08-01 15:00:00', '2026-08-02 10:00:00') === 1, 'Overnight stay must count one night.');
$expect(AccommodationDateWindow::nights('2026-08-01', '2026-08-04') === 3, 'Departure date is exclusive.');

$expect(AccommodationRateCalculator::total(100, 'night', 3, 2, 4) === 600.0, 'Night rates price each reserved space.');
$expect(AccommodationRateCalculator::total(700, 'week', 8, 1, 1) === 1400.0, 'Weekly rates charge commenced weeks.');
$expect(AccommodationRateCalculator::total(3000, 'month', 31, 1, 1) === 6000.0, 'Monthly rates charge commenced 30-day periods.');
$expect(AccommodationRateCalculator::total(450, 'stay', 10, 2, 2) === 900.0, 'Stay rates price each space once.');
$expect(AccommodationRateCalculator::total(50, 'person_night', 3, 2, 4) === 600.0, 'Person-night uses booked capacity.');
$expect(AccommodationRateCalculator::currency() === 'AUD', 'Default currency must be AUD.');

fwrite(STDOUT, "Property/accommodation domain: {$checks} checks passed.\n");
