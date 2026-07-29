<?php

declare(strict_types=1);

namespace App\Titan\AI;

use App\Domains\WorkCore\System\Actions\BusinessActionRegistry;
use App\Models\Company;
use App\Models\Conversation;
use App\Titan\Capabilities\CapabilityRegistry;
use App\Titan\Permissions\PermissionResolver;
use App\Titan\Tenancy\ActiveCompanyContext;
use App\Titan\Tenancy\CompanyContextResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ConversationContextBuilder
{
    public function __construct(
        private readonly CompanyContextResolver $contexts,
        private readonly PermissionResolver $permissions,
        private readonly CapabilityRegistry $capabilities,
        private readonly BusinessActionRegistry $actions,
    ) {}

    /** @return array<string,mixed> */
    public function build(?Conversation $conversation = null): array
    {
        $context = $this->context();
        $company = Company::query()->find($context->companyId);

        return [
            'session' => [
                'company_id' => $context->companyId,
                'user_id' => $context->userId,
                'membership_id' => $context->membershipId,
                'role' => $context->roleKey,
                'is_owner' => $context->isOwner,
            ],
            'company' => [
                'name' => (string) ($company?->name ?? ''),
                'timezone' => (string) ($company?->timezone ?? config('app.timezone')),
                'currency' => (string) ($company?->currency ?? 'AUD'),
                'country_code' => (string) ($company?->country_code ?? 'AU'),
            ],
            'conversation' => $this->conversation($conversation, $context),
            'workcore' => ['counts' => $this->workCoreCounts($context->companyId)],
            'intelligence' => $this->intelligenceCounts($context->companyId),
            'creative_marketing' => $this->creativeMarketingCounts($context->companyId),
            'tools' => $this->availableTools($context),
        ];
    }

    /** @return array<string,mixed>|null */
    private function conversation(?Conversation $conversation, ActiveCompanyContext $context): ?array
    {
        if ($conversation === null) {
            return null;
        }
        if ($conversation->company_id !== null && (int) $conversation->company_id !== $context->companyId) {
            throw new \RuntimeException('Conversation does not belong to the active company.', 403);
        }

        $messages = $conversation->messages()
            ->with('user:id,name')
            ->latest('id')
            ->limit(20)
            ->get()
            ->reverse()
            ->map(static function ($message) use ($context): array {
                $role = $message->user_id === null
                    ? 'assistant'
                    : ((int) $message->user_id === $context->userId ? 'user' : 'participant');

                return [
                    'role' => $role,
                    'sender' => $message->user?->name,
                    'content' => self::truncate((string) $message->message, 3000),
                    'created_at' => $message->created_at?->toIso8601String(),
                ];
            })
            ->values()
            ->all();

        return [
            'id' => (int) $conversation->getKey(),
            'type' => (string) $conversation->type,
            'name' => $conversation->name,
            'recent_messages' => $messages,
        ];
    }

    /** @return array<string,int|null> */
    private function workCoreCounts(int $companyId): array
    {
        $tables = [
            'customers' => 'tz_customers',
            'premises' => 'tz_premises',
            'work_orders' => 'tz_work_orders',
            'appointments' => 'tz_appointments',
            'workers' => 'tz_workers',
            'assets' => 'tz_assets',
        ];
        $counts = [];
        foreach ($tables as $key => $table) {
            $counts[$key] = Schema::hasTable($table)
                ? DB::table($table)->where('company_id', $companyId)->count()
                : null;
        }

        return $counts;
    }

    /** @return array<string,int|null> */
    private function intelligenceCounts(int $companyId): array
    {
        $tables = [
            'canvases' => 'titan_workspace_canvases',
            'memories' => 'titan_memories',
            'skills' => 'titan_skills',
            'agents' => 'titan_agents',
            'connections' => 'titan_connections',
            'voice_sessions' => 'titan_voice_sessions',
        ];
        $counts = [];
        foreach ($tables as $key => $table) {
            $counts[$key] = Schema::hasTable($table)
                ? DB::table($table)->where('company_id', $companyId)->count()
                : null;
        }

        return ['counts' => $counts];
    }


    /** @return array<string,int|null> */
    private function creativeMarketingCounts(int $companyId): array
    {
        $tables = [
            'campaigns' => 'titan_campaigns',
            'projects' => 'titan_creative_projects',
            'generation_jobs' => 'titan_generation_jobs',
            'pending_approvals' => 'titan_content_items',
            'publications' => 'titan_publications',
        ];
        $counts = [];
        foreach ($tables as $key => $table) {
            if (! Schema::hasTable($table)) {
                $counts[$key] = null;
                continue;
            }
            $query = DB::table($table)->where('company_id', $companyId);
            if ($key === 'generation_jobs') $query->whereIn('status', ['queued', 'processing']);
            if ($key === 'pending_approvals') $query->where('approval_required', true)->where('approval_status', 'pending');
            if ($key === 'publications') $query->whereIn('status', ['draft', 'scheduled', 'publishing']);
            $counts[$key] = $query->count();
        }

        return ['counts' => $counts];
    }

    /** @return array<int,array<string,mixed>> */
    private function availableTools(ActiveCompanyContext $context): array
    {
        $tools = [];
        foreach ($this->capabilities->all() as $definition) {
            $handler = $definition['handler'] ?? null;
            if (! is_string($handler) || $handler === '') {
                continue;
            }
            $permission = trim((string) ($definition['permission'] ?? ''));
            if ($permission !== '' && ! $this->permissions->allows($context->userId, $context->companyId, $permission)) {
                continue;
            }
            $tools[] = [
                'id' => (string) $definition['id'],
                'description' => (string) ($definition['description'] ?? ''),
                'input_schema' => $definition['input_schema'] ?? ['type' => 'object'],
                'confirmation_required' => false,
            ];
        }

        foreach ($this->actions->all() as $definition) {
            if ($definition->permission !== null && ! $this->permissions->allows($context->userId, $context->companyId, $definition->permission)) {
                continue;
            }
            $tools[] = [
                'id' => $definition->key,
                'description' => (string) ($definition->metadata['description'] ?? $definition->key),
                'input_schema' => $definition->metadata['input_schema'] ?? ['type' => 'object'],
                'confirmation_required' => $definition->requiresConfirmation,
                'risk' => $definition->risk,
            ];
        }

        usort($tools, static fn (array $left, array $right): int => strcmp((string) $left['id'], (string) $right['id']));

        return array_slice($tools, 0, 120);
    }

    private static function truncate(string $value, int $length): string
    {
        return function_exists('mb_substr')
            ? \mb_substr($value, 0, $length)
            : substr($value, 0, $length);
    }

    private function context(): ActiveCompanyContext
    {
        if (app()->bound(ActiveCompanyContext::class)) {
            return app(ActiveCompanyContext::class);
        }

        $context = $this->contexts->resolve(request());
        if (! $context instanceof ActiveCompanyContext) {
            throw new \RuntimeException('Active company context is required.', 409);
        }

        return $context;
    }
}
