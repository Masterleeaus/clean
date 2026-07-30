<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$files = [
    $root . '/app/Domains/WorkCore/System/Modules/Documents/Services/CanonicalPayloadHasher.php',
    $root . '/app/Domains/WorkCore/System/Modules/Documents/Services/EvidenceSignoffPolicy.php',
    $root . '/app/Domains/WorkCore/System/Modules/Assurance/Services/InspectionScoringPolicy.php',
    $root . '/app/Domains/WorkCore/System/Modules/Assurance/Services/RiskMatrix.php',
];

$failures = [];
$passes = [];
$assert = static function (bool $condition, string $label) use (&$failures, &$passes): void {
    if ($condition) {
        $passes[] = $label;
    } else {
        $failures[] = $label;
    }
};

foreach ($files as $file) {
    $assert(is_file($file), 'required policy exists: ' . basename($file));
    if (is_file($file)) {
        require_once $file;
    }
}

if (class_exists(App\Domains\WorkCore\System\Modules\Documents\Services\CanonicalPayloadHasher::class)) {
    $hasher = new App\Domains\WorkCore\System\Modules\Documents\Services\CanonicalPayloadHasher();
    $a = $hasher->hash(['b' => 2, 'a' => ['z' => 3, 'y' => 1]]);
    $b = $hasher->hash(['a' => ['y' => 1, 'z' => 3], 'b' => 2]);
    $assert($a === $b, 'canonical evidence hash ignores associative key order');
    $assert((bool) preg_match('/^[a-f0-9]{64}$/', $a), 'canonical evidence hash is SHA-256 hex');
}

if (class_exists(App\Domains\WorkCore\System\Modules\Documents\Services\EvidenceSignoffPolicy::class)) {
    $policy = new App\Domains\WorkCore\System\Modules\Documents\Services\EvidenceSignoffPolicy();
    $assert($policy->isSignable(['status' => 'active', 'checksum_sha256' => str_repeat('a', 64)]), 'active checksummed evidence is signable');
    $assert(! $policy->isSignable(['status' => 'active', 'checksum_sha256' => null]), 'evidence without checksum is not signable');
    $assert(! $policy->isSignable(['status' => 'void', 'checksum_sha256' => str_repeat('a', 64)]), 'void evidence is not signable');
}

if (class_exists(App\Domains\WorkCore\System\Modules\Assurance\Services\InspectionScoringPolicy::class)) {
    $policy = new App\Domains\WorkCore\System\Modules\Assurance\Services\InspectionScoringPolicy();
    $pass = $policy->score([
        ['result' => 'pass', 'weight' => 2, 'severity' => 'medium'],
        ['result' => 'pass', 'weight' => 1, 'severity' => 'low'],
        ['result' => 'na', 'weight' => 5, 'severity' => 'critical'],
    ]);
    $assert($pass['score'] === 100.0 && $pass['outcome'] === 'pass', 'inspection scoring excludes not-applicable items');
    $conditional = $policy->score([
        ['result' => 'pass', 'weight' => 3, 'severity' => 'medium'],
        ['result' => 'fail', 'weight' => 1, 'severity' => 'high'],
    ]);
    $assert($conditional['score'] === 75.0 && $conditional['outcome'] === 'conditional', 'non-critical inspection failure creates conditional outcome');
    $critical = $policy->score([
        ['result' => 'pass', 'weight' => 4, 'severity' => 'low'],
        ['result' => 'fail', 'weight' => 1, 'severity' => 'critical'],
    ]);
    $assert($critical['outcome'] === 'fail' && $critical['critical_failures'] === 1, 'critical inspection failure forces failed outcome');
}

