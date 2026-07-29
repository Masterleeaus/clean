<?php

namespace App\Domains\TitanTrain\Domain\Blueprints;

trait CleanerFoundationAssessments
{
    public function assessments(): array
        {
            return [
                [
                    'kind' => 'knowledge',
                    'title' => 'New Cleaner Foundation Knowledge Check',
                    'instructions' => 'Choose the safest and most compliant answer. A score of 85% is required.',
                    'pass_mark' => 85,
                    'attempt_limit' => 3,
                    'duration_minutes' => 25,
                    'questions' => $this->knowledgeQuestions(),
                ],
                [
                    'kind' => 'practical',
                    'title' => 'New Cleaner Supervised Practical Observation',
                    'instructions' => 'A trainer or supervisor observes the cleaner completing a controlled cleaning task and grades every criterion.',
                    'pass_mark' => 85,
                    'attempt_limit' => 3,
                    'duration_minutes' => 90,
                    'questions' => $this->practicalQuestions(),
                ],
            ];
        }

    private function knowledgeQuestions(): array
        {
            $position = 0;
    
            return [
                $this->choice(++$position, 'What should a cleaner do before starting work in an unfamiliar property?', ['start' => 'Start immediately to save time', 'risk' => 'Complete a pre-start risk and scope check', 'ask' => 'Ask the customer to supervise every task'], ['risk']),
                $this->choice(++$position, 'Which statement about cleaning chemicals is correct?', ['mix' => 'Mix products when one product is slow', 'approved' => 'Use approved labelled products according to the label and SDS', 'bottle' => 'Unlabelled bottles are acceptable for one shift'], ['approved']),
                $this->choice(++$position, 'Bleach must never be mixed with:', ['water' => 'Water when the label permits dilution', 'acid' => 'Acids, ammonia or other cleaners', 'approved' => 'An approved manufacturer dosing system'], ['acid']),
                $this->choice(++$position, 'Why is colour coding used?', ['speed' => 'To make the trolley look organised', 'contamination' => 'To prevent equipment transferring contamination between areas', 'cost' => 'To reduce the number of cloths used'], ['contamination']),
                $this->choice(++$position, 'What is the preferred general workflow?', ['dirty' => 'Dirty to clean, low to high', 'clean' => 'Clean to dirty, high to low and dry before wet where appropriate', 'random' => 'Any order if every surface looks wet'], ['clean']),
                $this->choice(++$position, 'A cleaner finds an uncapped needle behind a toilet. What should happen?', ['pick' => 'Pick it up by hand while wearing gloves', 'vacuum' => 'Vacuum it up', 'stop' => 'Stop, isolate the area and follow the sharps escalation procedure'], ['stop']),
                $this->choice(++$position, 'A customer access code should be:', ['photo' => 'Photographed on a personal phone', 'shared' => 'Shared with another worker for convenience', 'controlled' => 'Used and recorded only through the approved access workflow'], ['controlled']),
                $this->choice(++$position, 'When should wet-floor controls be placed?', ['after' => 'After mopping is complete', 'before' => 'Before creating the wet-floor risk', 'never' => 'Only in public buildings'], ['before']),
                $this->choice(++$position, 'What should happen when a product causes unexpected fumes?', ['finish' => 'Finish the room quickly', 'ventilate' => 'Stop work, move to safety, ventilate if safe and escalate', 'mask' => 'Add perfume to cover the smell'], ['ventilate']),
                $this->choice(++$position, 'What is correct for bathroom and kitchen tools?', ['same' => 'Use the same cloth if rinsed', 'separate' => 'Keep dedicated colour-coded tools separated', 'kitchen' => 'Use kitchen cloths in the toilet but not the reverse'], ['separate']),
                $this->choice(++$position, 'A cleaner notices pre-existing damage. The correct action is to:', ['hide' => 'Avoid mentioning it', 'repair' => 'Attempt an unapproved repair', 'record' => 'Record and report it before or as soon as work begins'], ['record']),
                $this->choice(++$position, 'More chemical than the label states is:', ['better' => 'Always more effective', 'unsafe' => 'Potentially unsafe and damaging', 'needed' => 'Required on all heavily soiled surfaces'], ['unsafe']),
                $this->choice(++$position, 'What should be included in job close-out?', ['only' => 'Only a message saying done', 'records' => 'Scope completion, exceptions, evidence, products and follow-up needs', 'none' => 'No record if the customer is absent'], ['records']),
                $this->choice(++$position, 'When may a cleaner enter a restricted room?', ['curious' => 'When curious about the contents', 'authorised' => 'Only when authorised and required by the work scope', 'photo' => 'When taking before photos'], ['authorised']),
                $this->choice(++$position, 'What should happen to damaged electrical cleaning equipment?', ['use' => 'Use it carefully until the shift ends', 'repair' => 'Open it and repair it without approval', 'isolate' => 'Stop using it, isolate it and report the fault'], ['isolate']),
            ];
        }

    private function practicalQuestions(): array
        {
            $criteria = [
                'Completes a pre-start risk and scope check and identifies stop-work conditions.',
                'Selects correct PPE, products, labels and dilution controls without mixing incompatible chemicals.',
                'Sets up colour-coded cloths, tools and waste controls to prevent cross-contamination.',
                'Uses a repeatable high-to-low, clean-to-dirty and dry-to-wet cleaning sequence.',
                'Cleans a bathroom and toilet using dedicated tools, correct dwell time and safe floor controls.',
                'Cleans a kitchen area without contaminating food-contact surfaces or using bathroom equipment.',
                'Uses vacuuming or mopping equipment safely and reports a simulated equipment fault correctly.',
                'Protects keys, access information, privacy and personal property throughout the task.',
                'Completes a final quality inspection and corrects safe in-scope defects.',
                'Records authorised evidence, exceptions, incidents and secure job close-out accurately.',
            ];
    
            return array_map(
                fn (string $prompt, int $index): array => [
                    'type' => $index === 9 ? 'supervisor_signoff' : 'practical',
                    'prompt' => $prompt,
                    'points' => 2,
                    'position' => $index + 1,
                    'is_active' => true,
                ],
                $criteria,
                array_keys($criteria),
            );
        }

    private function choice(int $position, string $prompt, array $options, array $correctAnswer): array
        {
            return [
                'type' => 'single_choice',
                'prompt' => $prompt,
                'options' => $options,
                'correct_answer' => $correctAnswer,
                'points' => 1,
                'position' => $position,
                'is_active' => true,
            ];
        }
}
