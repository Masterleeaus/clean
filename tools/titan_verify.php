<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$section = null;
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--section=')) {
        $section = substr($argument, strlen('--section='));
    }
}

$failures = [];
$passes = [];

$check = static function (bool $condition, string $label) use (&$failures, &$passes): void {
    if ($condition) {
        $passes[] = $label;
        return;
    }

    $failures[] = $label;
};

$requiredBySection = [
    'tenancy' => [
        'app/Titan/Tenancy/CompanyContextResolver.php',
        'app/Titan/Tenancy/EnsureActiveCompany.php',
        'app/Titan/Permissions/PermissionResolver.php',
        'app/Models/Company.php',
        'app/Models/CompanyMembership.php',
        'database/migrations/2026_07_25_000100_create_titan_company_foundation.php',
        'app/Titan/Tenancy/ActiveCompanyContext.php',
    ],
    'platform' => [
        'app/Titan/Extensions/ExtensionRegistry.php',
        'app/Titan/Extensions/ExtensionManager.php',
        'app/Titan/Vault/DatabaseVault.php',
        'app/Titan/Audit/DatabaseAuditRecorder.php',
        'app/Titan/Capabilities/CapabilityRegistry.php',
        'app/Providers/TitanServiceProvider.php',
        'database/migrations/2026_07_25_000110_create_titan_platform_tables.php',
        'config/titan.php',
    ],
    'workcore' => [
        'app/Domains/WorkCore/WorkCoreServiceProvider.php',
        'app/Domains/WorkCore/System/Contracts/TenantContextContract.php',
        'app/Titan/WorkCore/MeetupTenantResolver.php',
        'config/workcore.php',
        'database/migrations/2026_07_25_010000_create_workcore_crm_tables.php',
        'database/migrations/2026_07_26_020000_create_tz_documents_and_evidence_tables.php',
        'database/migrations/2026_07_26_020010_create_tz_assurance_tables.php',
        'app/Domains/WorkCore/System/Modules/Documents/Providers/WorkDocumentsServiceProvider.php',
        'app/Domains/WorkCore/System/Modules/Assurance/Providers/WorkAssuranceServiceProvider.php',
        'app/Domains/WorkCore/System/ReadModels/ReadModelExecutor.php',
        'resources/workcore/verticals/property_management.json',
        'resources/workcore/verticals/field_services_core.json',
    ],
    'routes' => [
        'routes/titan.php',
        'app/Http/Controllers/Titan/CompanyContextController.php',
        'app/Http/Controllers/Titan/WorkCoreSummaryController.php',
        'app/Http/Controllers/Titan/WorkCoreActionController.php',
        'app/Http/Controllers/Titan/WorkCoreReadController.php',
        'app/Http/Controllers/Titan/CapabilityController.php',
    ],
    'maps' => [
        'app/Extensions/TitanMapsIntelligence/extension.json',
        'app/Extensions/TitanMapsIntelligence/TitanMapsIntelligenceServiceProvider.php',
        'app/Titan/Maps/MapsCredentialResolver.php',
        'app/Titan/Maps/MapsCompanyContext.php',
        'app/Titan/Maps/MapsPermissionGateway.php',
        'app/Titan/Maps/MapsAuditGateway.php',
        'app/Titan/Maps/MapsWorkCoreGateway.php',
        'app/Titan/Maps/MapsPrivateExportStore.php',
        'app/Titan/Maps/MapsCapabilityRegistrar.php',
        'database/migrations/extensions/titan_maps/2026_07_25_020001_create_maps_provider_connections_table.php',
    ],
    'ai' => [
        'app/Titan/AI/TitanZeroOrchestrator.php',
        'app/Titan/AI/ConversationContextBuilder.php',
        'app/Titan/AI/ToolRouter.php',
        'app/Services/AIAssistantService.php',
        'database/migrations/2026_07_25_000120_add_company_scope_to_conversations.php',
    ],
    'intelligence' => [
        'app/Titan/Intelligence/Providers/TitanIntelligenceServiceProvider.php',
        'app/Titan/Intelligence/Contracts/IntelligenceRepository.php',
        'app/Titan/Intelligence/Repositories/DatabaseIntelligenceRepository.php',
        'app/Titan/Intelligence/Domain/MemoryRetentionPolicy.php',
        'app/Titan/Intelligence/Domain/ModelRoutingPolicy.php',
        'app/Titan/Intelligence/Domain/ShareToken.php',
        'app/Titan/Intelligence/Actions/AssignAgentToolAction.php',
        'app/Titan/Intelligence/Actions/CreateConnectionAction.php',
        'app/Titan/Intelligence/Actions/CreateProviderConnectionAction.php',
        'database/migrations/2026_07_26_050000_create_titan_intelligence_runtime_tables.php',
        'config/titan_intelligence.php',
        'docs/integration/BASE_AI_EXTENSION_RECONCILIATION.md',
    ],
    'creative' => [
        'app/Titan/Creative/Providers/TitanCreativeServiceProvider.php',
        'app/Titan/Creative/Contracts/CreativeRepository.php',
        'app/Titan/Creative/Repositories/DatabaseCreativeRepository.php',
        'app/Titan/Creative/Domain/CampaignSchedulePolicy.php',
        'app/Titan/Creative/Domain/ContentApprovalPolicy.php',
        'app/Titan/Creative/Domain/GenerationJobState.php',
        'app/Titan/Creative/Domain/PublicationState.php',
        'database/migrations/2026_07_26_060000_create_titan_creative_marketing_tables.php',
        'config/titan_creative.php',
        'docs/integration/MARKETING_CREATIVE_RECONCILIATION.md',
    ],
    'ui' => [
        'app/Http/Controllers/Titan/OperationsController.php',
        'app/Http/Controllers/Titan/CompanySetupController.php',
        'app/Http/Controllers/Titan/AiConfigurationController.php',
        'resources/views/titan/operations.blade.php',
        'resources/views/titan/company-setup.blade.php',
        'resources/js/titan/operations.js',
        'public/js/titan-operations.js',
    ],
    'release' => [
        'docs/integration/AUTHORITY_AND_PROVENANCE.md',
        'docs/integration/WORKCORE_REPAIR_REPORT.md',
        'docs/integration/BOS_ASSURANCE_EVIDENCE_DELTA.md',
        'docs/integration/MEETUP_HOST_ADAPTERS.md',
        'docs/integration/EXTENSION_REGISTRY.md',
        'docs/integration/REMAINING_WORK.md',
        'docs/integration/BASE_AI_EXTENSION_RECONCILIATION.md',
        'docs/integration/MARKETING_CREATIVE_RECONCILIATION.md',
        'APP_DIRECTORY_TREE.txt',
        '.titan/docs/APP_DIRECTORY_SUMMARY.md',
        '.titan/system/BUILD_REPORT.md',
        'README.md',
        'tools/titan_namespace_scan.php',
    ],
];

