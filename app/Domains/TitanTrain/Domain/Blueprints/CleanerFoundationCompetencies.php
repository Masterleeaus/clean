<?php

namespace App\Domains\TitanTrain\Domain\Blueprints;

trait CleanerFoundationCompetencies
{
    public function competencies(): array
        {
            return [
                ['name' => 'Cleaner Foundation', 'code' => 'CLEANER-FOUNDATION', 'description' => 'Completed the required cleaner foundation learning and supervised assessment.', 'validity_days' => 365, 'assessment_kind' => 'practical'],
                ['name' => 'Cleaning Chemical Safety', 'code' => 'CHEMICAL-SAFETY', 'description' => 'Can select, dilute, label and use approved cleaning chemicals without unsafe mixing.', 'validity_days' => 365, 'assessment_kind' => 'knowledge'],
                ['name' => 'Cleaning Infection Control', 'code' => 'INFECTION-CONTROL', 'description' => 'Can apply hand hygiene, colour coding and cross-contamination controls.', 'validity_days' => 365, 'assessment_kind' => 'practical'],
                ['name' => 'Customer Property Access', 'code' => 'PROPERTY-ACCESS', 'description' => 'Can protect keys, access codes, privacy and site security.', 'validity_days' => 365, 'assessment_kind' => 'knowledge'],
                ['name' => 'Cleaning Quality and Close-out', 'code' => 'CLEANING-QUALITY', 'description' => 'Can perform a complete clean, quality inspection, evidence capture and governed handover.', 'validity_days' => 365, 'assessment_kind' => 'practical'],
            ];
        }

    public function certificateTemplate(): array
        {
            return [
                'name' => 'Titan Train Cleaner Competency Certificate',
                'title_template' => '{{competency}}',
                'body_template' => '{{user}} has completed the required Titan Train cleaner learning and competency process for {{company}}.',
                'footer_template' => 'Issued {{issued_at}}. Verify using {{verification_code}}.',
            ];
        }
}
