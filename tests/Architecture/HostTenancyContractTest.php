<?php

declare(strict_types=1);

it('does not accept company authority from request input', function (): void {
    $source = file_get_contents(base_path('app/Titan/Tenancy/CompanyContextResolver.php'));

    expect($source)->not->toContain("input('company_id'")
        ->and($source)->not->toContain('input("company_id"')
        ->and($source)->toContain('CompanyMembership');
});

it('creates host-owned company membership tables', function (): void {
    $source = file_get_contents(base_path('database/migrations/2026_07_25_000100_create_titan_company_foundation.php'));

    expect($source)->toContain("Schema::create('tz_companies'")
        ->and($source)->toContain("Schema::create('tz_company_memberships'")
        ->and($source)->toContain("active_company_id");
});