$required = $section === null
    ? array_values(array_unique(array_merge(...array_values($requiredBySection))))
    : ($requiredBySection[$section] ?? []);

foreach ($required as $path) {
    $check(is_file($root . '/' . $path), "required path exists: {$path}");
}




if ($section === null || $section === 'tenancy') {
    $resolverPath = $root . '/app/Titan/Tenancy/CompanyContextResolver.php';
    if (is_file($resolverPath)) {
        $resolverSource = (string) file_get_contents($resolverPath);
        $check(! str_contains($resolverSource, "input('company_id'"), 'tenant resolver ignores request body company_id');
        $check(! str_contains($resolverSource, 'input("company_id"'), 'tenant resolver ignores quoted request body company_id');
        $check(str_contains($resolverSource, 'CompanyMembership'), 'tenant resolver validates company membership');
    }

    $migrationPath = $root . '/database/migrations/2026_07_25_000100_create_titan_company_foundation.php';
    if (is_file($migrationPath)) {
        $migrationSource = (string) file_get_contents($migrationPath);
        $check(str_contains($migrationSource, "Schema::create('tz_companies'"), 'host owns tz_companies table');
        $check(str_contains($migrationSource, "Schema::create('tz_company_memberships'"), 'host owns tz_company_memberships table');
        $check(str_contains($migrationSource, 'active_company_id'), 'users receive active company context');
    }
}


if ($section === null || $section === 'platform') {
    $registryPath = $root . '/app/Titan/Extensions/ExtensionRegistry.php';
    if (is_file($registryPath)) {
        $registrySource = (string) file_get_contents($registryPath);
        $check(str_contains($registrySource, "'domain'"), 'extension registry explicitly rejects domain type');
        $check(str_contains($registrySource, 'duplicate capability'), 'extension registry detects duplicate capabilities');
        $check(str_contains($registrySource, 'enabled_by_default'), 'extension registry reads default enablement');
    }

    $vaultPath = $root . '/app/Titan/Vault/DatabaseVault.php';
    if (is_file($vaultPath)) {
        $vaultSource = (string) file_get_contents($vaultPath);
        $check(str_contains($vaultSource, 'Crypt::encryptString'), 'Vault encrypts secret values');
        $check(str_contains($vaultSource, 'Crypt::decryptString'), 'Vault decrypts secret values only on resolution');
    }

    $platformMigration = $root . '/database/migrations/2026_07_25_000110_create_titan_platform_tables.php';
    if (is_file($platformMigration)) {
        $migrationSource = (string) file_get_contents($platformMigration);
        foreach (['titan_vault_secrets', 'titan_audit_logs', 'titan_extensions', 'titan_capabilities'] as $table) {
            $check(str_contains($migrationSource, "Schema::create('{$table}'"), "platform migration creates {$table}");
        }
    }
}


