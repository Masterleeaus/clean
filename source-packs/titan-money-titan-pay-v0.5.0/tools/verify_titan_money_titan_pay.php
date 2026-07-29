<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$mustExist = [
    'app/Domains/TitanMoney/Providers/TitanMoneyServiceProvider.php',
    'app/Domains/TitanMoney/Database/Migrations/2026_07_26_000010_create_titan_money_core_tables.php',
    'app/Domains/TitanMoney/Database/Migrations/2026_07_26_000020_create_titan_money_payment_audit_tables.php',
    'app/Domains/TitanMoney/Routes/web.php',
    'app/Domains/TitanMoney/Routes/api.php',
    'app/Domains/TitanPay/Providers/TitanPayServiceProvider.php',
];
foreach ($mustExist as $file) {
    if (! is_file($root.'/'.$file)) {
        $errors[] = 'Missing '.$file;
    }
}

$mustNotExist = [
    'app/Domains/Finance',
    'tests/Unit/Finance',
    'app/Domains/TitanMoney/Providers/FinanceServiceProvider.php',
    'app/Domains/TitanMoney/Config/finance.php',
    'tools/verify_titan_finance_titanpay.php',
];
foreach ($mustNotExist as $path) {
    if (file_exists($root.'/'.$path)) {
        $errors[] = 'Retired path remains: '.$path;
    }
}

$providers = file_get_contents($root.'/bootstrap/providers.php');
if (! str_contains($providers, 'TitanMoneyServiceProvider')) {
    $errors[] = 'TitanMoneyServiceProvider is not registered.';
}
if (str_contains($providers, 'FinanceServiceProvider')) {
    $errors[] = 'FinanceServiceProvider remains registered.';
}

$runtimeRoots = ['app', 'bootstrap', 'config', 'database', 'resources', 'routes', 'tests'];
$forbiddenPatterns = [
    'App\\Domains\\Finance',
    "'finance_",
    '"finance_',
    "route('finance.",
    'route("finance.',
    "routeIs('finance.",
    'routeIs("finance.',
    "view('finance::",
    'view("finance::',
    "config('finance.",
    'config("finance.',
    'company.permission:finance.',
    "prefix('finance')",
    'prefix("finance")',
    "name('finance.')",
    'name("finance.")',
    'api/finance/v1',
    'FINANCE_DEFAULT_',
    'FINANCE_INVOICE_',
    'FINANCE_PRIVATE_',
    "'finance_payment_id'",
    '"finance_payment_id"',
];

foreach ($runtimeRoots as $runtimeRoot) {
    $path = $root.'/'.$runtimeRoot;
    if (! is_dir($path)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        $name = $file->getFilename();
        if ($file->getExtension() !== 'php' && ! str_ends_with($name, '.blade.php') && ! str_ends_with($name, '.json')) {
            continue;
        }
        $content = file_get_contents($file->getPathname());
        foreach ($forbiddenPatterns as $forbidden) {
            if (str_contains($content, $forbidden)) {
                $errors[] = 'Retired Finance runtime identifier in '.str_replace($root.'/', '', $file->getPathname()).': '.$forbidden;
            }
        }
    }
}

$titanMoneyRoutes = file_get_contents($root.'/app/Domains/TitanMoney/Routes/web.php');
foreach (["prefix('titan-money')", "name('titanmoney.')", 'company.permission:titanmoney.read'] as $needle) {
    if (! str_contains($titanMoneyRoutes, $needle)) {
        $errors[] = 'Missing Titan Money route invariant: '.$needle;
    }
}

$titanMoneyApi = file_get_contents($root.'/app/Domains/TitanMoney/Routes/api.php');
if (! str_contains($titanMoneyApi, "prefix('api/titan-money/v1')")) {
    $errors[] = 'Titan Money API prefix is incorrect.';
}

$provider = file_get_contents($root.'/app/Domains/TitanMoney/Providers/TitanMoneyServiceProvider.php');
foreach (["Config/titanmoney.php", "'titanmoney'", "'titanmoney'"] as $needle) {
    if (! str_contains($provider, $needle)) {
        $errors[] = 'Missing Titan Money provider invariant: '.$needle;
    }
}

$titanMoneyMigration = file_get_contents($root.'/app/Domains/TitanMoney/Database/Migrations/2026_07_26_000010_create_titan_money_core_tables.php');
foreach (['titan_money_customers', 'titan_money_invoices', 'titan_money_invoice_lines', 'titan_money_recurring_invoices'] as $needle) {
    if (! str_contains($titanMoneyMigration, $needle)) {
        $errors[] = 'Missing Titan Money table: '.$needle;
    }
}

$titanPayMigration = file_get_contents($root.'/app/Domains/TitanPay/Database/Migrations/2026_07_26_000100_create_titanpay_tables.php');
foreach (['titan_money_invoices', 'titan_money_payments', 'titan_money_payment_id'] as $needle) {
    if (! str_contains($titanPayMigration, $needle)) {
        $errors[] = 'Titan Pay migration is not linked to Titan Money: '.$needle;
    }
}

$webhookRoutes = file_get_contents($root.'/app/Domains/TitanPay/Routes/api.php');
if (! str_contains($webhookRoutes, 'webhooks/{provider}/{connection}')) {
    $errors[] = 'Webhook route is not gateway-connection specific.';
}

$reconciliation = file_get_contents($root.'/app/Domains/TitanPay/Services/PaymentReconciliationService.php');
foreach (["status !== 'completed'", 'amountMinor !== $session->amount_minor', 'currencyCode) !== strtoupper($session->currency_code)'] as $needle) {
    if (! str_contains($reconciliation, $needle)) {
        $errors[] = 'Missing payment reconciliation invariant: '.$needle;
    }
}

$publicRoutes = file_get_contents($root.'/app/Domains/TitanPay/Routes/web.php');
if (preg_match('/Route::get\([^\n]*(success|complete|paid)/i', $publicRoutes)) {
    $errors[] = 'A public GET route appears to confirm payment.';
}

if ($errors !== []) {
    fwrite(STDERR, "FAIL\n- ".implode("\n- ", array_values(array_unique($errors)))."\n");
    exit(1);
}

echo "GREEN: Titan Money and Titan Pay structural invariants pass.\n";
