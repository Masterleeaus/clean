<?php

declare(strict_types=1);

$path = dirname(__DIR__, 2) . '/app/Http/Controllers/Api/AIRealTimeChatController.php';
$contents = file_get_contents($path);
if (! is_string($contents)) {
    throw new RuntimeException("Unable to read {$path}.");
}

$constructor = <<<'PHP'
    protected $settings;

    protected $settings_two;

    public function __construct()
    {
        // Settings
        $this->settings = Setting::getCache();
        $this->settings_two = SettingTwo::getCache();
        $apiKey = ApiHelper::setOpenAiKey();
        config(['openai.api_key' => $apiKey]);
        set_time_limit(120);
    }

PHP;
if (str_contains($contents, $constructor)) {
    $contents = str_replace($constructor, '', $contents, $count);
    if ($count !== 1) {
        throw new RuntimeException("Realtime constructor anchor occurred {$count} times.");
    }
}

foreach (["use App\\Models\\Setting;\n", "use App\\Models\\SettingTwo;\n"] as $import) {
    $contents = str_replace($import, '', $contents);
}

$methodAnchor = <<<'PHP'
    public function chatOutput(Request $request): JsonResponse|StreamedResponse
    {
PHP;
$methodReplacement = <<<'PHP'
    private function configureOpenAiRuntime(): void
    {
        $apiKey = ApiHelper::setOpenAiKey();
        config(['openai.api_key' => $apiKey]);
        set_time_limit(120);
    }

    public function chatOutput(Request $request): JsonResponse|StreamedResponse
    {
        $this->configureOpenAiRuntime();
PHP;
if (! str_contains($contents, '$this->configureOpenAiRuntime();')) {
    if (! str_contains($contents, $methodAnchor)) {
        throw new RuntimeException('Realtime chatOutput patch anchor was not found.');
    }
    $contents = str_replace($methodAnchor, $methodReplacement, $contents, $count);
    if ($count !== 1) {
        throw new RuntimeException("Realtime chatOutput anchor occurred {$count} times.");
    }
}

file_put_contents($path, $contents);
fwrite(STDOUT, "Realtime route inspection safety patch applied.\n");