if ($section === null || $section === 'workcore') {
    $workCoreRoot = $root . '/app/Domains/WorkCore';
    $check(! is_dir($workCoreRoot . '/IntegratedSources'), 'WorkCore donor IntegratedSources remain quarantined');
    $check(! is_dir($workCoreRoot . '/System/Rewind'), 'incomplete WorkCore Rewind remains quarantined');
    $check(! is_dir($workCoreRoot . '/System/Intelligence'), 'incomplete WorkCore Intelligence remains quarantined');
    $check(! is_file($root . '/app/Extensions/WorkCore/extension.json'), 'WorkCore is not installed as an extension');

    $middlewarePath = $root . '/app/Titan/Tenancy/EnsureActiveCompany.php';
    if (is_file($middlewarePath)) {
        $middlewareSource = (string) file_get_contents($middlewarePath);
        $check(str_contains($middlewareSource, 'TenantContextContract'), 'tenant middleware bridges host context into WorkCore');
        $check(str_contains($middlewareSource, 'OperationContextContract'), 'tenant middleware creates WorkCore operation context');
        $check(str_contains($middlewareSource, 'finally'), 'tenant middleware clears scoped WorkCore context after request');
    }

    $workCoreProviderPath = $root . '/app/Domains/WorkCore/WorkCoreServiceProvider.php';
    if (is_file($workCoreProviderPath)) {
        $providerSource = (string) file_get_contents($workCoreProviderPath);
        $check(str_contains($providerSource, "'documents' => WorkDocumentsServiceProvider::class"), 'WorkCore registers canonical Documents module');
        $check(str_contains($providerSource, "'assurance' => WorkAssuranceServiceProvider::class"), 'WorkCore registers canonical Assurance module');
        $check(str_contains($providerSource, 'ReadModelExecutor::class'), 'WorkCore registers governed read executor');
    }

    foreach ([
        'database/migrations/2026_07_26_020000_create_tz_documents_and_evidence_tables.php' => ['tz_documents', 'tz_document_versions', 'tz_evidence_items', 'tz_evidence_signoffs'],
        'database/migrations/2026_07_26_020010_create_tz_assurance_tables.php' => ['tz_inspections', 'tz_assurance_findings', 'tz_corrective_actions', 'tz_risk_register', 'tz_incidents'],
    ] as $migration => $tables) {
        $path = $root . '/' . $migration;
        if (! is_file($path)) {
            continue;
        }
        $source = (string) file_get_contents($path);
        foreach ($tables as $table) {
            $check(str_contains($source, "Schema::create('{$table}'"), "canonical migration creates {$table}");
        }
    }

    if (is_dir($workCoreRoot)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($workCoreRoot, FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if (! $file->isFile() || strtolower($file->getExtension()) !== 'php') {
                continue;
            }
            $source = (string) file_get_contents($file->getPathname());
            $relative = ltrim(substr(str_replace('\\', '/', $file->getPathname()), strlen(str_replace('\\', '/', $root))), '/');
            $check(! str_contains($source, 'App\\Extensions\\WorkCore'), "WorkCore namespace rebased: {$relative}");
        }
    }
}


if ($section === null || $section === 'routes') {
    $routesPath = $root . '/routes/titan.php';
    if (is_file($routesPath)) {
        $routesSource = (string) file_get_contents($routesPath);
        $check(str_contains($routesSource, "middleware(['auth:sanctum', 'titan.company'])"), 'Titan API routes require authentication and active company context');
        $check(! str_contains($routesSource, "input('company_id'"), 'Titan routes do not trust request body company_id');
        $check(! str_contains($routesSource, 'input("company_id"'), 'Titan routes do not trust quoted request body company_id');
    }

    $actionPath = $root . '/app/Http/Controllers/Titan/WorkCoreActionController.php';
    if (is_file($actionPath)) {
        $actionSource = (string) file_get_contents($actionPath);
        $check(str_contains($actionSource, 'ActiveCompanyContext'), 'WorkCore actions use server-resolved company context');
        $check(str_contains($actionSource, 'BusinessActionDispatcher'), 'WorkCore actions use governed dispatcher');
        $check(! str_contains($actionSource, "input('company_id'"), 'WorkCore action controller rejects request company authority');
    }

    $readPath = $root . '/app/Http/Controllers/Titan/WorkCoreReadController.php';
    if (is_file($readPath)) {
        $readSource = (string) file_get_contents($readPath);
        $check(str_contains($readSource, 'ActiveCompanyContext'), 'WorkCore reads use server-resolved company context');
        $check(str_contains($readSource, 'ReadModelExecutor'), 'WorkCore reads use governed executor');
        $check(str_contains($readSource, 'containsTenantAuthority'), 'WorkCore read controller rejects request tenant authority');
    }

    $toolRouterPath = $root . '/app/Titan/AI/ToolRouter.php';
    if (is_file($toolRouterPath)) {
        $toolSource = (string) file_get_contents($toolRouterPath);
        $check(str_contains($toolSource, '$this->readModels->has($tool)'), 'Titan Zero executes only registered WorkCore read models');
        $check(str_contains($toolSource, 'ReadModelExecutor'), 'Titan Zero uses the shared governed read executor');
    }
}



