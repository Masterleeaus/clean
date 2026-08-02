<?php

declare(strict_types=1);

namespace Tests\Feature\TitanArchitecture;

use App\Extensions\Chatbot\System\TitanShell\PlatformApplicationRegistry;
use App\Extensions\Chatbot\System\TitanShell\TemplateSchema;
use Tests\TestCase;

final class FiveApplicationRegistryTest extends TestCase
{
    public function test_registry_exposes_exactly_five_platform_applications(): void
    {
        self::assertSame(
            ['titan-zero', 'titan-go', 'titan-launch', 'titan-desk', 'titan-hub'],
            array_keys(PlatformApplicationRegistry::all()),
        );
    }

    public function test_legacy_slugs_resolve_to_canonical_applications(): void
    {
        self::assertSame('titan-go', PlatformApplicationRegistry::canonicalSlug('titan-dispatch'));
        self::assertSame('titan-desk', PlatformApplicationRegistry::canonicalSlug('titan-front-desk'));
        self::assertSame('titan-launch', PlatformApplicationRegistry::canonicalSlug('titan-sprout'));
        self::assertSame('titan-zero', PlatformApplicationRegistry::canonicalSlug('titan-money'));
        self::assertSame('titan-zero', PlatformApplicationRegistry::canonicalSlug('titan-analytics'));
        self::assertSame('titan-hub', PlatformApplicationRegistry::canonicalSlug('titan-hub'));
    }

    public function test_template_schema_lists_only_canonical_platform_applications(): void
    {
        $schemas = TemplateSchema::all();

        self::assertCount(5, $schemas);
        self::assertSame(
            ['titan-zero', 'titan-go', 'titan-launch', 'titan-desk', 'titan-hub'],
            array_column(array_column($schemas, 'identity'), 'slug'),
        );
    }

    public function test_legacy_schema_resolution_returns_the_canonical_identity(): void
    {
        self::assertSame('titan-go', TemplateSchema::resolve('titan-dispatch')['identity']['slug']);
        self::assertSame('titan-desk', TemplateSchema::resolve('titan-front-desk')['identity']['slug']);
        self::assertSame('titan-launch', TemplateSchema::resolve('titan-sprout')['identity']['slug']);
    }

    public function test_registry_keeps_templates_modules_and_engines_out_of_the_application_list(): void
    {
        $slugs = array_keys(PlatformApplicationRegistry::all());

        self::assertNotContains('titan-sprout', $slugs, true);
        self::assertNotContains('titan-dispatch', $slugs, true);
        self::assertNotContains('titan-money', $slugs, true);
        self::assertNotContains('titan-quality', $slugs, true);
        self::assertNotContains('titan-train', $slugs, true);
    }
}
