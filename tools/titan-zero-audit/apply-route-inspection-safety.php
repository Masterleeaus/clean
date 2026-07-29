<?php

declare(strict_types=1);

$path = dirname(__DIR__, 2) . '/app/Http/Controllers/Api/AIChatController.php';
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
        throw new RuntimeException("AIChatController constructor anchor occurred {$count} times.");
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
    public function chatOutput(Request $request): JsonResponse|StreamedResponse
    {
        $this->configureOpenAiRuntime();
PHP;
if (! str_contains($contents, '$this->configureOpenAiRuntime();')) {
    if (! str_contains($contents, $methodAnchor)) {
        throw new RuntimeException('chatOutput patch anchor was not found.');
    }
    $contents = str_replace($methodAnchor, $methodReplacement, $contents, $count);
    if ($count !== 1) {
        throw new RuntimeException("chatOutput patch anchor occurred {$count} times.");
    }
}

$runtimeMethod = <<<'PHP'
    private function configureOpenAiRuntime(): void
    {
        $apiKey = ApiHelper::setOpenAiKey();
        config(['openai.api_key' => $apiKey]);
        set_time_limit(120);
    }

PHP;
if (! str_contains($contents, 'private function configureOpenAiRuntime(): void')) {
    $anchor = "    public function changeChatTitle(Request \$request): JsonResponse\n";
    if (! str_contains($contents, $anchor)) {
        throw new RuntimeException('changeChatTitle patch anchor was not found.');
    }
    $contents = str_replace($anchor, $runtimeMethod . $anchor, $contents, $count);
    if ($count !== 1) {
        throw new RuntimeException("changeChatTitle patch anchor occurred {$count} times.");
    }
}

file_put_contents($path, $contents);
fwrite(STDOUT, "Route inspection safety patch applied.\n");