if ($section === null || $section === 'maps') {
    $manifestPath = $root . '/app/Extensions/TitanMapsIntelligence/extension.json';
    if (is_file($manifestPath)) {
        try {
            $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
            $check(($manifest['type'] ?? null) === 'integration', 'Maps is registered as an integration extension');
            $check(($manifest['enabled_by_default'] ?? null) === false, 'Maps is disabled by default');
        } catch (Throwable) {
            $check(false, 'Maps manifest is valid JSON');
        }
    }

    $contextPath = $root . '/app/Titan/Maps/MapsCompanyContext.php';
    if (is_file($contextPath)) {
        $source = (string) file_get_contents($contextPath);
        $check(str_contains($source, 'ActiveCompanyContext'), 'Maps company context is host-owned');
        $check(! str_contains($source, "input('company_id'"), 'Maps company context ignores request company authority');
    }

    $credentialPath = $root . '/app/Titan/Maps/MapsCredentialResolver.php';
    if (is_file($credentialPath)) {
        $source = (string) file_get_contents($credentialPath);
        $check(str_contains($source, 'Vault'), 'Maps credentials resolve through Titan Vault');
        $check(! str_contains($source, 'env('), 'Maps credentials do not read plaintext environment secrets');
    }

    $gatewayPath = $root . '/app/Titan/Maps/MapsWorkCoreGateway.php';
    if (is_file($gatewayPath)) {
        $source = (string) file_get_contents($gatewayPath);
        $check(str_contains($source, 'BusinessActionDispatcher'), 'Maps promotions use WorkCore dispatcher');
        $check(! str_contains($source, "DB::table('tz_"), 'Maps gateway does not write WorkCore tables directly');
    }
}


if ($section === null || $section === 'ai') {
    $servicePath = $root . '/app/Services/AIAssistantService.php';
    if (is_file($servicePath)) {
        $source = (string) file_get_contents($servicePath);
        $check(str_contains($source, 'Vault'), 'AI credentials resolve through Titan Vault');
        $check(str_contains($source, 'ActiveCompanyContext'), 'AI provider configuration is company scoped');
        $check(! str_contains($source, "setting('ai_api_key')"), 'AI service does not read plaintext database API keys');
        $check(! str_contains($source, 'response->body()'), 'AI service does not expose raw provider response bodies');
        $check(! str_contains($source, '?key='), 'Gemini credentials are not placed in URLs');
    }

    $servicesPath = $root . '/config/services.php';
    if (is_file($servicesPath)) {
        $source = (string) file_get_contents($servicesPath);
        $check(! str_contains($source, 'AI_API_KEY'), 'services config contains no plaintext AI secret environment key');
        $check(! str_contains($source, 'OPENROUTER_API_KEY'), 'services config contains no plaintext OpenRouter secret environment key');
    }

    $routerPath = $root . '/app/Titan/AI/ToolRouter.php';
    if (is_file($routerPath)) {
        $source = (string) file_get_contents($routerPath);
        $check(str_contains($source, 'CapabilityRegistry'), 'Titan tool router uses host capability registry');
        $check(str_contains($source, 'BusinessActionRegistry'), 'Titan tool router uses WorkCore action registry');
        $check(str_contains($source, 'BusinessActionDispatcher'), 'Titan tool router uses WorkCore dispatcher');
        $check(! str_contains($source, "DB::table('tz_"), 'Titan tool router does not write WorkCore tables directly');
        $check(! str_contains($source, "\$arguments['company_id']") && ! str_contains($source, '$arguments["company_id"]'), 'Titan tool router does not accept client company authority');
    }

    $orchestratorPath = $root . '/app/Titan/AI/TitanZeroOrchestrator.php';
    if (is_file($orchestratorPath)) {
        $source = (string) file_get_contents($orchestratorPath);
        $check(str_contains($source, 'ConversationContextBuilder'), 'Titan Zero builds governed conversation context');
        $check(str_contains($source, 'ToolRouter'), 'Titan Zero delegates only through the tool router');
    }

    $controllerPath = $root . '/app/Http/Controllers/ChatController.php';
    if (is_file($controllerPath)) {
        $source = (string) file_get_contents($controllerPath);
        $check(! str_contains($source, 'new \App\Services\AIAssistantService'), 'Chat controller receives AI services by dependency injection');
        $check(str_contains($source, 'TitanZeroOrchestrator'), 'Chat controller routes assistant messages through Titan Zero');
    }

    $migrationPath = $root . '/database/migrations/2026_07_25_000120_add_company_scope_to_conversations.php';
    if (is_file($migrationPath)) {
        $source = (string) file_get_contents($migrationPath);
        $check(str_contains($source, 'company_id'), 'AI conversations receive company scope');
        $check(str_contains($source, 'tz_companies'), 'conversation company scope references host companies');
    }
}


