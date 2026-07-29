<?php

namespace App\Domains\TitanTrain\Domain\Blueprints;

final class CleanerFoundationBlueprint
{
    use CleanerFoundationModules;
    use CleanerFoundationAssessments;
    use CleanerFoundationCompetencies;

    public function key(): string
        {
            return 'cleaner-foundation-au';
        }

    public function version(): string
        {
            return '1.0.0';
        }

    public function sourceId(): string
        {
            return $this->key().':'.$this->version();
        }

    public function assignmentKey(): string
        {
            return 'titan-train:'.$this->sourceId();
        }

    public function program(): array
        {
            return [
                'title' => 'Titan Train — New Cleaner Foundation',
                'slug' => 'titan-train-new-cleaner-foundation',
                'summary' => 'Australian cleaner onboarding covering safety, chemicals, infection control, property access, cleaning methods, quality and evidence.',
                'description' => 'A practical foundation pathway for new residential and general commercial cleaners. Learners complete required lessons, a knowledge assessment and a supervised practical observation before competencies may be granted.',
                'level' => 'beginner',
                'estimated_duration_minutes' => 300,
                'requirements' => [
                    'Active company membership',
                    'Access to company cleaning procedures and safety data sheets',
                    'Supervised practical shift before final competency sign-off',
                ],
                'outcomes' => [
                    'Work safely within company procedures and Australian work health and safety expectations',
                    'Handle cleaning chemicals without mixing incompatible products',
                    'Prevent cross-contamination using colour coding and clean-to-dirty workflows',
                    'Clean bathrooms, kitchens, surfaces and floors using a repeatable sequence',
                    'Protect property access, customer privacy and keys',
                    'Complete quality checks, evidence and incident escalation',
                ],
                'source_type' => 'titan_train_blueprint',
                'source_id' => $this->sourceId(),
            ];
        }
}
