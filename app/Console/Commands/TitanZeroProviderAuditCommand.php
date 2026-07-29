<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Marketplace\MarketplaceServiceProvider;
use App\Support\Extensions\ExtensionCatalog;
use App\Support\Extensions\ExtensionProviderAudit;
use App\Support\Extensions\ExtensionProviderAuditResult;
use Illuminate\Console\Command;

final class TitanZeroProviderAuditCommand extends Command
{
    protected $signature = 'titan-zero:provider-audit
        {--json : Emit machine-readable JSON}
        {--strict : Return failure when any extension is Red}';

    protected $description = 'Audit extension manifests and source-level provider collisions without enabling or mutating extensions.';

    public function handle(): int
    {
        $catalog = new ExtensionCatalog(
            extensionsPath: app_path('Extensions'),
            enabledIdentifiers: array_values((array) config('titan-zero.extensions.enabled', [])),
            legacyProviders: MarketplaceServiceProvider::getExtensionProviders(),
        );

        $results = (new ExtensionProviderAudit(
            catalog: $catalog,
            extensionsPath: app_path('Extensions'),
            hostSourcePaths: [
                app_path('Providers'),
                app_path('Http'),
                app_path('Domains'),
                base_path('routes'),
                database_path('migrations'),
            ],
        ))->audit();

        $counts = ['Green' => 0, 'Amber' => 0, 'Red' => 0];
        foreach ($results as $result) {
            $counts[$result->status] = ($counts[$result->status] ?? 0) + 1;
        }

        if ($this->option('json')) {
            $this->line(json_encode([
                'summary' => [
                    'green' => $counts['Green'],
                    'amber' => $counts['Amber'],
                    'red' => $counts['Red'],
                    'total' => count($results),
                ],
                'extensions' => array_map(
                    static fn (ExtensionProviderAuditResult $result): array => [
                        'directory' => $result->manifest->directory,
                        'name' => $result->manifest->name,
                        'version' => $result->manifest->version,
                        'provider' => $result->manifest->provider,
                        'enabled' => $result->manifest->enabled,
                        'status' => $result->status,
                        'diagnostics' => $result->diagnostics,
                    ],
                    $results,
                ),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(
                ['Status', 'Extension', 'Version', 'Enabled', 'Findings'],
                array_map(
                    static fn (ExtensionProviderAuditResult $result): array => [
                        $result->status,
                        $result->manifest->directory,
                        $result->manifest->version,
                        $result->manifest->enabled ? 'yes' : 'no',
                        $result->diagnostics === [] ? 'none' : implode(' | ', $result->diagnostics),
                    ],
                    $results,
                ),
            );
            $this->line(sprintf('Green=%d Amber=%d Red=%d', $counts['Green'], $counts['Amber'], $counts['Red']));
        }

        return $this->option('strict') && $counts['Red'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