if ($section === null || $section === 'intelligence') {
    $providerPath = $root . '/app/Titan/Intelligence/Providers/TitanIntelligenceServiceProvider.php';
    $repositoryPath = $root . '/app/Titan/Intelligence/Repositories/DatabaseIntelligenceRepository.php';
    $migrationPath = $root . '/database/migrations/2026_07_26_050000_create_titan_intelligence_runtime_tables.php';
    $configPath = $root . '/config/titan_intelligence.php';
    $bootstrapProviders = $root . '/bootstrap/providers.php';
    $contextPath = $root . '/app/Titan/AI/ConversationContextBuilder.php';
    $operationsView = $root . '/resources/views/titan/operations.blade.php';

    if (is_file($bootstrapProviders)) {
        $source = (string) file_get_contents($bootstrapProviders);
        $check(str_contains($source, 'TitanIntelligenceServiceProvider::class'), 'Titan Intelligence provider is registered by the host');
    }

    if (is_file($providerPath)) {
        $source = (string) file_get_contents($providerPath);
        $check(substr_count($source, "'titan.") >= 19, 'Titan Intelligence registers at least nineteen governed capabilities');
        $check(str_contains($source, 'IntelligenceRepository::class'), 'Titan Intelligence binds its repository contract');
        $check(! str_contains($source, "DB::table('tz_"), 'Titan Intelligence provider does not write WorkCore tables');
    }

    if (is_file($repositoryPath)) {
        $source = (string) file_get_contents($repositoryPath);
        $check(str_contains($source, "where('company_id', \$companyId)"), 'Titan Intelligence repository scopes records to company context');
        $check(str_contains($source, 'getSchemaBuilder()->hasTable'), 'Titan Intelligence summaries fail closed before migrations');
        $check(! str_contains($source, 'Http::'), 'Titan Intelligence repository performs no direct provider calls');
        $check(! str_contains($source, "DB::table('tz_"), 'Titan Intelligence repository does not write WorkCore tables');
    }

    if (is_file($migrationPath)) {
        $source = (string) file_get_contents($migrationPath);
        foreach (['titan_workspace_folders', 'titan_memories', 'titan_skills', 'titan_agents', 'titan_connections', 'titan_provider_connections', 'titan_model_catalogue', 'titan_voice_sessions', 'titan_announcements', 'titan_onboarding_flows'] as $table) {
            $check(str_contains($source, "Schema::create('{$table}'"), "Titan Intelligence migration creates {$table}");
        }
        $check(str_contains($source, "string('token_hash', 64)"), 'Titan Intelligence stores share tokens as SHA-256 hashes');
        foreach (['api_key', 'access_token', 'refresh_token', 'client_secret', 'password'] as $column) {
            $check(! preg_match("/->(?:string|text)\\('{$column}'/", $source), "Titan Intelligence migration excludes plaintext {$column} columns");
        }
    }

    if (is_file($configPath)) {
        $configuration = require $configPath;
        $classification = is_array($configuration['donor_classification'] ?? null) ? $configuration['donor_classification'] : [];
        $check(count($classification) === 75, 'all seventy-five Base App and AI donor packages have explicit decisions');
        foreach (['ai-chat-pro', 'ai-agent', 'model-council', 'ai-chat-pro-gmail', 'chatbot-booking', 'ugc-creator', 'maintenance'] as $key) {
            $check(isset($classification[$key]), "donor classification records {$key}");
        }
        foreach ((array) ($configuration['connectors'] ?? []) as $key => $definition) {
            $check(($definition['status'] ?? null) === 'adapter-required', "connector {$key} remains disabled pending an adapter");
        }
    }

    $assignTool = $root . '/app/Titan/Intelligence/Actions/AssignAgentToolAction.php';
    if (is_file($assignTool)) {
        $source = (string) file_get_contents($assignTool);
        $check(str_contains($source, 'CapabilityRegistry'), 'agent tools validate Titan capabilities');
        $check(str_contains($source, 'BusinessActionRegistry'), 'agent tools validate WorkCore actions');
        $check(str_contains($source, 'ReadModelRegistry'), 'agent tools validate WorkCore reads');
    }

    if (is_file($contextPath)) {
        $source = (string) file_get_contents($contextPath);
        $check(str_contains($source, 'intelligenceCounts'), 'conversation context includes bounded intelligence counts');
        $check(! str_contains($source, "DB::table('titan_memories')->select"), 'conversation context does not expose memory content');
    }

    if (is_file($operationsView)) {
        $source = (string) file_get_contents($operationsView);
        $check(str_contains($source, 'data-titan-intelligence-summary'), 'Operations includes the Titan Intelligence summary panel');
    }

    $check(! is_dir($root . '/app/Titan/Intelligence/Donors'), 'donor directories remain outside Titan Intelligence runtime');
}



