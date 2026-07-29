<?php

declare(strict_types=1);

namespace App\Domains\WorkCore;

use App\Domains\WorkCore\System\Actions\BusinessActionDispatcher;
use App\Domains\WorkCore\System\Actions\BusinessActionRegistry;
use App\Domains\WorkCore\System\Actions\Contracts\ActionAuditRecorderContract;
use App\Domains\WorkCore\System\Actions\Contracts\ActionIdempotencyStoreContract;
use App\Domains\WorkCore\System\Actions\Contracts\ConfirmationVerifierContract;
use App\Domains\WorkCore\System\Actions\Contracts\DomainEventRecorderContract;
use App\Domains\WorkCore\System\Actions\Contracts\EntitlementResolverContract;
use App\Domains\WorkCore\System\Actions\Infrastructure\DatabaseActionAuditRecorder;
use App\Domains\WorkCore\System\Actions\Infrastructure\DatabaseActionIdempotencyStore;
use App\Domains\WorkCore\System\Actions\Infrastructure\DatabaseDomainEventRecorder;
use App\Domains\WorkCore\System\Actions\Policies\ExplicitConfirmationVerifier;
use App\Domains\WorkCore\System\Api\ApiResponseFactory;
use App\Domains\WorkCore\System\Authorization\CompanyRecordAuthorizer;
use App\Domains\WorkCore\System\Capabilities\CapabilityDefinition;
use App\Domains\WorkCore\System\Capabilities\CapabilityRegistry as WorkCoreCapabilityRegistry;
use App\Domains\WorkCore\System\Context\CompanyOperatingContextResolver;
use App\Domains\WorkCore\System\Context\ContextIdNormalizer;
use App\Domains\WorkCore\System\Context\OperationContext;
use App\Domains\WorkCore\System\Contracts\OperationContextContract;
use App\Domains\WorkCore\System\Contracts\PermissionResolverContract;
use App\Domains\WorkCore\System\Contracts\Records\CompanyAccessContract;
use App\Domains\WorkCore\System\Contracts\Records\CompanyMemberAccessContract;
use App\Domains\WorkCore\System\Contracts\Records\Adapters\CompanyAccessAdapter;
use App\Domains\WorkCore\System\Contracts\Records\Adapters\CompanyMemberAccessAdapter;
use App\Domains\WorkCore\System\Contracts\TenantContextContract;
use App\Domains\WorkCore\System\Contracts\TenantResolverContract;
use App\Domains\WorkCore\System\Host\Contracts\MenuAdapterContract;
use App\Domains\WorkCore\System\Host\Contracts\NotificationAdapterContract;
use App\Domains\WorkCore\System\Host\Contracts\StorageAdapterContract;
use App\Domains\WorkCore\System\Host\Contracts\ToolBridgeContract;
use App\Domains\WorkCore\System\Modules\Assets\Providers\WorkAssetsServiceProvider;
use App\Domains\WorkCore\System\Modules\Attendance\Providers\WorkAttendanceServiceProvider;
use App\Domains\WorkCore\System\Modules\Assurance\Providers\WorkAssuranceServiceProvider;
use App\Domains\WorkCore\System\Modules\CRM\Providers\WorkCRMServiceProvider;
use App\Domains\WorkCore\System\Modules\Catalogue\Providers\WorkCatalogueServiceProvider;
use App\Domains\WorkCore\System\Modules\Dispatch\Providers\WorkDispatchServiceProvider;
use App\Domains\WorkCore\System\Modules\Documents\Providers\WorkDocumentsServiceProvider;
use App\Domains\WorkCore\System\Modules\Fleet\Providers\WorkFleetServiceProvider;
use App\Domains\WorkCore\System\Modules\Finance\Providers\WorkFinanceServiceProvider;
use App\Domains\WorkCore\System\Modules\Forms\Providers\WorkFormsServiceProvider;
use App\Domains\WorkCore\System\Modules\Inventory\Providers\WorkInventoryServiceProvider;
use App\Domains\WorkCore\System\Modules\KnowledgeBase\Providers\WorkKnowledgeBaseServiceProvider;
use App\Domains\WorkCore\System\Modules\Operations\Providers\WorkOperationsServiceProvider;
use App\Domains\WorkCore\System\Modules\Premises\Providers\WorkPremisesServiceProvider;
use App\Domains\WorkCore\System\Modules\Payments\Providers\WorkPaymentsServiceProvider;
use App\Domains\WorkCore\System\Modules\Repairs\Providers\WorkRepairsServiceProvider;
use App\Domains\WorkCore\System\Modules\Reviews\Providers\WorkReviewsServiceProvider;
use App\Domains\WorkCore\System\Modules\Rosters\Providers\WorkRostersServiceProvider;
use App\Domains\WorkCore\System\Modules\Scheduling\Providers\WorkSchedulingServiceProvider;
use App\Domains\WorkCore\System\Modules\Supply\Providers\WorkSupplyServiceProvider;
use App\Domains\WorkCore\System\Modules\Support\Providers\WorkSupportServiceProvider;
use App\Domains\WorkCore\System\Modules\TrustAccounting\Providers\WorkTrustAccountingServiceProvider;
use App\Domains\WorkCore\System\Modules\Workforce\Providers\WorkWorkforceServiceProvider;
use App\Domains\WorkCore\System\Notifications\NotificationDispatcherContract;
use App\Domains\WorkCore\System\Outbox\DatabaseOutboxPublisher;
use App\Domains\WorkCore\System\Outbox\NullOutboxTransport;
use App\Domains\WorkCore\System\Outbox\OutboxPublisherContract;
use App\Domains\WorkCore\System\Outbox\OutboxTransportContract;
use App\Domains\WorkCore\System\ReadModels\{ReadModelExecutor, ReadModelRegistry};
use App\Domains\WorkCore\System\References\RecordTypeRegistry;
use App\Domains\WorkCore\System\Registry\WorkModuleRegistry;
use App\Domains\WorkCore\System\Tenancy\TenantContext;
use App\Domains\WorkCore\System\Validation\CompanyScopedReferenceValidator;
use App\Domains\WorkCore\System\Verticals\TradeCompliance\TradeCompliancePolicy;
use App\Domains\WorkCore\System\Verticals\TradeCompliance\WorkOrderComplianceGate;
use App\Titan\Capabilities\CapabilityRegistry as HostCapabilityRegistry;
use App\Titan\WorkCore\MeetupEntitlementResolver;
use App\Titan\WorkCore\MeetupMenuAdapter;
use App\Titan\WorkCore\MeetupNotificationAdapter;
use App\Titan\WorkCore\MeetupPermissionResolver;
use App\Titan\WorkCore\MeetupStorageAdapter;
use App\Titan\WorkCore\MeetupTenantResolver;
use App\Titan\WorkCore\MeetupToolBridge;
use Illuminate\Support\ServiceProvider;