if (class_exists(App\Domains\WorkCore\System\Modules\Assurance\Services\RiskMatrix::class)) {
    $matrix = new App\Domains\WorkCore\System\Modules\Assurance\Services\RiskMatrix();
    $assert($matrix->assess(1, 1) === ['score' => 1, 'level' => 'low'], 'risk matrix classifies low risk');
    $assert($matrix->assess(3, 3) === ['score' => 9, 'level' => 'medium'], 'risk matrix classifies medium risk');
    $assert($matrix->assess(4, 4) === ['score' => 16, 'level' => 'high'], 'risk matrix classifies high risk');
    $assert($matrix->assess(5, 5) === ['score' => 25, 'level' => 'critical'], 'risk matrix classifies critical risk');
    try {
        $matrix->assess(0, 5);
        $assert(false, 'risk matrix rejects values outside 1 to 5');
    } catch (InvalidArgumentException) {
        $assert(true, 'risk matrix rejects values outside 1 to 5');
    }
}



$schemaFiles = [
    $root . '/database/migrations/2026_07_26_020000_create_tz_documents_and_evidence_tables.php' => [
        'tz_documents', 'tz_document_versions', 'tz_document_links', 'tz_document_comments',
        'tz_document_approvals', 'tz_evidence_items', 'tz_evidence_signoffs', 'tz_evidence_chain_events',
    ],
    $root . '/database/migrations/2026_07_26_020010_create_tz_assurance_tables.php' => [
        'tz_inspection_templates', 'tz_inspection_template_items', 'tz_inspections', 'tz_inspection_results',
        'tz_assurance_findings', 'tz_corrective_actions', 'tz_risk_register', 'tz_incidents',
    ],
];
foreach ($schemaFiles as $schemaFile => $tables) {
    $assert(is_file($schemaFile), 'required migration exists: ' . basename($schemaFile));
    $source = is_file($schemaFile) ? (string) file_get_contents($schemaFile) : '';
    foreach ($tables as $table) {
        $assert(str_contains($source, "Schema::create('{$table}'"), "migration creates {$table}");
    }
    $assert(substr_count($source, "foreignId('company_id')") >= count($tables), basename($schemaFile) . ' scopes every table to a company');
}



$moduleContracts = [
    $root . '/app/Domains/WorkCore/System/Modules/Documents/Providers/WorkDocumentsServiceProvider.php' => [
        'workcore.document.create', 'workcore.document.version.add', 'workcore.document.link',
        'workcore.document.comment.add', 'workcore.document.approval.request', 'workcore.document.approval.decide',
        'workcore.evidence.register', 'workcore.evidence.signoff', 'workcore.document.search', 'workcore.evidence.search',
    ],
    $root . '/app/Domains/WorkCore/System/Modules/Assurance/Providers/WorkAssuranceServiceProvider.php' => [
        'workcore.inspection_template.create', 'workcore.inspection.start', 'workcore.inspection.result.record',
        'workcore.inspection.complete', 'workcore.finding.create', 'workcore.corrective_action.create',
        'workcore.corrective_action.update', 'workcore.risk.record', 'workcore.incident.record',
        'workcore.inspection.search', 'workcore.finding.search', 'workcore.risk.search', 'workcore.incident.search',
        'workcore.assurance.summary',
    ],
];
foreach ($moduleContracts as $providerFile => $keys) {
    $assert(is_file($providerFile), 'required module provider exists: ' . basename($providerFile));
    $providerSource = is_file($providerFile) ? (string) file_get_contents($providerFile) : '';
    foreach ($keys as $key) {
        $assert(str_contains($providerSource, $key), "provider registers {$key}");
    }
}

$workCoreProvider = $root . '/app/Domains/WorkCore/WorkCoreServiceProvider.php';
$workCoreSource = is_file($workCoreProvider) ? (string) file_get_contents($workCoreProvider) : '';
$assert(str_contains($workCoreSource, "'documents' => WorkDocumentsServiceProvider::class"), 'WorkCore registers Documents module');
$assert(str_contains($workCoreSource, "'assurance' => WorkAssuranceServiceProvider::class"), 'WorkCore registers Assurance module');