if ($section === null || $section === 'creative') {
    $providerPath = $root . '/app/Titan/Creative/Providers/TitanCreativeServiceProvider.php';
    $repositoryPath = $root . '/app/Titan/Creative/Repositories/DatabaseCreativeRepository.php';
    $migrationPath = $root . '/database/migrations/2026_07_26_060000_create_titan_creative_marketing_tables.php';
    $configPath = $root . '/config/titan_creative.php';

    if (is_file($providerPath)) {
        $source = (string) file_get_contents($providerPath);
        foreach (['titan.brand_kit.create','titan.creative.generation.queue','titan.marketing.campaign.create','titan.marketing.publication.request','titan.creative.summary'] as $capability) {
            $check(str_contains($source, $capability), "creative provider registers {$capability}");
        }
        $check(str_contains($source, "'version' => '0.6.0'"), 'creative provider declares v0.6.0');
        $check(! str_contains($source, 'Http::'), 'creative provider contains no direct provider client');
    }

    if (is_file($repositoryPath)) {
        $source = (string) file_get_contents($repositoryPath);
        $check(str_contains($source, 'assertProviderReference'), 'creative repository validates Titan provider references');
        $check(str_contains($source, 'assertConnectionReference'), 'creative repository validates Titan connector references');
        $check(str_contains($source, 'ContentApprovalPolicy'), 'creative repository enforces approval policy');
        foreach (['api_key','access_token','GuzzleHttp','curl_exec'] as $needle) {
            $check(! str_contains($source, $needle), "creative repository excludes secret/provider pattern {$needle}");
        }
    }

    if (is_file($migrationPath)) {
        $source = (string) file_get_contents($migrationPath);
        foreach (['titan_brand_kits','titan_creative_projects','titan_generation_jobs','titan_campaigns','titan_content_items','titan_publications','titan_analytics_observations'] as $table) {
            $check(str_contains($source, "Schema::create('{$table}'"), "creative migration creates {$table}");
        }
        $check(! str_contains($source, "string('api_key')"), 'creative migration stores no API key');
        $check(! str_contains($source, "string('access_token')"), 'creative migration stores no access token');
    }

    if (is_file($configPath)) {
        $config = require $configPath;
        $decisions = $config['donor_classification'] ?? [];
        $check(is_array($decisions) && count($decisions) === 30, 'creative donor package decision catalogue covers all 30 packages');
        $allowed = ['integrated_concept','optional_adapter','deferred_provider','rejected_duplicate_authority','quarantined'];
        foreach ($decisions as $package => $decision) {
            $check(is_array($decision) && in_array($decision['decision'] ?? null, $allowed, true), "donor package decision is valid: {$package}");
        }
        foreach (($config['provider_adapters'] ?? []) as $provider => $definition) {
            $check(($definition['status'] ?? null) !== 'enabled', "creative provider adapter is not silently enabled: {$provider}");
        }
    }

    $bootstrap = $root . '/bootstrap/providers.php';
    if (is_file($bootstrap)) {
        $source = (string) file_get_contents($bootstrap);
        $check(str_contains($source, 'TitanCreativeServiceProvider::class'), 'host registers Titan Creative provider');
    }
    $check(! is_dir($root . '/app/Extensions/Marketing'), 'runtime excludes donor Marketing directory');
    $check(! is_dir($root . '/app/Extensions/Creative'), 'runtime excludes donor Creative directory');
}

