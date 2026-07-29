<?php

declare(strict_types=1);

namespace App\Domains\TitanZero\WorkCore;

use App\Domains\Company\Models\Company;
use App\Domains\TitanZero\WorkCore\Contracts\WorkCoreJobGateway;
use App\Domains\TitanZero\WorkCore\DTO\CompletedBillableJob;
use App\Domains\TitanZero\WorkCore\DTO\WorkCoreJobCandidate;
use App\Domains\TitanZero\WorkCore\DTO\WorkCoreJobResolution;
use App\Models\User;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use LogicException;

final class NativeWorkCoreJobGateway implements WorkCoreJobGateway
{
    private const RUNTIME_ROOTS = [
        'App\\Domains\\WorkCore\\System',
        'App\\Extensions\\WorkCore\\System',
    ];

    public function __construct(private readonly ConnectionInterface $db) {}

    public function resolve(Company $company, string $query): WorkCoreJobResolution
    {
        $workCoreCompanyId = $this->workCoreCompanyId($company);
        if ($workCoreCompanyId === null) {
            return WorkCoreJobResolution::unavailable('This company has not been linked to a WorkCore company yet.');
        }
        if (! $this->tablesAvailable()) {
            return WorkCoreJobResolution::unavailable('WorkCore job tables are not installed in this application.');
        }

        $query = trim($query);
        if ($query === '') {
            return WorkCoreJobResolution::notFound();
        }

        $builder = $this->summaryQuery($workCoreCompanyId);
        $normalised = trim($query, " #\t\n\r\0\x0B");
        $builder->where(function ($where) use ($normalised): void {
            $where->where('work_orders.public_id', $normalised)
                ->orWhereRaw('LOWER(work_orders.number) = ?', [mb_strtolower($normalised)])
                ->orWhereRaw('LOWER(work_orders.title) LIKE ?', ['%'.mb_strtolower($normalised).'%'])
                ->orWhereRaw('LOWER(customers.name) LIKE ?', ['%'.mb_strtolower($normalised).'%'])
                ->orWhereRaw('LOWER(COALESCE(premises.name, \'\')) LIKE ?', ['%'.mb_strtolower($normalised).'%']);
        });

        $rows = $builder->orderByRaw("CASE WHEN work_orders.status = 'completed' THEN 1 ELSE 0 END")
            ->orderByDesc('work_orders.updated_at')
            ->limit(5)
            ->get();

        $candidates = $rows->map(fn (object $row): WorkCoreJobCandidate => $this->candidate($row))->all();
        if ($candidates === []) {
            return WorkCoreJobResolution::notFound();
        }
        if (count($candidates) > 1) {
            return WorkCoreJobResolution::ambiguous($candidates);
        }

        return WorkCoreJobResolution::found($candidates[0]);
    }

    public function complete(Company $company, User $actor, string $publicId, string $correlationId): CompletedBillableJob
    {
        $workCoreCompanyId = $this->workCoreCompanyId($company)
            ?? throw new LogicException('The Titan company is not mapped to WorkCore.');
        if (! $this->tablesAvailable()) {
            throw new LogicException('WorkCore is not installed.');
        }

        $before = $this->loadJob($workCoreCompanyId, $publicId)
            ?? throw new LogicException('The selected WorkCore job no longer exists.');

        if (($before['status'] ?? null) !== 'completed') {
            $runtime = $this->runtime();
            if ($runtime === null) {
                throw new LogicException('The governed WorkCore business-action runtime is not installed.');
            }

            $actorId = (int) $actor->getKey();
            if ($actorId < 1) {
                throw new LogicException('A valid WorkCore actor is required.');
            }

            $tenant = app($runtime['tenant_context']);
            $operation = app($runtime['operation_context']);
            $tenant->set($workCoreCompanyId, $actorId);
            $operation->set($workCoreCompanyId, $actorId, $correlationId, null, 'en-AU', (string) ($company->timezone ?: 'Australia/Sydney'));

            $requestClass = $runtime['action_request'];
            $request = new $requestClass(
                key: 'workcore.work_order.change_status',
                payload: [
                    'work_order_public_id' => $publicId,
                    'status' => 'completed',
                    'reason' => 'Completed through Titan Zero chat.',
                ],
                companyId: $workCoreCompanyId,
                actorId: $actorId,
                idempotencyKey: 'chat-complete-work-order:'.$workCoreCompanyId.':'.$publicId.':'.hash('sha256', (string) ($before['updated_at'] ?? '1')),
                confirmationId: 'titan-zero-chat:'.$correlationId,
                source: 'titan-zero-chat',
            );
            app($runtime['dispatcher'])->dispatch($request);
        }

        $job = $this->loadJob($workCoreCompanyId, $publicId)
            ?? throw new LogicException('The completed WorkCore job could not be reloaded.');

        return new CompletedBillableJob(
            publicId: (string) $job['public_id'],
            number: (string) $job['number'],
            title: (string) $job['title'],
            status: (string) $job['status'],
            sourceVersion: hash('sha256', implode('|', [
                (string) ($job['updated_at'] ?? ''),
                (string) ($job['completed_at'] ?? ''),
                json_encode($job['services'], JSON_THROW_ON_ERROR),
            ])),
            customer: $job['customer'],
            premises: $job['premises'],
            services: $job['services'],
            completedAt: isset($job['completed_at']) ? (string) $job['completed_at'] : null,
        );
    }

