<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$files = [
    'app/Titan/Intelligence/Domain/MemoryRetentionPolicy.php',
    'app/Titan/Intelligence/Domain/ModelRoutingPolicy.php',
    'app/Titan/Intelligence/Domain/ShareToken.php',
    'app/Titan/Intelligence/Domain/AgentRunState.php',
    'app/Titan/Intelligence/Domain/VoiceSessionState.php',
];
foreach ($files as $file) {
    if (! is_file($root.'/'.$file)) throw new RuntimeException("Missing {$file}");
    require_once $root.'/'.$file;
}

use App\Titan\Intelligence\Domain\AgentRunState;
use App\Titan\Intelligence\Domain\MemoryRetentionPolicy;
use App\Titan\Intelligence\Domain\ModelRoutingPolicy;
use App\Titan\Intelligence\Domain\ShareToken;
use App\Titan\Intelligence\Domain\VoiceSessionState;

$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (! $condition) throw new RuntimeException($message);
};

$expect(! MemoryRetentionPolicy::mayPersist(true, false), 'Temporary chat memory must not persist without promotion.');
$expect(MemoryRetentionPolicy::mayPersist(true, true), 'Promoted temporary chat memory may persist.');
$expect(MemoryRetentionPolicy::mayPersist(false, false), 'Ordinary memory may persist.');
$expires = MemoryRetentionPolicy::expiresAt(new DateTimeImmutable('2026-07-26T00:00:00+10:00'), 30);
$expect($expires?->format('Y-m-d') === '2026-08-25', 'Thirty-day retention date is incorrect.');
$expect(MemoryRetentionPolicy::expiresAt(new DateTimeImmutable(), null) === null, 'Null retention must not expire.');
$expect(MemoryRetentionPolicy::normaliseConfidence(1.4) === 1.0, 'Confidence must clamp high.');
$expect(MemoryRetentionPolicy::normaliseConfidence(-0.2) === 0.0, 'Confidence must clamp low.');

$models = [
    ['id' => 'cheap', 'capabilities' => ['chat'], 'cost' => 1.0, 'latency_ms' => 900, 'quality' => 0.7],
    ['id' => 'fast-tools', 'capabilities' => ['chat','tools'], 'cost' => 2.0, 'latency_ms' => 300, 'quality' => 0.8],
    ['id' => 'premium-tools', 'capabilities' => ['chat','tools'], 'cost' => 8.0, 'latency_ms' => 500, 'quality' => 0.95],
];
$ranked = ModelRoutingPolicy::rank($models, ['tools'], 5.0, 1000);
$expect(count($ranked) === 1, 'Routing constraints must filter incompatible or over-budget models.');
$expect($ranked[0]['id'] === 'fast-tools', 'Compatible model should rank first.');
$expect(ModelRoutingPolicy::rank($models, ['vision'], null, null) === [], 'Unsupported capability must fail closed.');

$issued = ShareToken::issue(static fn (int $length): string => str_repeat('a', $length));
$expect(strlen($issued['token']) === 64, 'Share token must be 64 characters.');
$expect($issued['hash'] !== $issued['token'], 'Share token must not be stored in plaintext.');
$expect(ShareToken::verify($issued['token'], $issued['hash']), 'Share token verification failed.');
$expect(! ShareToken::verify('wrong', $issued['hash']), 'Wrong share token must fail.');

$expect(AgentRunState::canTransition('queued', 'running'), 'Queued run should start.');
$expect(AgentRunState::canTransition('running', 'succeeded'), 'Running run should succeed.');
$expect(AgentRunState::canTransition('running', 'failed'), 'Running run should fail.');
$expect(! AgentRunState::canTransition('succeeded', 'running'), 'Completed run cannot restart.');
$expect(AgentRunState::terminal('cancelled'), 'Cancelled run must be terminal.');

$expect(VoiceSessionState::canTransition('created', 'connecting'), 'Voice session should connect.');
$expect(VoiceSessionState::canTransition('connecting', 'active'), 'Voice session should become active.');
$expect(VoiceSessionState::canTransition('active', 'completed'), 'Active voice session should complete.');
$expect(! VoiceSessionState::canTransition('completed', 'active'), 'Completed voice session cannot reactivate.');
$expect(VoiceSessionState::terminal('failed'), 'Failed voice session must be terminal.');

fwrite(STDOUT, "Titan Intelligence domain: {$checks} checks passed.\n");
