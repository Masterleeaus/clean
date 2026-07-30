<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
$checks = 0;
$expect = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (! $condition) {
        throw new RuntimeException($message);
    }
};

$required = [
    'docker-compose.yml',
    'docker/php/Dockerfile',
    'docker/php/entrypoint.sh',
    'bin/titan-preflight',
    'bin/titan-verify-offline',
    'bin/titan-build',
    'bin/titan-verify-connected',
    'tools/titan_migration_order.php',
    'tools/titan_route_provider_scan.php',
    '.env.testing',
    '.github/workflows/titan-verify.yml',
    'DEPLOYMENT.md',
];

foreach ($required as $relative) {
    $expect(is_file($root.'/'.$relative), "Missing deployment artifact: {$relative}");
}

$env = (string) file_get_contents($root.'/.env.example');
$expect((bool) preg_match('/^APP_KEY=\s*$/m', $env), '.env.example must not contain a reusable application key.');
$expect(str_contains($env, 'DB_CONNECTION=sqlite'), '.env.example must default to SQLite for local verification.');
$expect(str_contains($env, 'FILESYSTEM_DISK=local'), '.env.example must default to private local storage.');
$expect(str_contains($env, 'QUEUE_CONNECTION=database'), '.env.example must use the durable database queue.');
$expect(str_contains($env, 'BROADCAST_CONNECTION=log'), '.env.example must fail safely without provider credentials.');

$testing = is_file($root.'/.env.testing') ? (string) file_get_contents($root.'/.env.testing') : '';
foreach (['.env.testing', '.env.verification'] as $envFile) {
    $contents = is_file($root.'/'.$envFile) ? (string) file_get_contents($root.'/'.$envFile) : '';
    preg_match('/^APP_KEY=base64:(.+)$/m', $contents, $keyMatch);
    $decoded = isset($keyMatch[1]) ? base64_decode($keyMatch[1], true) : false;
    $expect(is_string($decoded) && strlen($decoded) === 32, "{$envFile} APP_KEY must decode to exactly 32 bytes.");
}

foreach (['APP_ENV=testing', 'DB_CONNECTION=sqlite', 'DB_DATABASE=:memory:', 'CACHE_STORE=array', 'QUEUE_CONNECTION=sync', 'SESSION_DRIVER=array'] as $line) {
    $expect(str_contains($testing, $line), ".env.testing missing {$line}");
}

$compose = is_file($root.'/docker-compose.yml') ? (string) file_get_contents($root.'/docker-compose.yml') : '';
foreach (['app:', 'queue:', 'scheduler:', 'postgres:', 'node:'] as $service) {
    $expect(str_contains($compose, $service), "docker-compose.yml missing {$service}");
}
$expect(str_contains($compose, 'healthcheck:'), 'PostgreSQL healthcheck is required.');
$expect(substr_count($compose, 'APP_KEY:') >= 2, 'APP_KEY must propagate to app and inherited worker services.');

$dockerfile = is_file($root.'/docker/php/Dockerfile') ? (string) file_get_contents($root.'/docker/php/Dockerfile') : '';
foreach (['pdo_pgsql', 'pdo_sqlite', 'mbstring', 'intl', 'zip', 'pcntl', 'curl', 'dom', 'simplexml', 'xmlwriter', 'COPY --from=composer:2', 'node:22', 'node_modules/npm'] as $needle) {
    $expect(str_contains($dockerfile, $needle), "PHP Dockerfile missing {$needle}");
}

$verify = is_file($root.'/bin/titan-verify-connected') ? (string) file_get_contents($root.'/bin/titan-verify-connected') : '';
$build = is_file($root.'/bin/titan-build') ? (string) file_get_contents($root.'/bin/titan-build') : '';
$preflight = is_file($root.'/bin/titan-preflight') ? (string) file_get_contents($root.'/bin/titan-preflight') : '';
$expect(str_contains($preflight, 'pdo_sqlite'), 'Preflight must require the SQLite PDO driver.');
$expect(str_contains($preflight, 'pdo_pgsql'), 'Preflight must support PostgreSQL PDO validation.');
$expect(strpos($verify, 'cp .env.verification .env') < strpos($verify, 'composer install'), 'Connected verifier must create .env before Composer package discovery.');
$expect(strpos($build, 'cp .env.example .env') < strpos($build, 'composer install'), 'Build script must create .env before Composer package discovery.');
$expect(str_contains($verify, 'titan-verify-offline'), 'Connected verifier must run the complete offline regression suite.');

foreach (['composer validate --strict', 'php artisan about', 'php artisan route:list', 'php artisan migrate:fresh', 'php artisan migrate:rollback', 'php artisan test', 'npm run build', 'queue:work', 'schedule:list'] as $needle) {
    $expect(str_contains($verify, $needle), "Connected verifier missing {$needle}");
}

$workflow = is_file($root.'/.github/workflows/titan-verify.yml') ? (string) file_get_contents($root.'/.github/workflows/titan-verify.yml') : '';
$expect(is_file($root.'/package-lock.json') || ! str_contains($workflow, 'cache-dependency-path: package-lock.json'), 'CI must not require an absent npm lockfile for caching.');
$expect(strpos($workflow, 'cp .env.verification .env') < strpos($workflow, 'composer install'), 'CI must create .env before Composer package discovery.');
foreach (['postgres:', 'shivammathur/setup-php', 'actions/setup-node', 'composer install', 'npm install', 'titan-verify-connected'] as $needle) {
    $expect(str_contains($workflow, $needle), "CI workflow missing {$needle}");
}

$migrationOutput = []; $migrationCode = 0;
exec('php '.escapeshellarg($root.'/tools/titan_migration_order.php'), $migrationOutput, $migrationCode);
$expect($migrationCode === 0, 'Migration order tool must exit successfully.');
$expect(str_contains(implode("\n", $migrationOutput), '815 references'), 'Migration order tool must include inferred constrained() references.');

$routeOutput = []; $routeCode = 0;
exec('php '.escapeshellarg($root.'/tools/titan_route_provider_scan.php'), $routeOutput, $routeCode);
$expect($routeCode === 0, 'Route/provider tool must exit successfully.');
$expect(str_contains(implode("\n", $routeOutput), '0 unresolved'), 'Route/provider tool must report zero unresolved classes.');

fwrite(STDOUT, "Deployment contracts: {$checks} checks passed.\n");
