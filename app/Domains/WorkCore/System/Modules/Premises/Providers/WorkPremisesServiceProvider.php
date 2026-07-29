<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Premises\Providers;

use App\Domains\WorkCore\System\Actions\{ActionDefinition, BusinessActionRegistry};
use App\Domains\WorkCore\System\Contracts\Records\PremisesAccessContract;
use App\Domains\WorkCore\System\Contracts\Records\Adapters\PremisesAccessAdapter;
use App\Domains\WorkCore\System\Modules\Premises\Actions\{
    AddPremisesHazard, AllocatePremisesOccupancy, ApprovePremises, ArchivePremises,
    ChangePremisesAgreementStatus, CreatePremises, CreatePremisesAccessPoint, CreatePremisesAgreement,
    CreatePremisesPartyRole, CreatePremisesServicePlan, CreatePremisesServiceWindow, CreatePremisesSpace,
    EndPremisesOccupancy, GetPremisesProfile, GetPremisesReadiness, LinkPremisesContact, LinkPremisesDocument,
    RecordPremisesMeterReading, RegisterPremisesIdentifier, ResolvePremisesHazard, SearchPremises
};
use App\Domains\WorkCore\System\Modules\Premises\Application\Accommodation\Actions\{
    AddAccommodationCharge, ChangeAccommodationReservationStatus, CheckInAccommodationStay,
    CheckOutAccommodationStay, CreateAccommodationReservation, UpdateAccommodationHousekeeping,
    UpsertAccommodationRatePlan
};
use App\Domains\WorkCore\System\Modules\Premises\Application\Accommodation\ReadModels\{
    GetAccommodationBoard, GetAccommodationFolio, GetPremisesAgreementProfile,
    GetPremisesOccupancyBoard, SearchAccommodationAvailability
};
use App\Domains\WorkCore\System\Modules\Premises\Contracts\{
    PremisesRepositoryContract, PropertyAccommodationRepositoryContract
};
use App\Domains\WorkCore\System\Modules\Premises\Repositories\{
    EloquentPremisesRepository, EloquentPropertyAccommodationRepository
};
use App\Domains\WorkCore\System\ReadModels\{ReadModelDefinition, ReadModelRegistry};
use Illuminate\Support\ServiceProvider;

