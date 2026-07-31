<?php

declare(strict_types=1);

namespace App\Http\Controllers\Titan;

use App\Http\Controllers\Controller;
use App\Titan\Tenancy\ActiveCompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class WorkCoreSummaryController extends Controller
{
    /** @var array<string,string> */
    private const TABLES = [
        'customers' => 'tz_customers',
        'premises' => 'tz_premises',
        'work_orders' => 'tz_work_orders',
        'appointments' => 'tz_appointments',
        'workers' => 'tz_workers',
        'assets' => 'tz_assets',
        'documents' => 'tz_documents',
        'evidence' => 'tz_evidence_items',
        'inspections' => 'tz_inspections',
        'findings' => 'tz_assurance_findings',
        'risks' => 'tz_risk_register',
        'incidents' => 'tz_incidents',
        'agreements' => 'tz_premises_agreements',
        'occupancies' => 'tz_premises_occupancies',
        'reservations' => 'tz_accommodation_reservations',
        'stays' => 'tz_accommodation_stays',
        'housekeeping' => 'tz_accommodation_housekeeping_tasks',
        'invoices' => 'tz_finance_invoices',
        'payment_observations' => 'tz_payment_observations',
        'trust_matters' => 'tz_trust_matters',
    ];

    public function __invoke(ActiveCompanyContext $context): JsonResponse
    {
        $counts = [];
        foreach (self::TABLES as $key => $table) {
            $counts[$key] = Schema::hasTable($table)
                ? DB::table($table)->where('company_id', $context->companyId)->count()
                : null;
        }

        $workOrdersByStatus = [];
        if (Schema::hasTable('tz_work_orders') && Schema::hasColumn('tz_work_orders', 'status')) {
            $workOrdersByStatus = DB::table('tz_work_orders')
                ->selectRaw('status, COUNT(*) AS aggregate')
                ->where('company_id', $context->companyId)
                ->groupBy('status')
                ->pluck('aggregate', 'status')
                ->map(static fn ($value): int => (int) $value)
                ->all();
        }

        $assurance = [
            'pending_inspections' => $this->countWhere(
                'tz_inspections',
                $context->companyId,
                static fn ($query) => $query->whereIn('status', ['scheduled', 'in_progress'])->whereNull('deleted_at'),
            ),
            'open_findings' => $this->countWhere(
                'tz_assurance_findings',
                $context->companyId,
                static fn ($query) => $query->whereNotIn('status', ['resolved', 'closed', 'cancelled'])->whereNull('deleted_at'),
            ),
            'overdue_corrective_actions' => $this->countWhere(
                'tz_corrective_actions',
                $context->companyId,
                static fn ($query) => $query->whereNotNull('due_at')->where('due_at', '<', now())
                    ->whereNotIn('status', ['completed', 'verified', 'closed', 'cancelled'])->whereNull('deleted_at'),
            ),
            'high_critical_risks' => $this->countWhere(
                'tz_risk_register',
                $context->companyId,
                static fn ($query) => $query->whereIn('risk_level', ['high', 'critical'])->where('status', 'open')->whereNull('deleted_at'),
            ),
            'open_incidents' => $this->countWhere(
                'tz_incidents',
                $context->companyId,
                static fn ($query) => $query->whereNotIn('status', ['closed', 'cancelled'])->whereNull('deleted_at'),
            ),
        ];

        $propertyAccommodation = [
            'arrivals_today' => $this->countWhere(
                'tz_accommodation_reservations',
                $context->companyId,
                static fn ($query) => $query->whereDate('arrival_at', now()->toDateString())
                    ->whereIn('status', ['confirmed', 'checked_in'])->whereNull('deleted_at'),
            ),
            'departures_today' => $this->countWhere(
                'tz_accommodation_reservations',
                $context->companyId,
                static fn ($query) => $query->whereDate('departure_at', now()->toDateString())
                    ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])->whereNull('deleted_at'),
            ),
            'in_house' => $this->countWhere(
                'tz_accommodation_stays',
                $context->companyId,
                static fn ($query) => $query->where('status', 'in_house')->whereNull('deleted_at'),
            ),
            'dirty_spaces' => $this->countWhere(
                'tz_accommodation_housekeeping_tasks',
                $context->companyId,
                static fn ($query) => $query->whereIn('status', ['dirty', 'assigned', 'in_progress', 'inspected'])
                    ->whereNull('deleted_at'),
            ),
            'active_agreements' => $this->countWhere(
                'tz_premises_agreements',
                $context->companyId,
                static fn ($query) => $query->whereIn('status', ['active', 'suspended'])->whereNull('deleted_at'),
            ),
            'active_occupancies' => $this->countWhere(
                'tz_premises_occupancies',
                $context->companyId,
                static fn ($query) => $query->whereIn('status', ['reserved', 'active', 'suspended'])->whereNull('deleted_at'),
            ),
        ];


        $finance = [
            'open_receivables_minor' => $this->sumWhere(
                'tz_finance_invoices', $context->companyId, 'balance_minor',
                static fn ($query) => $query->whereIn('status', ['issued', 'viewed', 'overdue', 'partially_paid'])->whereNull('deleted_at'),
            ),
            'overdue_invoices' => $this->countWhere(
                'tz_finance_invoices', $context->companyId,
                static fn ($query) => $query->where('status', 'overdue')->whereNull('deleted_at'),
            ),
            'draft_expenses_minor' => $this->sumWhere(
                'tz_finance_expenses', $context->companyId, 'total_minor',
                static fn ($query) => $query->where('status', 'draft')->whereNull('deleted_at'),
            ),
            'currency' => 'AUD',
        ];

        $payments = [
            'pending_sessions' => $this->countWhere(
                'tz_payment_sessions', $context->companyId,
                static fn ($query) => $query->whereIn('status', ['pending', 'processing']),
            ),
            'unmatched_observations' => $this->countWhere(
                'tz_payment_observations', $context->companyId,
                static fn ($query) => $query->where('status', 'unmatched'),
            ),
            'proposed_matches' => $this->countWhere(
                'tz_payment_matches', $context->companyId,
                static fn ($query) => $query->where('status', 'proposed'),
            ),
        ];

        $trustAccounting = [
            'ledger_balance_minor' => $this->sumWhere(
                'tz_trust_ledger_entries', $context->companyId, 'amount_minor', static fn ($query) => $query,
            ),
            'open_matters' => $this->countWhere(
                'tz_trust_matters', $context->companyId,
                static fn ($query) => $query->where('status', 'open'),
            ),
            'pending_disbursements' => $this->countWhere(
                'tz_trust_disbursements', $context->companyId,
                static fn ($query) => $query->whereIn('status', ['pending_approval', 'approved']),
            ),
            'reconciliation_exceptions_minor' => $this->sumWhere(
                'tz_trust_reconciliations', $context->companyId, 'difference_minor',
                static fn ($query) => $query->where('status', '!=', 'completed'),
            ),
            'currency' => 'AUD',
        ];


        $intelligenceRuntime = [
            'active_agents' => $this->countWhere(
                'titan_agents', $context->companyId,
                static fn ($query) => $query->whereIn('status', ['draft', 'active', 'paused']),
            ),
            'active_connections' => $this->countWhere(
                'titan_connections', $context->companyId,
                static fn ($query) => $query->whereIn('status', ['pending', 'active', 'degraded']),
            ),
            'active_memories' => $this->countWhere(
                'titan_memories', $context->companyId,
                static fn ($query) => $query->where('status', 'active')
                    ->where(static fn ($scope) => $scope->whereNull('expires_at')->orWhere('expires_at', '>', now())),
            ),
            'published_skills' => $this->countWhere(
                'titan_skills', $context->companyId,
                static fn ($query) => $query->whereIn('status', ['published', 'active']),
            ),
            'active_voice_sessions' => $this->countWhere(
                'titan_voice_sessions', $context->companyId,
                static fn ($query) => $query->whereIn('status', ['created', 'connecting', 'active']),
            ),
            'pending_onboarding' => $this->countWhere(
                'titan_onboarding_progress', $context->companyId,
                static fn ($query) => $query->where('status', '!=', 'completed'),
            ),
        ];

        $creativeMarketing = [
            'active_campaigns' => $this->countWhere(
                'titan_campaigns', $context->companyId,
                static fn ($query) => $query->whereIn('status', ['draft', 'planned', 'active', 'paused']),
            ),
            'open_projects' => $this->countWhere(
                'titan_creative_projects', $context->companyId,
                static fn ($query) => $query->whereIn('status', ['draft', 'active', 'review']),
            ),
            'generation_queue' => $this->countWhere(
                'titan_generation_jobs', $context->companyId,
                static fn ($query) => $query->whereIn('status', ['queued', 'processing']),
            ),
            'pending_approvals' => $this->countWhere(
                'titan_content_items', $context->companyId,
                static fn ($query) => $query->where('approval_required', true)->where('approval_status', 'pending'),
            ),
            'pending_publications' => $this->countWhere(
                'titan_publications', $context->companyId,
                static fn ($query) => $query->whereIn('status', ['draft', 'scheduled', 'publishing']),
            ),
            'active_automations' => $this->countWhere(
                'titan_marketing_automations', $context->companyId,
                static fn ($query) => $query->whereIn('status', ['active', 'paused']),
            ),
        ];

        return response()->json([
            'data' => [
                'company_id' => $context->companyId,
                'counts' => $counts,
                'work_orders_by_status' => $workOrdersByStatus,
                'assurance' => $assurance,
                'property_accommodation' => $propertyAccommodation,
                'finance' => $finance,
                'payments' => $payments,
                'trust_accounting' => $trustAccounting,
                'intelligence_runtime' => $intelligenceRuntime,
                'creative_marketing' => $creativeMarketing,
            ],
        ]);
    }


    private function sumWhere(string $table, int $companyId, string $column, callable $scope): ?int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return null;
        }

        $query = DB::table($table)->where('company_id', $companyId);
        $scope($query);

        return (int) $query->sum($column);
    }

    private function countWhere(string $table, int $companyId, callable $scope): ?int
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        $query = DB::table($table)->where('company_id', $companyId);
        $scope($query);

        return $query->count();
    }
}
