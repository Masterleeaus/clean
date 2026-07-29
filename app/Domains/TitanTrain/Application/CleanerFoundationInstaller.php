<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Application;

use App\Domains\TitanTrain\Domain\Blueprints\CleanerFoundationBlueprint;
use App\Domains\TitanTrain\Infrastructure\Models\Assessment;
use App\Domains\TitanTrain\Infrastructure\Models\ChannelLink;
use App\Domains\TitanTrain\Infrastructure\Models\Competency;
use App\Domains\TitanTrain\Infrastructure\Models\Lesson;
use App\Domains\TitanTrain\Infrastructure\Models\Module;
use App\Domains\TitanTrain\Infrastructure\Models\Program;
use App\Domains\WorkCore\System\Contracts\TenantContextContract;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CleanerFoundationInstaller
{
    public function __construct(
        private CleanerFoundationBlueprint $blueprint,
        private TitanTrainEventPublisher $events,
        private TenantContextContract $tenant,
    ) {}

    public function install(User $actor): Program
    {
        $existing = Program::query()->where('source_type', 'titan_train_blueprint')->where('source_id', $this->blueprint->sourceId())->first();
        if ($existing !== null) {
            return $existing->load(['modules.lessons', 'assessments.questions']);
        }

        return DB::transaction(function () use ($actor): Program {
            DB::table('tz_companies')->where('id', $this->tenant->companyId())->lockForUpdate()->first();
            $existing = Program::query()
                ->where('source_type', 'titan_train_blueprint')
                ->where('source_id', $this->blueprint->sourceId())
                ->first();
            if ($existing !== null) {
                return $existing->load(['modules.lessons', 'assessments.questions']);
            }

            $data = $this->blueprint->program();
            $program = Program::query()->create([
                ...$data,
                'requirements' => $data['requirements'] ?? [],
                'outcomes' => $data['outcomes'] ?? [],
                'status' => 'published',
                'published_at' => now(),
            ]);

            foreach ($this->blueprint->modules() as $moduleData) {
                $lessons = $moduleData['lessons'];
                unset($moduleData['lessons']);
                $module = Module::query()->create([...$moduleData, 'program_id' => $program->id]);
                foreach ($lessons as $lessonData) {
                    Lesson::query()->create([
                        'company_id' => $program->company_id,
                        'module_id' => $module->id,
                        'title' => $lessonData['title'],
                        'content_type' => $lessonData['content_type'] ?? 'text',
                        'body' => $lessonData['body'] ?? null,
                        'duration_minutes' => $lessonData['duration_minutes'] ?? 0,
                        'position' => $lessonData['position'],
                        'is_required' => (bool) ($lessonData['is_required'] ?? true),
                        'is_skippable' => (bool) ($lessonData['is_skippable'] ?? false),
                    ]);
                }
            }

            foreach ($this->blueprint->assessments() as $assessmentData) {
                $questions = $assessmentData['questions'];
                unset($assessmentData['questions']);
                $assessment = Assessment::query()->create([
                    'company_id' => $program->company_id,
                    'program_id' => $program->id,
                    'kind' => $assessmentData['kind'],
                    'title' => $assessmentData['title'],
                    'instructions' => $assessmentData['instructions'] ?? null,
                    'pass_mark' => $assessmentData['pass_mark'],
                    'attempt_limit' => $assessmentData['attempt_limit'],
                    'duration_minutes' => $assessmentData['duration_minutes'],
                    'status' => 'published',
                ]);
                foreach ($questions as $question) {
                    $assessment->questions()->create([
                        'company_id' => $program->company_id,
                        'type' => $question['type'],
                        'prompt' => $question['prompt'],
                        'options' => $question['options'] ?? null,
                        'correct_answer' => $question['correct_answer'] ?? null,
                        'points' => $question['points'] ?? 1,
                        'position' => $question['position'],
                        'requires_review' => in_array($question['type'], ['practical', 'supervisor_signoff', 'text'], true),
                    ]);
                }
            }

            foreach ($this->blueprint->competencies() as $competencyData) {
                Competency::query()->firstOrCreate(
                    ['company_id' => $program->company_id, 'code' => $competencyData['code']],
                    [
                        'name' => $competencyData['name'],
                        'description' => $competencyData['description'],
                        'validity_days' => $competencyData['validity_days'],
                        'assessment_kind' => $competencyData['assessment_kind'],
                        'active' => true,
                    ],
                );
            }

            ChannelLink::query()->firstOrCreate(
                ['company_id' => $program->company_id, 'slug' => 'train-cleaner-foundation'],
                [
                    'program_id' => $program->id,
                    'channel_type' => 'program_cohort',
                    'status' => 'active',
                    'metadata' => ['title' => 'Cleaner Foundation', 'pwa_app' => 'titan-train'],
                ],
            );

            $this->events->publish('titan_train.program.installed', 'titan_train.program', $program->public_id, (int) $actor->getKey(), [
                'source_id' => $this->blueprint->sourceId(),
                'modules' => count($this->blueprint->modules()),
                'lessons' => array_sum(array_map(static fn (array $module): int => count($module['lessons']), $this->blueprint->modules())),
                'assessments' => count($this->blueprint->assessments()),
                'competencies' => count($this->blueprint->competencies()),
            ]);

            return $program->load(['modules.lessons', 'assessments.questions']);
        });
    }

    public function find(): ?Program
    {
        return Program::query()->where('source_type', 'titan_train_blueprint')->where('source_id', $this->blueprint->sourceId())->first();
    }
}