final class WorkPremisesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PremisesRepositoryContract::class, EloquentPremisesRepository::class);
        $this->app->bind(PropertyAccommodationRepositoryContract::class, EloquentPropertyAccommodationRepository::class);
        $this->app->bind(PremisesAccessContract::class, PremisesAccessAdapter::class);

        $actions = $this->app->make(BusinessActionRegistry::class);
        $premisesDefinitions = [
            ['workcore.premises.create', CreatePremises::class, 'medium', 'create'],
            ['workcore.premises.approve', ApprovePremises::class, 'high', 'approve'],
            ['workcore.premises.archive', ArchivePremises::class, 'critical', 'archive'],
            ['workcore.premises.space.create', CreatePremisesSpace::class, 'medium', 'manage_spaces'],
            ['workcore.premises.hazard.report', AddPremisesHazard::class, 'high', 'manage_hazards'],
            ['workcore.premises.access_point.create', CreatePremisesAccessPoint::class, 'high', 'manage_access'],
            ['workcore.premises.service_window.create', CreatePremisesServiceWindow::class, 'medium', 'manage_service_plans'],
            ['workcore.premises.service_plan.create', CreatePremisesServicePlan::class, 'medium', 'manage_service_plans'],
            ['workcore.premises.meter_reading.record', RecordPremisesMeterReading::class, 'low', 'record_meter_readings'],
            ['workcore.premises.identifier.register', RegisterPremisesIdentifier::class, 'medium', 'manage_identifiers'],
            ['workcore.premises.contact.link', LinkPremisesContact::class, 'medium', 'manage_contacts'],
            ['workcore.premises.document.link', LinkPremisesDocument::class, 'medium', 'manage_documents'],
            ['workcore.premises.hazard.resolve', ResolvePremisesHazard::class, 'high', 'manage_hazards'],
            ['workcore.premises.party_role.create', CreatePremisesPartyRole::class, 'medium', 'manage_party_roles'],
            ['workcore.premises.agreement.create', CreatePremisesAgreement::class, 'high', 'manage_agreements'],
            ['workcore.premises.agreement.status', ChangePremisesAgreementStatus::class, 'critical', 'manage_agreements'],
            ['workcore.premises.occupancy.allocate', AllocatePremisesOccupancy::class, 'high', 'manage_occupancies'],
            ['workcore.premises.occupancy.end', EndPremisesOccupancy::class, 'critical', 'manage_occupancies'],
        ];
        foreach ($premisesDefinitions as [$key, $handler, $risk, $permission]) {
            $actions->register(new ActionDefinition(
                key: $key,
                handler: $handler,
                risk: $risk,
                requiresConfirmation: true,
                capability: 'workcore.premises',
                permission: (string) config("workcore.premises.permissions.{$permission}"),
                metadata: ['domain' => 'premises_property_operations', 'canonical_owner' => 'WorkCore Premises'],
            ));
        }

        $accommodationDefinitions = [
            ['workcore.accommodation.rate_plan.upsert', UpsertAccommodationRatePlan::class, 'medium', 'manage_rates'],
            ['workcore.accommodation.reservation.create', CreateAccommodationReservation::class, 'medium', 'manage_reservations'],
            ['workcore.accommodation.reservation.status', ChangeAccommodationReservationStatus::class, 'high', 'manage_reservations'],
            ['workcore.accommodation.stay.check_in', CheckInAccommodationStay::class, 'critical', 'check_in'],
            ['workcore.accommodation.stay.check_out', CheckOutAccommodationStay::class, 'critical', 'check_out'],
            ['workcore.accommodation.housekeeping.update', UpdateAccommodationHousekeeping::class, 'medium', 'housekeeping'],
            ['workcore.accommodation.charge.add', AddAccommodationCharge::class, 'high', 'charges'],
        ];
        foreach ($accommodationDefinitions as [$key, $handler, $risk, $permission]) {
            $actions->register(new ActionDefinition(
                key: $key,
                handler: $handler,
                risk: $risk,
                requiresConfirmation: true,
                capability: 'workcore.accommodation',
                permission: (string) config("workcore.accommodation.permissions.{$permission}"),
                metadata: ['domain' => 'accommodation_operations', 'canonical_owner' => 'WorkCore Premises'],
            ));
        }

        $reads = $this->app->make(ReadModelRegistry::class);
        $premisesView = (string) config('workcore.premises.permissions.view');
        $reads->register(new ReadModelDefinition('workcore.premises.search', SearchPremises::class, 'workcore.premises', permission: $premisesView));
        $reads->register(new ReadModelDefinition('workcore.premises.profile', GetPremisesProfile::class, 'workcore.premises', permission: $premisesView));
        $reads->register(new ReadModelDefinition('workcore.premises.readiness', GetPremisesReadiness::class, 'workcore.premises', permission: $premisesView));
        $reads->register(new ReadModelDefinition('workcore.premises.agreement.profile', GetPremisesAgreementProfile::class, 'workcore.premises', permission: $premisesView));
        $reads->register(new ReadModelDefinition('workcore.premises.occupancy.board', GetPremisesOccupancyBoard::class, 'workcore.premises', permission: $premisesView));

        $accommodationView = (string) config('workcore.accommodation.permissions.view');
        $reads->register(new ReadModelDefinition('workcore.accommodation.availability', SearchAccommodationAvailability::class, 'workcore.accommodation', permission: $accommodationView));
        $reads->register(new ReadModelDefinition('workcore.accommodation.board', GetAccommodationBoard::class, 'workcore.accommodation', permission: $accommodationView));
        $reads->register(new ReadModelDefinition('workcore.accommodation.folio', GetAccommodationFolio::class, 'workcore.accommodation', permission: $accommodationView));
    }

    public function boot(): void
    {
        if ((bool) config('workcore.premises.routes_enabled', false)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }
    }
}