final class WorkCoreServiceProvider extends ServiceProvider
{
    /** @var array<string,class-string<ServiceProvider>> */
    private const MODULES = [
        'crm' => WorkCRMServiceProvider::class,
        'premises' => WorkPremisesServiceProvider::class,
        'catalogue' => WorkCatalogueServiceProvider::class,
        'operations' => WorkOperationsServiceProvider::class,
        'scheduling' => WorkSchedulingServiceProvider::class,
        'dispatch' => WorkDispatchServiceProvider::class,
        'workforce' => WorkWorkforceServiceProvider::class,
        'rosters' => WorkRostersServiceProvider::class,
        'assets' => WorkAssetsServiceProvider::class,
        'inventory' => WorkInventoryServiceProvider::class,
        'forms' => WorkFormsServiceProvider::class,
        'attendance' => WorkAttendanceServiceProvider::class,
        'documents' => WorkDocumentsServiceProvider::class,
        'assurance' => WorkAssuranceServiceProvider::class,
        'support' => WorkSupportServiceProvider::class,
        'knowledge' => WorkKnowledgeBaseServiceProvider::class,
        'reviews' => WorkReviewsServiceProvider::class,
        'supply' => WorkSupplyServiceProvider::class,
        'fleet' => WorkFleetServiceProvider::class,
        'repairs' => WorkRepairsServiceProvider::class,
        'finance' => WorkFinanceServiceProvider::class,
        'payments' => WorkPaymentsServiceProvider::class,
        'trust_accounting' => WorkTrustAccountingServiceProvider::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(config_path('workcore.php'), 'workcore');
        $this->mergeConfigFrom(config_path('trade_compliance.php'), 'trade_compliance');

        $this->app->scoped(TenantContextContract::class, static fn (): TenantContext => new TenantContext());
        $this->app->scoped(OperationContextContract::class, static fn (): OperationContext => new OperationContext());
        $this->app->bind(TenantResolverContract::class, MeetupTenantResolver::class);
        $this->app->bind(PermissionResolverContract::class, MeetupPermissionResolver::class);
        $this->app->bind(EntitlementResolverContract::class, MeetupEntitlementResolver::class);
        $this->app->bind(MenuAdapterContract::class, MeetupMenuAdapter::class);
        $this->app->bind(StorageAdapterContract::class, MeetupStorageAdapter::class);
        $this->app->bind(NotificationAdapterContract::class, MeetupNotificationAdapter::class);
        $this->app->bind(NotificationDispatcherContract::class, MeetupNotificationAdapter::class);
        $this->app->bind(ToolBridgeContract::class, MeetupToolBridge::class);

        $this->app->singleton(ContextIdNormalizer::class);
        $this->app->singleton(CompanyOperatingContextResolver::class);
        $this->app->singleton(CompanyScopedReferenceValidator::class);
        $this->app->singleton(CompanyRecordAuthorizer::class);
        $this->app->singleton(WorkModuleRegistry::class);
        $this->app->singleton(WorkCoreCapabilityRegistry::class);
        $this->app->singleton(BusinessActionRegistry::class);
        $this->app->singleton(ReadModelRegistry::class);
        $this->app->scoped(ReadModelExecutor::class);
        $this->app->singleton(RecordTypeRegistry::class, static fn (): RecordTypeRegistry => new RecordTypeRegistry([]));
        $this->app->scoped(ApiResponseFactory::class);
        $this->app->bind(CompanyAccessContract::class, CompanyAccessAdapter::class);
        $this->app->bind(CompanyMemberAccessContract::class, CompanyMemberAccessAdapter::class);

        $this->app->bind(ConfirmationVerifierContract::class, ExplicitConfirmationVerifier::class);
        $this->app->bind(ActionIdempotencyStoreContract::class, DatabaseActionIdempotencyStore::class);
        $this->app->bind(ActionAuditRecorderContract::class, DatabaseActionAuditRecorder::class);
        $this->app->bind(DomainEventRecorderContract::class, DatabaseDomainEventRecorder::class);
        $this->app->bind(OutboxTransportContract::class, NullOutboxTransport::class);
        $this->app->bind(OutboxPublisherContract::class, DatabaseOutboxPublisher::class);
        $this->app->scoped(BusinessActionDispatcher::class);

        $this->app->singleton(TradeCompliancePolicy::class, static fn (): TradeCompliancePolicy => new TradeCompliancePolicy(
            array_keys((array) config('trade_compliance.requirement_types', [])),
        ));
        $this->app->scoped(WorkOrderComplianceGate::class);

        require_once __DIR__ . '/System/Support/helpers.php';

        if (! (bool) config('workcore.enabled', true)) {
            return;
        }

        $this->registerCapabilities();
        $this->registerModules();
    }