if ($section === null || $section === 'ui') {
    $webRoutes = $root . '/routes/web.php';
    if (is_file($webRoutes)) {
        $source = (string) file_get_contents($webRoutes);
        $check(! str_contains($source, 'Artisan::call'), 'web routes expose no Artisan maintenance actions');
        $check(! str_contains($source, "Route::prefix('system')"), 'public system maintenance route group is removed');
        $check(str_contains($source, "name('titan.operations')"), 'Titan operations interface has a named route');
        $check(str_contains($source, "name('titan.company.setup')"), 'company setup fallback route exists');
        $check(str_contains($source, "middleware(['auth', 'titan.company'])"), 'Titan operations routes require authentication and active company');
    }

    $chatController = $root . '/app/Http/Controllers/ChatController.php';
    if (is_file($chatController)) {
        $source = (string) file_get_contents($chatController);
        $check(substr_count($source, 'forCompany(') >= 10, 'chat conversation access is consistently company scoped');
        $check(str_contains($source, 'CompanyMembership'), 'chat user discovery is limited to active company memberships');
    }

    $settingsView = $root . '/resources/views/system_settings.blade.php';
    if (is_file($settingsView)) {
        $source = (string) file_get_contents($settingsView);
        $check(! str_contains($source, 'ai_api_key'), 'legacy system settings do not render plaintext AI credentials');
        $check(! str_contains($source, "setting('ai_provider')"), 'legacy global AI provider settings are removed');
    }

    $companyModel = $root . '/app/Models/Company.php';
    if (is_file($companyModel)) {
        $source = (string) file_get_contents($companyModel);
        $check(str_contains($source, "'public_id'"), 'company model permits required public identifier creation');
    }

    $companySetupController = $root . '/app/Http/Controllers/Titan/CompanySetupController.php';
    if (is_file($companySetupController)) {
        $source = (string) file_get_contents($companySetupController);
        $check(str_contains($source, "where('status', 'active')"), 'company setup validates active membership before redirecting');
        $check(str_contains($source, "active_company_id' => null"), 'company setup clears stale active company context');
    }

    $aiConfigurationController = $root . '/app/Http/Controllers/Titan/AiConfigurationController.php';
    if (is_file($aiConfigurationController)) {
        $source = (string) file_get_contents($aiConfigurationController);
        $transactionPosition = strpos($source, 'DB::transaction');
        $lockPosition = strpos($source, 'lockForUpdate');
        $vaultPosition = strpos($source, '$vault->put');
        $check(
            is_int($transactionPosition) && is_int($lockPosition) && $transactionPosition < $lockPosition,
            'AI company settings are locked inside a database transaction'
        );
        $check(
            is_int($transactionPosition) && is_int($vaultPosition) && $transactionPosition < $vaultPosition,
            'AI Vault writes participate in the company settings transaction'
        );
    }

    $settingController = $root . '/app/Http/Controllers/SettingController.php';
    if (is_file($settingController)) {
        $source = (string) file_get_contents($settingController);
        $check(str_contains($source, 'ALLOWED_SETTINGS'), 'system setting writes use an explicit allowlist');
        $check(! str_contains($source, "except('_token')"), 'system settings do not persist arbitrary request fields');
    }

    $channelsPath = $root . '/routes/channels.php';
    if (is_file($channelsPath)) {
        $source = (string) file_get_contents($channelsPath);
        $check(str_contains($source, "online.{companyId}"), 'presence channel is company scoped');
        $check(str_contains($source, 'CompanyMembership'), 'presence authorization verifies active company membership');
        $check(str_contains($source, 'forCompany('), 'conversation broadcast authorization is company scoped');
        $check(str_contains($source, 'App.Models.User.{id}.{companyId}'), 'private user broadcast channel is company scoped');
    }

    $conversationCreatedEvent = $root . '/app/Events/ConversationCreated.php';
    if (is_file($conversationCreatedEvent)) {
        $source = (string) file_get_contents($conversationCreatedEvent);
        $check(str_contains($source, '$this->conversation->company_id'), 'new-conversation broadcasts use company-scoped user channels');
        $check(str_contains($source, "'company_id'"), 'new-conversation payload identifies its company');
    }

    $chatScript = $root . '/public/js/chat-app.js';
    if (is_file($chatScript)) {
        $source = (string) file_get_contents($chatScript);
        $check(str_contains($source, 'currentCompanyId'), 'chat client stores active company context');
        $check(str_contains($source, 'attachment_url'), 'chat client renders signed attachment URLs');
        $check(! str_contains($source, '/storage/${msg.attachment'), 'shared-content attachments do not use public paths');
        $check(! str_contains($source, '/storage/${message.attachment'), 'message attachments do not use public paths');
        $check(! str_contains($source, "join('online')"), 'chat client does not join a global presence channel');
        $check(str_contains($source, 'App.Models.User.${ChatApp.currentUser.id}.${ChatApp.currentCompanyId}'), 'chat client subscribes to a company-scoped private user channel');
    }

    $chatView = $root . '/resources/views/chat.blade.php';
    if (is_file($chatView)) {
        $source = (string) file_get_contents($chatView);
        $check(str_contains($source, 'activeCompanyId'), 'chat boot receives server-resolved company context');
    }

    $layoutPath = $root . '/resources/views/layouts/app.blade.php';
    if (is_file($layoutPath)) {
        $source = (string) file_get_contents($layoutPath);
        $check(! str_contains($source, "config('app.admin_email') === auth()->user()->email"), 'authenticated navigation is not hidden from non-admin company users');
    }

    $sidebarPath = $root . '/resources/views/layouts/partials/mini-sidebar.blade.php';
    if (is_file($sidebarPath)) {
        $source = (string) file_get_contents($sidebarPath);
        $check(str_contains($source, "route('titan.operations')"), 'company navigation exposes Titan operations');
        $check(str_contains($source, 'active_company_id'), 'operations navigation is guarded when no company is active');
    }

    $messageController = $root . '/app/Http/Controllers/ChatController.php';
    if (is_file($messageController)) {
        $source = (string) file_get_contents($messageController);
        $check(str_contains($source, "config('titan.private_disk'"), 'new chat attachments use the configured private disk');
        $check(! str_contains($source, "storeAs(\$directory, \$fileName, 'public')"), 'new chat attachments are never stored on the public disk');
    }

    $dashboardController = $root . '/app/Http/Controllers/DashboardController.php';
    if (is_file($dashboardController)) {
        $source = (string) file_get_contents($dashboardController);
        $check(str_contains($source, 'ActiveCompanyContext'), 'dashboard receives host-resolved company context');
        $check(str_contains($source, 'forCompany('), 'dashboard conversations are company scoped');
        $check(str_contains($source, 'companyMemberships'), 'dashboard users are company membership scoped');
    }

    $operationsView = $root . '/resources/views/titan/operations.blade.php';
    if (is_file($operationsView)) {
        $source = (string) file_get_contents($operationsView);
        $check(str_contains($source, 'data-titan-company-switcher'), 'operations interface includes company switching');
        $check(str_contains($source, 'data-titan-workcore-summary'), 'operations interface includes WorkCore summary');
        $check(str_contains($source, 'data-titan-capabilities'), 'operations interface includes capability status');
        $check(str_contains($source, 'data-titan-assurance-summary'), 'operations interface includes WorkCore assurance status');
        $check(! str_contains($source, 'name="company_id"'), 'operations UI does not submit client company authority');
    }
}

