<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Application;

use App\Domains\TitanTrain\Domain\Blueprints\CleanerFoundationBlueprint;
use App\Domains\TitanTrain\Infrastructure\Models\Assignment;
use App\Domains\TitanTrain\Infrastructure\Models\Certificate;
use App\Domains\TitanTrain\Infrastructure\Models\CompetencyGrant;
use App\Domains\TitanTrain\Infrastructure\Models\Lesson;
use App\Models\User;

final class CleanerReadinessService
{
    public function __construct(private CleanerFoundationBlueprint $blueprint, private CleanerFoundationInstaller $installer) {}

    /** @return array<string,mixed> */
    public function forUser(User $user): array
    {
        $program = $this->installer->find();
        if ($program === null) {
            return ['installed' => false, 'assigned' => false, 'ready' => false, 'status' => 'not_installed'];
        }
        $assignment = Assignment::query()->where('program_id', $program->id)->where('user_id', $user->getKey())->where('assignment_key', $this->blueprint->assignmentKey())->first();
        if ($assignment === null) {
            return ['installed' => true, 'assigned' => false, 'ready' => false, 'status' => 'not_assigned', 'program' => $program];
        }
        $required = Lesson::query()->whereHas('module', fn ($query) => $query->where('program_id', $program->id))->where('is_required', true)->count();
        $requiredCodes = array_column($this->blueprint->competencies(), 'code');
        $grants = CompetencyGrant::query()
            ->where('user_id', $user->getKey())
            ->where('status', 'active')
            ->whereHas('competency', fn ($query) => $query->whereIn('code', $requiredCodes))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->distinct('competency_id')
            ->count('competency_id');
        $certificate = Certificate::query()->where('user_id', $user->getKey())->where('program_id', $program->id)->where('status', 'active')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->latest('issued_at')->first();
        $ready = $assignment->completed_at !== null && $grants >= count($this->blueprint->competencies()) && $certificate !== null;
        return [
            'installed' => true, 'assigned' => true, 'ready' => $ready, 'status' => $assignment->status,
            'progress_percent' => (int) $assignment->progress_percent, 'required_lessons' => $required,
            'knowledge_passed' => $assignment->knowledge_passed_at !== null,
            'practical_passed' => $assignment->practical_passed_at !== null,
            'active_competencies' => $grants, 'required_competencies' => count($this->blueprint->competencies()),
            'certificate' => $certificate, 'assignment' => $assignment, 'program' => $program,
        ];
    }
}
