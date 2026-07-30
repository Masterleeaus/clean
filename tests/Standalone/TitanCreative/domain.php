<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$files = [
    'app/Titan/Creative/Domain/CampaignSchedulePolicy.php',
    'app/Titan/Creative/Domain/ContentApprovalPolicy.php',
    'app/Titan/Creative/Domain/GenerationJobState.php',
    'app/Titan/Creative/Domain/PublicationState.php',
    'app/Titan/Creative/Domain/ProviderReferencePolicy.php',
];
foreach ($files as $file) {
    if (! is_file($root.'/'.$file)) throw new RuntimeException("Missing {$file}");
    require_once $root.'/'.$file;
}

use App\Titan\Creative\Domain\CampaignSchedulePolicy;
use App\Titan\Creative\Domain\ContentApprovalPolicy;
use App\Titan\Creative\Domain\GenerationJobState;
use App\Titan\Creative\Domain\PublicationState;
use App\Titan\Creative\Domain\ProviderReferencePolicy;

$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (! $condition) throw new RuntimeException($message);
};

$expect(CampaignSchedulePolicy::valid(null, null), 'Campaign may be open-ended.');
$expect(CampaignSchedulePolicy::valid('2026-07-26', '2026-08-01'), 'Campaign end may follow start.');
$expect(CampaignSchedulePolicy::valid('2026-07-26', '2026-07-26'), 'Campaign may start and end same day.');
$expect(! CampaignSchedulePolicy::valid('2026-08-01', '2026-07-26'), 'Campaign end cannot precede start.');
$expect(! CampaignSchedulePolicy::valid('not-a-date', '2026-07-26'), 'Invalid campaign start is rejected.');

$expect(ContentApprovalPolicy::mayPublish(false, 'draft'), 'Content without required approval may publish from draft.');
$expect(! ContentApprovalPolicy::mayPublish(true, 'draft'), 'Approval-required content cannot publish from draft.');
$expect(ContentApprovalPolicy::mayPublish(true, 'approved'), 'Approved content may publish.');
$expect(! ContentApprovalPolicy::mayPublish(true, 'rejected'), 'Rejected content cannot publish.');
$expect(ContentApprovalPolicy::approvalStatus(false, null) === 'not_required', 'Optional approval has not-required state.');
$expect(ContentApprovalPolicy::approvalStatus(true, null) === 'pending', 'Required approval begins pending.');

$expect(GenerationJobState::canTransition('queued', 'processing'), 'Queued generation may process.');
$expect(GenerationJobState::canTransition('processing', 'succeeded'), 'Processing generation may succeed.');
$expect(GenerationJobState::canTransition('processing', 'failed'), 'Processing generation may fail.');
$expect(GenerationJobState::canTransition('queued', 'cancelled'), 'Queued generation may cancel.');
$expect(! GenerationJobState::canTransition('failed', 'processing'), 'Failed generation cannot restart.');
$expect(GenerationJobState::terminal('succeeded'), 'Succeeded generation is terminal.');
$expect(GenerationJobState::terminal('cancelled'), 'Cancelled generation is terminal.');

$expect(PublicationState::canTransition('draft', 'scheduled'), 'Draft publication may schedule.');
$expect(PublicationState::canTransition('scheduled', 'publishing'), 'Scheduled publication may publish.');
$expect(PublicationState::canTransition('publishing', 'published'), 'Publishing may complete.');
$expect(PublicationState::canTransition('published', 'retracted'), 'Published content may be retracted.');
$expect(! PublicationState::canTransition('published', 'draft'), 'Published content cannot be silently edited back to draft.');
$expect(PublicationState::terminal('failed'), 'Failed publication is terminal.');

$expect(! ProviderReferencePolicy::requiresReference('manual'), 'Manual work requires no provider reference.');
$expect(ProviderReferencePolicy::requiresReference('ai_image'), 'AI image generation requires provider reference.');
$expect(ProviderReferencePolicy::requiresReference('video_dubbing'), 'Dubbing requires provider reference.');
$expect(ProviderReferencePolicy::valid('manual', null), 'Manual work accepts null provider reference.');
$expect(! ProviderReferencePolicy::valid('ai_video', null), 'Provider-backed job rejects missing reference.');
$expect(ProviderReferencePolicy::valid('ai_video', 'provider_abc123'), 'Provider-backed job accepts configured reference.');

fwrite(STDOUT, "Titan Creative domain: {$checks} checks passed.\n");
