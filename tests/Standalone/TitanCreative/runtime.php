<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (! $condition) throw new RuntimeException($message);
};

$provider = $root.'/app/Titan/Creative/Providers/TitanCreativeServiceProvider.php';
$expect(is_file($provider), 'Titan Creative service provider must exist.');
$source = is_file($provider) ? (string) file_get_contents($provider) : '';
$capabilities = [
    'titan.brand_kit.create','titan.audience.create','titan.content_template.create','titan.creative.project.create',
    'titan.creative.asset.register','titan.creative.generation.queue','titan.creative.generation.transition',
    'titan.marketing.campaign.create','titan.marketing.content.create','titan.marketing.calendar.schedule',
    'titan.marketing.channel.create','titan.marketing.publication.request','titan.marketing.publication.transition',
    'titan.marketing.automation.create','titan.marketing.seo_brief.create','titan.marketing.newsletter.create',
    'titan.marketing.approval.decide','titan.marketing.analytics.record','titan.creative.summary',
];
foreach ($capabilities as $capability) {
    $expect(str_contains($source, $capability), "Provider registers {$capability}.");
}
$expect(str_contains($source, "'version' => '0.6.0'"), 'Capabilities identify v0.6.0 provider version.');
$expect(str_contains($source, "'kind' => 'first-party'"), 'Capabilities are first-party.');
$expect(str_contains($source, 'CreativeRepository::class'), 'Provider binds creative repository contract.');
$expect(str_contains($source, 'DatabaseCreativeRepository::class'), 'Provider binds database creative repository.');
$expect(! str_contains($source, 'Http::'), 'Provider does not call remote HTTP clients.');

$actions = [
    'CreateBrandKitAction','CreateAudienceAction','CreateTemplateAction','CreateProjectAction','RegisterAssetAction',
    'QueueGenerationAction','TransitionGenerationAction','CreateCampaignAction','CreateContentAction','ScheduleContentAction',
    'CreatePublicationChannelAction','RequestPublicationAction','TransitionPublicationAction','CreateAutomationAction',
    'CreateSeoBriefAction','CreateNewsletterAction','DecideApprovalAction','RecordAnalyticsAction','CreativeSummaryAction',
];
foreach ($actions as $action) {
    $path = $root.'/app/Titan/Creative/Actions/'.$action.'.php';
    $expect(is_file($path), "Action exists: {$action}.");
    $actionSource = is_file($path) ? (string) file_get_contents($path) : '';
    $expect(str_contains($actionSource, 'ActiveCompanyContext'), "{$action} uses server-resolved company context.");
    $expect(str_contains($actionSource, 'CreativeRepository'), "{$action} uses governed repository.");
    $expect(! str_contains($actionSource, "['company_id']"), "{$action} does not trust payload company authority.");
}

$bootstrap = (string) file_get_contents($root.'/bootstrap/providers.php');
$expect(str_contains($bootstrap, 'TitanCreativeServiceProvider::class'), 'Host registers Titan Creative provider.');
$expect(! is_dir($root.'/app/Extensions/Marketing'), 'No donor Marketing directory enters runtime autoload.');
$expect(! is_dir($root.'/app/Extensions/Creative'), 'No donor Creative directory enters runtime autoload.');

fwrite(STDOUT, "Titan Creative runtime: {$checks} checks passed.\n");
