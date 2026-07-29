<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Application;

use App\Domains\TitanTrain\Domain\Blueprints\CleanerFoundationBlueprint;
use App\Domains\TitanTrain\Infrastructure\Models\Assignment;
use App\Domains\TitanTrain\Infrastructure\Models\ChannelLink;
use App\Domains\WorkCore\System\Contracts\TenantContextContract;
use App\Domains\WorkCore\System\Models\CompanyMember;
use App\Models\User;
use App\Domains\TitanTrain\Domain\Exceptions\TitanTrainRuleViolation;
use Illuminate\Support\Facades\DB;

final class CleanerOnboardingService
{
    public function __construct(
        private CleanerFoundationInstaller $installer,
        private CleanerFoundationBlueprint $blueprint,
        private TenantContextContract $tenant,
        private TitanTrainEventPublisher $events,
    ) {}

    public function assign(User $cleaner, User $actor, ?int $branchId = null, ?\DateTimeInterface $dueAt = null): Assignment
    {
        $membership = CompanyMember::withoutGlobalScopes()
            ->where('company_id', $this->tenant->companyId())
            ->where('user_id', $cleaner->getKey())
            ->where('status', 'active')
            ->first();
        if ($membership === null) {
            throw new TitanTrainRuleViolation('The selected cleaner is not an active member of this company.');
        }

        if ($branchId !== null) {
            $branchExists = DB::table('tz_branches')
                ->where('id', $branchId)
                ->where('company_id', $this->tenant->companyId())
                ->exists();
            if (! $branchExists) {
                throw new TitanTrainRuleViolation('The selected branch does not belong to this company.');
            }
        }

        $program = $this->installer->install($actor);

        return DB::transaction(function () use ($cleaner, $actor, $branchId, $dueAt, $program): Assignment {
            DB::table('tz_company_memberships')
                ->where('company_id', $this->tenant->companyId())
                ->where('user_id', $cleaner->getKey())
                ->lockForUpdate()
                ->first();

            $latest = Assignment::query()
                ->where('user_id', $cleaner->getKey())
                ->where('assignment_key', $this->blueprint->assignmentKey())
                ->lockForUpdate()
                ->latest('cycle_number')
                ->first();
            if ($latest !== null && $latest->status !== 'completed') {
                return $latest->load('program');
            }

            $cycle = $latest ? ((int) $latest->cycle_number + 1) : 1;
            $assignment = Assignment::query()->create([
                'company_id' => $program->company_id,
                'user_id' => $cleaner->getKey(),
                'assignment_key' => $this->blueprint->assignmentKey(),
                'cycle_number' => $cycle,
                'branch_id' => $branchId,
                'program_id' => $program->id,
                'assigned_by_user_id' => $actor->getKey(),
                'status' => 'assigned',
                'progress_percent' => 0,
                'due_at' => $dueAt,
                'metadata' => [
                    'blueprint_key' => $this->blueprint->key(),
                    'blueprint_version' => $this->blueprint->version(),
                    'requires_supervised_practical' => true,
                ],
            ]);

            ChannelLink::query()->updateOrCreate(
                ['company_id' => $program->company_id, 'slug' => 'train-cleaner-'.$cleaner->getKey()],
                [
                    'program_id' => $program->id,
                    'assignment_id' => $assignment->id,
                    'channel_type' => 'private_learner',
                    'status' => 'active',
                    'metadata' => ['user_id' => $cleaner->getKey(), 'pwa_app' => 'titan-train'],
                ],
            );

            $this->events->publish('titan_train.assignment.created', 'titan_train.assignment', $assignment->public_id, (int) $actor->getKey(), [
                'user_id' => $cleaner->getKey(),
                'program_id' => $program->public_id,
                'due_at' => $assignment->due_at?->toISOString(),
            ]);

            return $assignment->load('program');
        });
    }
}