    /** @return array{dispatcher:string,action_request:string,tenant_context:string,operation_context:string}|null */
    private function runtime(): ?array
    {
        foreach (self::RUNTIME_ROOTS as $root) {
            $runtime = [
                'dispatcher' => $root.'\\Actions\\BusinessActionDispatcher',
                'action_request' => $root.'\\Actions\\ActionRequest',
                'tenant_context' => $root.'\\Contracts\\TenantContextContract',
                'operation_context' => $root.'\\Contracts\\OperationContextContract',
            ];

            if (class_exists($runtime['dispatcher'])
                && class_exists($runtime['action_request'])
                && interface_exists($runtime['tenant_context'])
                && interface_exists($runtime['operation_context'])) {
                return $runtime;
            }
        }

        return null;
    }

    private function workCoreCompanyId(Company $company): ?int
    {
        $value = ($company->settings_json ?? [])['workcore_company_id'] ?? null;

        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    private function tablesAvailable(): bool
    {
        return Schema::hasTable('tz_work_orders')
            && Schema::hasTable('tz_work_order_services')
            && Schema::hasTable('tz_customers');
    }

    private function summaryQuery(int $companyId): \Illuminate\Database\Query\Builder
    {
        $builder = $this->db->table('tz_work_orders as work_orders')
            ->join('tz_customers as customers', function ($join): void {
                $join->on('customers.id', '=', 'work_orders.customer_id')
                    ->on('customers.company_id', '=', 'work_orders.company_id');
            })
            ->where('work_orders.company_id', $companyId)
            ->whereNull('work_orders.deleted_at')
            ->whereNull('customers.deleted_at')
            ->select([
                'work_orders.id as internal_id', 'work_orders.public_id', 'work_orders.number',
                'work_orders.title', 'work_orders.status', 'work_orders.completed_at',
                'work_orders.updated_at', 'customers.public_id as customer_public_id',
                'customers.name as customer_name', 'customers.email as customer_email',
                'customers.phone as customer_phone', 'customers.address as customer_address',
                'customers.portal_user_id as customer_portal_user_id',
            ]);

        if (Schema::hasTable('tz_premises')) {
            $builder->leftJoin('tz_premises as premises', function ($join): void {
                $join->on('premises.id', '=', 'work_orders.premises_id')
                    ->on('premises.company_id', '=', 'work_orders.company_id');
            })->addSelect([
                'premises.public_id as premises_public_id', 'premises.name as premises_name',
                'premises.address_line_1', 'premises.address_line_2', 'premises.suburb',
                'premises.state', 'premises.postcode', 'premises.country_code',
            ]);
        } else {
            $builder->selectRaw(implode(', ', [
                'NULL as premises_public_id', 'NULL as premises_name',
                'NULL as address_line_1', 'NULL as address_line_2', 'NULL as suburb',
                'NULL as state', 'NULL as postcode', 'NULL as country_code',
            ]));
        }

        return $builder;
    }

    /** @return array<string,mixed>|null */
    private function loadJob(int $companyId, string $publicId): ?array
    {
        $row = $this->summaryQuery($companyId)->where('work_orders.public_id', $publicId)->first();
        if (! $row) {
            return null;
        }

        $services = $this->db->table('tz_work_order_services')
            ->where('company_id', $companyId)
            ->where('work_order_id', (int) $row->internal_id)
            ->orderBy('sort_order')
            ->get(['public_id','service_code','service_name','description','quantity','unit_price','sort_order','status'])
            ->map(static fn (object $item): array => (array) $item)->all();

        return [
            'public_id' => $row->public_id,
            'number' => $row->number,
            'title' => $row->title,
            'status' => $row->status,
            'completed_at' => $row->completed_at,
            'updated_at' => $row->updated_at,
            'customer' => [
                'public_id' => $row->customer_public_id,
                'name' => $row->customer_name,
                'email' => $row->customer_email,
                'phone' => $row->customer_phone,
                'address' => $row->customer_address,
                'portal_user_id' => $row->customer_portal_user_id,
            ],
            'premises' => $row->premises_public_id ? [
                'public_id' => $row->premises_public_id,
                'name' => $row->premises_name,
                'address_line_1' => $row->address_line_1,
                'address_line_2' => $row->address_line_2,
                'suburb' => $row->suburb,
                'state' => $row->state,
                'postcode' => $row->postcode,
                'country_code' => $row->country_code,
            ] : null,
            'services' => $services,
        ];
    }

    private function candidate(object $row): WorkCoreJobCandidate
    {
        return new WorkCoreJobCandidate(
            publicId: (string) $row->public_id,
            number: (string) $row->number,
            title: (string) $row->title,
            status: (string) $row->status,
            customerName: (string) $row->customer_name,
            premisesName: $row->premises_name !== null ? (string) $row->premises_name : null,
        );
    }
}
