<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/** @return list<string> */
function titanZeroRouteInspectionSafetyIssues(string $root): array
{
    $issues = [];

    foreach ([
        'AIChatController' => $root . '/app/Http/Controllers/Api/AIChatController.php',
        'AIRealTimeChatController' => $root . '/app/Http/Controllers/Api/AIRealTimeChatController.php',
    ] as $controller => $path) {
        $source = (string) @file_get_contents($path);
        if ($source === '') {
            $issues[] = "{$controller} source is missing.";
            continue;
        }
        if (str_contains($source, 'Setting::getCache()') || str_contains($source, 'SettingTwo::getCache()')) {
            $issues[] = "{$controller} still queries settings during route inspection.";
        }
        if (! str_contains($source, '$this->configureOpenAiRuntime();')) {
            $issues[] = "{$controller} does not initialise its OpenAI runtime lazily.";
        }
        if (! str_contains($source, 'private function configureOpenAiRuntime(): void')) {
            $issues[] = "{$controller} has no request-scoped OpenAI runtime initialiser.";
        }
    }

    $cacheTrait = (string) @file_get_contents($root . '/app/Models/Concerns/HasCacheFirst.php');
    if (! str_contains($cacheTrait, 'app()->runningInConsole()')) {
        $issues[] = 'HasCacheFirst has no console-only missing-schema guard.';
    }
    if (! str_contains($cacheTrait, 'Schema::hasTable($model->getTable())')) {
        $issues[] = 'HasCacheFirst does not verify its table before console inspection.';
    }
    if (! str_contains($cacheTrait, 'static::query()->first() ?? $model')) {
        $issues[] = 'HasCacheFirst does not return an empty model when a configured row is absent.';
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
