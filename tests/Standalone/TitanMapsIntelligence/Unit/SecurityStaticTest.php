<?php

declare(strict_types=1);

return static function (): void {
    $root = dirname(__DIR__, 4);
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/app/Extensions/TitanMapsIntelligence'));
    $combined = '';
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $combined .= file_get_contents($file->getPathname());
        }
    }
    foreach (['shell_exec(', 'exec(', 'passthru(', 'proc_open(', 'verify(false)', 'data-excavator.com', 'CefSharp', 'Google Maps Scraper PRO'] as $prohibited) {
        assert_true(! str_contains($combined, $prohibited), 'Prohibited implementation marker: '.$prohibited);
    }
    assert_true(! preg_match('/["\'](?:AIza|sk-|ghp_)[A-Za-z0-9_-]{12,}/', $combined), 'Hard-coded secret-like value found');

    $controllers = glob($root.'/app/Extensions/TitanMapsIntelligence/Http/Controllers/*.php') ?: [];
    foreach ($controllers as $controller) {
        $content = (string) file_get_contents($controller);
        assert_true(str_contains($content, 'AuthorisedCompanyContext'));
        assert_true(! str_contains($content, "input('company_id'"));
        assert_true(! str_contains($content, 'request()->company_id'));
    }
};