if ($section === null || $section === 'release') {
    $releaseDocuments = [
        'docs/integration/AUTHORITY_AND_PROVENANCE.md',
        'docs/integration/WORKCORE_REPAIR_REPORT.md',
        'docs/integration/BOS_ASSURANCE_EVIDENCE_DELTA.md',
        'docs/integration/MEETUP_HOST_ADAPTERS.md',
        'docs/integration/EXTENSION_REGISTRY.md',
        'docs/integration/REMAINING_WORK.md',
        'docs/integration/BASE_AI_EXTENSION_RECONCILIATION.md',
        'docs/integration/MARKETING_CREATIVE_RECONCILIATION.md',
        'APP_DIRECTORY_TREE.txt',
        '.titan/docs/APP_DIRECTORY_SUMMARY.md',
        '.titan/system/BUILD_REPORT.md',
    ];
    foreach ($releaseDocuments as $document) {
        $path = $root . '/' . $document;
        if (! is_file($path)) {
            continue;
        }
        $source = (string) file_get_contents($path);
        $check(trim($source) !== '', "release document is not empty: {$document}");
        $check(! preg_match('/\b(?:TBD|TODO|FIXME)\b/i', $source), "release document has no unresolved placeholders: {$document}");
    }

    $gitignorePath = $root . '/.gitignore';
    if (is_file($gitignorePath)) {
        $source = (string) file_get_contents($gitignorePath);
        foreach (['/.env', '/vendor', '/node_modules', '/.worktrees', '/storage/integration-evidence'] as $entry) {
            $check(str_contains($source, $entry), "gitignore protects local artefact: {$entry}");
        }
    }

    $buildReportPath = $root . '/.titan/system/BUILD_REPORT.md';
    if (is_file($buildReportPath)) {
        $source = (string) file_get_contents($buildReportPath);
        $check(str_contains($source, 'Composer'), 'build report records Composer verification status');
        $check(str_contains($source, 'Laravel boot'), 'build report distinguishes Laravel boot verification');
        $check(str_contains($source, 'Frontend build'), 'build report records frontend build status');
        $check(str_contains($source, 'Quarantined'), 'build report records quarantined sources');
    }
}

$runtimeRoots = ['app', 'bootstrap', 'config', 'database', 'routes', 'tests', 'tools'];
$prohibitedSegments = ['/IntegratedSources/', '/__MACOSX/'];
$prohibitedExtensions = ['pdb', 'dll', 'exe'];

foreach ($runtimeRoots as $runtimeRoot) {
    $directory = $root . '/' . $runtimeRoot;
    if (! is_dir($directory)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }

        $path = str_replace('\\', '/', $file->getPathname());
        $relative = ltrim(substr($path, strlen(str_replace('\\', '/', $root))), '/');
        foreach ($prohibitedSegments as $segment) {
            $check(! str_contains('/' . $relative, $segment), "runtime excludes donor path: {$relative}");
        }
        $extension = strtolower($file->getExtension());
        $check(! in_array($extension, $prohibitedExtensions, true), "runtime excludes prohibited binary: {$relative}");

        if ($extension === 'php') {
            $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file->getPathname()) . ' 2>&1';
            exec($command, $output, $status);
            $check($status === 0, "PHP syntax valid: {$relative}");
            $output = [];
        }
    }
}

printf("Titan verification: %d passed, %d failed\n", count($passes), count($failures));
foreach ($failures as $failure) {
    fwrite(STDERR, "FAIL: {$failure}\n");
}

exit($failures === [] ? 0 : 1);