$configSource = (string) file_get_contents($root . '/config/workcore.php');
$assert(str_contains($configSource, "'workcore.documents'"), 'WorkCore publishes documents capability');
$assert(str_contains($configSource, "'workcore.assurance'"), 'WorkCore publishes assurance capability');

$repositoryContracts = [
    $root . '/app/Domains/WorkCore/System/Modules/Documents/Repositories/EloquentDocumentRepository.php',
    $root . '/app/Domains/WorkCore/System/Modules/Assurance/Repositories/EloquentAssuranceRepository.php',
];
foreach ($repositoryContracts as $repositoryFile) {
    $assert(is_file($repositoryFile), 'required repository exists: ' . basename($repositoryFile));
    $repositorySource = is_file($repositoryFile) ? (string) file_get_contents($repositoryFile) : '';
    $assert(str_contains($repositorySource, 'assertCompany($companyId)'), basename($repositoryFile) . ' enforces active company');
    $assert(! str_contains($repositorySource, "['company_id']"), basename($repositoryFile) . ' does not trust payload company authority');
}



$summaryController = (string) file_get_contents($root . '/app/Http/Controllers/Titan/WorkCoreSummaryController.php');
foreach (['documents', 'evidence', 'inspections', 'findings', 'risks', 'incidents'] as $key) {
    $assert(str_contains($summaryController, "'{$key}'"), "operations summary exposes {$key} count");
}
foreach (['pending_inspections', 'open_findings', 'overdue_corrective_actions', 'high_critical_risks', 'open_incidents'] as $key) {
    $assert(str_contains($summaryController, "'{$key}'"), "operations summary exposes assurance metric {$key}");
}
$operationsView = (string) file_get_contents($root . '/resources/views/titan/operations.blade.php');
$assert(str_contains($operationsView, 'data-titan-assurance-summary'), 'operations view includes assurance panel');
$operationsJs = (string) file_get_contents($root . '/public/js/titan-operations.js');
$assert(str_contains($operationsJs, 'data-titan-assurance-count'), 'operations JavaScript renders assurance metrics');



$readExecutorFile = $root . '/app/Domains/WorkCore/System/ReadModels/ReadModelExecutor.php';
$assert(is_file($readExecutorFile), 'governed WorkCore read executor exists');
$readExecutorSource = is_file($readExecutorFile) ? (string) file_get_contents($readExecutorFile) : '';
foreach (['PermissionResolverContract', 'EntitlementResolverContract', 'containsTenantAuthority', 'perPage'] as $needle) {
    $assert(str_contains($readExecutorSource, $needle), "read executor enforces {$needle}");
}

$toolRouterSource = (string) file_get_contents($root . '/app/Titan/AI/ToolRouter.php');
$assert(str_contains($toolRouterSource, 'ReadModelRegistry'), 'Titan tool router consumes WorkCore read registry');
$assert(str_contains($toolRouterSource, 'ReadModelExecutor'), 'Titan tool router consumes governed WorkCore read executor');
$assert(str_contains($toolRouterSource, '$this->readModels->has($tool)'), 'Titan tool router executes registered WorkCore read models');

$readControllerFile = $root . '/app/Http/Controllers/Titan/WorkCoreReadController.php';
$assert(is_file($readControllerFile), 'governed WorkCore read API controller exists');
$readControllerSource = is_file($readControllerFile) ? (string) file_get_contents($readControllerFile) : '';
$assert(str_contains($readControllerSource, 'ActiveCompanyContext'), 'WorkCore read API uses host active company context');
$assert(str_contains($readControllerSource, 'containsTenantAuthority'), 'WorkCore read API rejects payload tenant authority');
$routeSource = (string) file_get_contents($root . '/routes/titan.php');
$assert(str_contains($routeSource, "workcore/reads/{read}"), 'Titan API exposes governed WorkCore read route');

printf("WorkCore Assurance standalone tests: %d passed, %d failed\n", count($passes), count($failures));
foreach ($failures as $failure) {
    fwrite(STDERR, "FAIL: {$failure}\n");
}
exit($failures === [] ? 0 : 1);
