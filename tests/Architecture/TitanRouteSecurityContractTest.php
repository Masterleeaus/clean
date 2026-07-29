<?php

declare(strict_types=1);

it('protects all Titan API routes with authentication and company context', function (): void {
    $source = file_get_contents(base_path('routes/titan.php'));

    expect($source)->toContain("middleware(['auth:sanctum', 'titan.company'])")
        ->and($source)->not->toContain("input('company_id'")
        ->and($source)->not->toContain('input("company_id"');
});

it('dispatches WorkCore actions using server resolved company context', function (): void {
    $source = file_get_contents(base_path('app/Http/Controllers/Titan/WorkCoreActionController.php'));

    expect($source)->toContain('ActiveCompanyContext')
        ->and($source)->toContain('BusinessActionDispatcher')
        ->and($source)->not->toContain("request('company_id'")
        ->and($source)->not->toContain("input('company_id'");
});
