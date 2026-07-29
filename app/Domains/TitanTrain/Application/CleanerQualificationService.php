<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Application;

use App\Domains\TitanTrain\Domain\Blueprints\CleanerFoundationBlueprint;
use App\Domains\TitanTrain\Infrastructure\Models\Assignment;
use App\Domains\TitanTrain\Infrastructure\Models\Certificate;
use App\Domains\TitanTrain\Infrastructure\Models\Competency;
use App\Domains\TitanTrain\Infrastructure\Models\CompetencyGrant;
use App\Models\User;
use App\Domains\TitanTrain\Domain\Exceptions\TitanTrainRuleViolation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CleanerQualificationService
{
    public function __construct(
        private CleanerFoundationBlueprint $blueprint,
        private CleanerFoundationInstaller $installer,
        private CleanerReadinessService $readiness,
        private TitanTrainEventPublisher $events,
    ) {}

    /** @return array<string,mixed> */
    public function finalise(Assignment $assignment, User $trainer): array
    {
        if ($assignment->lessons_completed_at === null || $assignment->knowledge_passed_at === null || $assignment->practical_passed_at === null) {
            throw new TitanTrainRuleViolation('Lessons, knowledge assessment and practical observation must all be complete.');
        }
        $program = $this->installer->find() ?? throw new TitanTrainRuleViolation('Cleaner foundation is not installed.');
        return DB::transaction(function () use ($assignment, $trainer, $program): array {
            $assignment = Assignment::query()->whereKey($assignment->id)->lockForUpdate()->firstOrFail();
            if ($assignment->completed_at !== null) {
                return $this->readiness->forUser($assignment->user);
            }
            $certificateExpiry = null;
            foreach ($this->blueprint->competencies() as $definition) {
                $competency = Competency::query()->where('code', $definition['code'])->firstOrFail();
                $expiresAt = $definition['validity_days'] ? now()->addDays((int) $definition['validity_days']) : null;
                if ($expiresAt !== null && ($certificateExpiry === null || $expiresAt->lt($certificateExpiry))) {
                    $certificateExpiry = $expiresAt;
                }
                CompetencyGrant::query()->firstOrCreate(
                    [
                        'company_id' => $assignment->company_id,
                        'competency_id' => $competency->id,
                        'user_id' => $assignment->user_id,
                        'assignment_id' => $assignment->id,
                    ],
                    [
                        'assignment_id' => $assignment->id, 'granted_by_user_id' => $trainer->getKey(),
                        'granted_at' => now(), 'expires_at' => $expiresAt,
                        'metadata' => ['blueprint' => $this->blueprint->sourceId()],
                    ],
                );
            }
            $certificate = Certificate::query()
                ->where('user_id', $assignment->user_id)
                ->where('program_id', $program->id)
                ->where('assignment_id', $assignment->id)
                ->where('status', 'active')
                ->first();
            if ($certificate === null) {
                $certificate = Certificate::query()->create([
                    'company_id' => $assignment->company_id, 'user_id' => $assignment->user_id,
                    'program_id' => $program->id, 'assignment_id' => $assignment->id,
                    'issued_by_user_id' => $trainer->getKey(),
                    'certificate_number' => 'TT-'.now()->format('Y').'-'.str_pad((string) $assignment->id, 8, '0', STR_PAD_LEFT),
                    'verification_code' => Str::random(48), 'status' => 'active',
                    'issued_at' => now(), 'expires_at' => $certificateExpiry,
                    'metadata' => $this->blueprint->certificateTemplate(),
                ]);
            }
            $assignment->forceFill(['status' => 'completed', 'completed_at' => now(), 'progress_percent' => 100])->save();
            $this->events->publish('titan_train.cleaner.qualified', 'titan_train.assignment', $assignment->public_id, (int) $trainer->getKey(), [
                'user_id' => $assignment->user_id, 'certificate_id' => $certificate->public_id,
            ]);
            return $this->readiness->forUser($assignment->user);
        });
    }
}
