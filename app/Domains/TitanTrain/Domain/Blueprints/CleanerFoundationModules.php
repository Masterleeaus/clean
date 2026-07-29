<?php

namespace App\Domains\TitanTrain\Domain\Blueprints;

trait CleanerFoundationModules
{
    public function modules(): array
        {
            return [
                [
                    'title' => '1. Cleaner Role and Professional Standards',
                    'description' => 'Understand the cleaner role, expected conduct, communication and scope.',
                    'position' => 1,
                    'lessons' => [
                        $this->lesson('Your Role, Scope and Daily Responsibilities', 1, 15, 'Follow the work order, company procedures and site instructions. Confirm scope before beginning. Do not perform unapproved work or make promises about price, timing or repairs.'),
                        $this->lesson('Presentation, Punctuality and Customer Communication', 2, 15, 'Arrive ready, wear approved clothing and identification, communicate respectfully and report delays through the approved channel. Never argue with an occupant or disclose another customer’s information.'),
                    ],
                ],
                [
                    'title' => '2. Property Access, Privacy and Security',
                    'description' => 'Protect keys, codes, privacy and the customer’s property.',
                    'position' => 2,
                    'lessons' => [
                        $this->lesson('Keys, Codes and Authorised Entry', 1, 15, 'Use only assigned access credentials. Never copy, photograph, label with an address, share or retain a key or code outside the authorised workflow.'),
                        $this->lesson('Privacy, Personal Property and Restricted Areas', 2, 15, 'Access only areas required for the job. Do not open drawers, photograph personal items or discuss what you see. Escalate unexpected conditions and restricted areas.'),
                        $this->lesson('Secure Exit and Lost-Key Escalation', 3, 10, 'Before leaving, confirm doors, windows, alarms, keys, equipment and waste. Report a missing key, access failure or security concern immediately and do not conceal it.'),
                    ],
                ],
                [
                    'title' => '3. Work Health and Safety',
                    'description' => 'Recognise hazards, use controls and report incidents.',
                    'position' => 3,
                    'lessons' => [
                        $this->lesson('Pre-start Risk Check and Stop-work Authority', 1, 20, 'Check access, lighting, electricity, trip hazards, unstable items, pets, people, sharps, biological contamination and ventilation. Stop and escalate when controls are not adequate.'),
                        $this->lesson('PPE, Manual Handling and Slips', 2, 20, 'Use task-appropriate gloves, footwear, eye protection and other required PPE. Keep loads close, use aids, avoid twisting and place wet-floor controls before creating a slip risk.'),
                        $this->lesson('Incidents, Injuries, Sharps and Hazardous Discoveries', 3, 15, 'Do not handle unknown sharps or hazardous materials without the required procedure. Make the area safe, seek first aid when needed and record the incident promptly.'),
                    ],
                ],
                [
                    'title' => '4. Chemical Safety and Product Control',
                    'description' => 'Select, dilute, label and use products safely.',
                    'position' => 4,
                    'lessons' => [
                        $this->lesson('Safety Data Sheets, Labels and Approved Products', 1, 20, 'Use only company-approved products in labelled containers. Read the label and safety data sheet before first use and whenever the task or product changes.'),
                        $this->lesson('Never Mix Cleaning Chemicals', 2, 20, 'Never mix cleaning chemicals unless an approved product system explicitly requires it. In particular, never mix bleach with acids, ammonia or other cleaners. Stop work, ventilate and escalate after an accidental mixture or unexpected fumes.'),
                        $this->lesson('Dilution, Dwell Time and Ventilation', 3, 20, 'Measure dilution accurately, add product according to label directions, provide ventilation and allow the stated dwell time. More chemical is not automatically more effective and may damage surfaces or harm people.'),
                    ],
                ],
                [
                    'title' => '5. Infection Prevention and Cross-Contamination',
                    'description' => 'Prevent transfer of soil and microorganisms between areas.',
                    'position' => 5,
                    'lessons' => [
                        $this->lesson('Hand Hygiene, Gloves and High-touch Surfaces', 1, 15, 'Perform hand hygiene at appropriate points, change contaminated gloves and prioritise high-touch points such as handles, switches and taps using the approved procedure.'),
                        $this->lesson('Colour Coding and Cross-Contamination', 2, 20, 'Use company colour coding for cloths, mops and buckets. Never use toilet-area equipment in kitchens or general areas. Replace or isolate contaminated tools immediately.'),
                        $this->lesson('Clean-to-dirty, High-to-low and Dry-to-wet', 3, 15, 'Work from cleaner areas to dirtier areas, high surfaces to low surfaces and dry soil removal before wet cleaning where appropriate. This reduces rework and contamination spread.'),
                    ],
                ],
                [
                    'title' => '6. Core Cleaning Method and Surface Care',
                    'description' => 'Use a consistent room sequence and avoid surface damage.',
                    'position' => 6,
                    'lessons' => [
                        $this->lesson('Prepare, Declutter Safely and Plan the Room', 1, 15, 'Review scope, gather equipment, identify fragile or valuable items and plan an exit path. Do not discard unapproved items or move heavy furniture without authorisation.'),
                        $this->lesson('Dusting, Wiping and Surface Compatibility', 2, 20, 'Use clean tools and an appropriate product for the material. Test uncertain surfaces, avoid overspray near electronics and prevent abrasive damage, water ingress and residue.'),
                        $this->lesson('Repeatable Room Sequence', 3, 20, 'Use a consistent clockwise or zone-based sequence so edges, corners, high-touch points and low areas are not missed. Finish each zone before moving on where practical.'),
                    ],
                ],
                [
                    'title' => '7. Bathrooms, Toilets and Kitchens',
                    'description' => 'Clean higher-risk wet areas using dedicated methods.',
                    'position' => 7,
                    'lessons' => [
                        $this->lesson('Bathroom and Toilet Cleaning Sequence', 1, 25, 'Ventilate, apply products safely, allow dwell time, work from cleaner fixtures toward the toilet, use dedicated colour-coded tools and finish with dry, streak-free surfaces and a floor clean.'),
                        $this->lesson('Kitchen Cleaning Sequence', 2, 25, 'Protect food-contact areas, remove dry soil, clean high-touch points and appliances within scope, manage grease with approved products and prevent bathroom equipment entering the kitchen.'),
                        $this->lesson('Mould, Bodily Fluids, Pests and Out-of-scope Hazards', 3, 15, 'Do not treat significant mould, bodily fluids, pest contamination or unknown residues as routine cleaning unless trained, equipped and authorised. Isolate and escalate.'),
                    ],
                ],
                [
                    'title' => '8. Floors, Equipment and Waste',
                    'description' => 'Use equipment safely and leave it ready for the next job.',
                    'position' => 8,
                    'lessons' => [
                        $this->lesson('Vacuuming, Mopping and Floor Protection', 1, 20, 'Select the correct floor method, inspect cords and machines, work edges and traffic lanes, control wet-floor risks and avoid excess water on moisture-sensitive flooring.'),
                        $this->lesson('Equipment Inspection, Cleaning and Fault Reporting', 2, 15, 'Inspect equipment before use, isolate damaged equipment, clean tools after use, manage batteries and cords safely and report faults rather than improvising unsafe repairs.'),
                        $this->lesson('Waste, Laundry and Contaminated Consumables', 3, 15, 'Separate waste streams where required, avoid compressing bags by hand, secure contaminated items and transport cloths and mop heads in a way that prevents leakage and cross-contamination.'),
                    ],
                ],
                [
                    'title' => '9. Quality, Evidence and Job Close-out',
                    'description' => 'Prove the work, correct defects and close the property securely.',
                    'position' => 9,
                    'lessons' => [
                        $this->lesson('Final Quality Check and Evidence', 1, 20, 'Inspect the work from the customer’s viewpoint. Check scope, edges, corners, high-touch points, floors, odour, residue and damage. Capture only authorised evidence and protect personal information.'),
                        $this->lesson('Defects, Damage and Customer Concerns', 2, 15, 'Correct safe in-scope defects before leaving. Photograph and report pre-existing or accidental damage honestly. Do not hide mistakes or make unauthorised compensation promises.'),
                        $this->lesson('Complete the Work Order and Handover', 3, 15, 'Record completion, exceptions, used products, evidence and follow-up needs. Return keys, secure the site and notify the correct person through the governed workflow.'),
                    ],
                ],
            ];
        }

    private function lesson(string $title, int $position, int $minutes, string $body): array
        {
            return [
                'title' => $title,
                'content_type' => 'text',
                'body' => $body,
                'duration_minutes' => $minutes,
                'position' => $position,
                'is_required' => true,
                'is_skippable' => false,
            ];
        }
}