    public function boot(MenuAdapterContract $menu): void
    {
        if ((bool) config('workcore.enabled', true)) {
            $menu->register();
        }
    }

    private function registerCapabilities(): void
    {
        $workCore = $this->app->make(WorkCoreCapabilityRegistry::class);
        $host = $this->app->make(HostCapabilityRegistry::class);

        foreach ((array) config('workcore.capabilities', []) as $key) {
            $id = trim((string) $key);
            if ($id === '') {
                continue;
            }
            $definition = new CapabilityDefinition(
                key: $id,
                provider: 'WorkCore',
                version: (string) config('workcore.version', '0.2.0'),
                requires: ['host.active-company-context', 'host.permissions', 'host.audit'],
                metadata: ['kind' => 'domain', 'canonical_owner' => 'WorkCore'],
            );
            $workCore->register($definition);
            $host->register([
                'id' => $id,
                'provider' => 'workcore',
                'version' => (string) config('workcore.version', '0.2.0'),
                'kind' => 'domain',
                'canonical_owner' => 'WorkCore',
            ]);
        }
    }

    private function registerModules(): void
    {
        $modules = $this->app->make(WorkModuleRegistry::class);
        foreach (self::MODULES as $key => $provider) {
            $modules->register($key, $provider);
        }
    }
}
