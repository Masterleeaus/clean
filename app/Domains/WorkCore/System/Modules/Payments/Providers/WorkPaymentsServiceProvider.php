<?php

declare(strict_types=1);

namespace App\Domains\WorkCore\System\Modules\Payments\Providers;

use App\Domains\WorkCore\System\Actions\{ActionDefinition, BusinessActionRegistry};
use App\Domains\WorkCore\System\Modules\Payments\Actions\{
    CreatePaymentSession, ProposePaymentMatches, ReconcilePayment,
    RecordPaymentAttempt, RecordPaymentObservation
};
use App\Domains\WorkCore\System\Modules\Payments\Contracts\PaymentRepositoryContract;
use App\Domains\WorkCore\System\Modules\Payments\ReadModels\{GetPaymentSession, GetPaymentSummary};
use App\Domains\WorkCore\System\Modules\Payments\Repositories\EloquentPaymentRepository;
use App\Domains\WorkCore\System\ReadModels\{ReadModelDefinition, ReadModelRegistry};
use Illuminate\Support\ServiceProvider;

final class WorkPaymentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentRepositoryContract::class, EloquentPaymentRepository::class);
        $actions = $this->app->make(BusinessActionRegistry::class);
        $definitions = [
            ['workcore.payment.session.create', CreatePaymentSession::class, 'medium', 'create_sessions'],
            ['workcore.payment.attempt.record', RecordPaymentAttempt::class, 'medium', 'record_attempts'],
            ['workcore.payment.observation.record', RecordPaymentObservation::class, 'high', 'record_observations'],
            ['workcore.payment.matches.propose', ProposePaymentMatches::class, 'high', 'match'],
            ['workcore.payment.reconcile', ReconcilePayment::class, 'critical', 'reconcile'],
        ];
        foreach ($definitions as [$key, $handler, $risk, $permission]) {
            $actions->register(new ActionDefinition(
                key: $key,
                handler: $handler,
                risk: $risk,
                requiresConfirmation: true,
                capability: 'workcore.payments',
                permission: (string) config("workcore.payments.permissions.{$permission}"),
                metadata: ['domain' => 'payment_orchestration', 'canonical_owner' => 'ZeroPay Payment Orchestration'],
            ));
        }
        $reads = $this->app->make(ReadModelRegistry::class);
        $view = (string) config('workcore.payments.permissions.view');
        $reads->register(new ReadModelDefinition('workcore.payment.summary', GetPaymentSummary::class, 'workcore.payments', permission: $view));
        $reads->register(new ReadModelDefinition('workcore.payment.session', GetPaymentSession::class, 'workcore.payments', permission: $view));
    }
}
