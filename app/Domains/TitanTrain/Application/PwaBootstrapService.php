<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Application;

use App\Domains\TitanTrain\Infrastructure\Models\Assignment;
use App\Domains\TitanTrain\Infrastructure\Models\ChannelLink;
use App\Models\User;

final class PwaBootstrapService
{
    public function __construct(
        private CleanerReadinessService $readiness,
        private TitanTrainAccess $access,
        private TitanTrainApiPresenter $presenter,
    ) {}

    /** @return array<string,mixed> */
    public function build(User $user): array
    {
        $membership = $this->access->membership($user);
        $assignments = Assignment::query()
            ->where('user_id', $user->getKey())
            ->with(['program.modules.lessons', 'completions', 'program.assessments', 'attempts.assessment'])
            ->orderByDesc('id')
            ->get();
        $manager = $this->access->isManager($user);
        $channels = ChannelLink::query()->where(function ($query) use ($assignments, $manager): void {
            if ($manager) {
                $query->whereIn('channel_type', ['program_cohort', 'private_learner']);

                return;
            }

            $query->whereIn('assignment_id', $assignments->pluck('id'))
                ->orWhereIn('program_id', $assignments->pluck('program_id'));
        })->get();

        $prefix = trim((string) config('titan_train.api_prefix', 'api/v1/titan-train'), '/');

        return [
            'app' => [
                'id' => 'titan-train',
                'name' => 'Titan Train',
                'version' => '0.3.0-pass3',
                'launch_url' => url('/train'),
                'online_only' => true,
                'native_template' => true,
                'views' => ['learn', 'practice', 'skills', 'me'],
                'channel_surface' => 'titan-channels',
            ],
            'company' => [
                'public_id' => $membership->company?->public_id,
                'role' => $membership->role_key,
                'is_owner' => (bool) $membership->is_owner,
            ],
            'permissions' => ['manage' => $manager, 'learn' => true],
            'readiness' => $this->presenter->readiness($this->readiness->forUser($user)),
            'assignments' => $assignments->map(fn (Assignment $assignment): array => $this->presenter->assignment($assignment))->values()->all(),
            'channels' => $channels->map(fn (ChannelLink $link): array => $this->presenter->channel($link))->values()->all(),
            'endpoints' => [
                'bootstrap' => url($prefix.'/pwa/bootstrap'),
                'assignments' => url($prefix.'/assignments'),
                'assignment' => url($prefix.'/assignments/{assignment}'),
                'complete_lesson' => url($prefix.'/assignments/{assignment}/lessons/{lesson}/complete'),
                'start_assessment' => url($prefix.'/assignments/{assignment}/assessments/{assessment}/start'),
                'submit_attempt' => url($prefix.'/attempts/{attempt}/submit'),
                'management_install' => url($prefix.'/management/cleaner-foundation/install'),
            ],
        ];
    }
}
