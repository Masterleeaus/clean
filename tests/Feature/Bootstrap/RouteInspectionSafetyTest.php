<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/** @return list<string> */
function titanZeroRouteInspectionSafetyIssues(string $root): array
{
    $issues = [];
    $path = $root . '/app/Http/Controllers/Api/AIChatController.php';
    $source = (string) @file_get_contents($path);

    if ($source === '') {
        return ['AIChatController source is missing.'];
    }
    if (str_contains($source, 'Setting::getCache()') || str_contains($source, 'SettingTwo::getCache()')) {
        $issues[] = 'AIChatController constructor still queries settings during route inspection.';
    }
    if (! str_contains($source, '$this->configureOpenAiRuntime();')) {
        $issues[] = 'chatOutput does not initialise its OpenAI runtime lazily.';
    }
    if (! str_contains($source, 'private function configureOpenAiRuntime(): void')) {
        $issues[] = 'AIChatController has no request-scoped OpenAI runtime initialiser.';
    }

    return $issues;
}

if (class_exists(TestCase::class)) {
    final class RouteInspectionSafetyTest extends TestCase
    {
        public function test_route_listing_does_not_trigger_database_backed_controller_setup(): void
        {
            self::assertSame([], titanZeroRouteInspectionSafetyIssues(dirname(__DIR__, 3)));
        }
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $issues = titanZeroRouteInspectionSafetyIssues(dirname(__DIR__, 3));
    if ($issues !== []) {
        fwrite(STDERR, implode(PHP_EOL, $issues) . PHP_EOL);
        exit(1);
    }
    fwrite(STDOUT, "Route inspection safety test passed.\n");
}
