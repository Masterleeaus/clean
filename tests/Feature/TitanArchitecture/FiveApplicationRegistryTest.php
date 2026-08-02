<?php

declare(strict_types=1);

namespace Tests\Feature\TitanArchitecture;

use App\Extensions\Chatbot\System\TitanShell\PlatformApplicationRegistry;
use App\Extensions\Chatbot\System\TitanShell\TemplateSchema;
use Tests\TestCase;

final class FiveApplicationRegistryTest extends TestCase
{
    private const APPLICATION_SLUGS = [
        'titan-zero',
        'titan-go',
        'titan-launch',
        'titan-desk',
        'titan-hub',
    ];

    public function test_registry_exposes_exactly_five_platform_applications(): void
    {
        self::assertSame(self::APPLICATION_SLUGS, array_keys(PlatformApplicationRegistry::all()));
    }

    public function test_legacy_slugs_resolve_to_canonical_applications(): void
    {
        self::assertSame('titan-go', PlatformApplicationRegistry::canonicalSlug('titan-dispatch'));
        self::assertSame('titan-desk', PlatformApplicationRegistry::canonicalSlug('titan-front-desk'));
        self::assertSame('titan-launch', PlatformApplicationRegistry::canonicalSlug('titan-sprout'));
        self::assertSame('titan-zero', PlatformApplicationRegistry::canonicalSlug('titan-money'));
        self::assertSame('titan-zero', PlatformApplicationRegistry::canonicalSlug('titan-analytics'));
        self::assertSame('titan-hub', PlatformApplicationRegistry::canonicalSlug('titan-hub'));
        self::assertNull(PlatformApplicationRegistry::canonicalSlug('titan-train'));
    }

    public function test_template_schema_lists_only_canonical_platform_applications(): void
    {
        $schemas = TemplateSchema::all();

        self::assertCount(5, $schemas);
        self::assertSame(
            self::APPLICATION_SLUGS,
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

    public function test_published_index_contains_only_the_five_platform_applications(): void
    {
        $index = json_decode(
            (string) file_get_contents(base_path('app/Extensions/Chatbot/resources/titan-apps/TemplateSchemas/index.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        self::assertSame('platform-application-index', $index['kind']);
        self::assertSame(
            self::APPLICATION_SLUGS,
            array_column(array_column($index['templates'], 'identity'), 'slug'),
        );
        self::assertSame('titan-launch', $index['legacy_slug_map']['titan-sprout']);
        self::assertSame('titan-desk', $index['legacy_slug_map']['titan-front-desk']);
    }

    public function test_builder_and_operational_runtime_no_longer_advertise_fourteen_apps(): void
    {
        $builder = (string) file_get_contents(base_path('app/Extensions/Chatbot/resources/views/home/edit-window/edit-steps/titan-shell-builder.blade.php'));
        $runtime = (string) file_get_contents(base_path('app/Extensions/Chatbot/resources/js/titan-operational-screens.js'));

        self::assertStringNotContainsString('14 apps', $builder);
        self::assertStringContainsString('count($titanBuilderSchemas)', $builder);

        preg_match_all("/^\s+'titan-[^']+': \{ headline:/m", $runtime, $profiles);
        self::assertCount(5, $profiles[0]);

        foreach (self::APPLICATION_SLUGS as $slug) {
            self::assertStringContainsString("'{$slug}': { headline:", $runtime);
        }
    }
}
