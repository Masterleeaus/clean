<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$source = $root.'/source-packs/titan-money-titan-pay-v0.5.0';
$errors = [];

$required = [
    'TITAN_MONEY_TITAN_PAY_CHAT_UPGRADE_PLAN.md',
    'TITAN_MONEY_TITAN_PAY_BRANCH_STATUS.md',
    'SOURCE_PROVENANCE_TITAN_MONEY_TITAN_PAY.md',
    'SOURCE_IMPORT_TITAN_MONEY_TITAN_PAY.json',
    'AGENTS.md',
    'docs/integration/titan-money-titan-pay/README.md',
    'tools/titan-money-pay-inventory.php',
];

foreach ($required as $path) {
    if (! is_file($root.'/'.$path)) {
        $errors[] = "Missing branch control file: {$path}";
    }
}

if (! is_dir($source)) {
    $errors[] = 'Staged source directory is missing.';
} else {
    $sourceRequirements = [
        'README_TITAN_MONEY_TITAN_PAY.md',
        'app/Domains/TitanMoney/Providers/TitanMoneyServiceProvider.php',
        'app/Domains/TitanPay/Providers/TitanPayServiceProvider.php',
        'app/Domains/TitanZero/Workflows/CompleteJobAndInvoiceWorkflow.php',
        'docs/CHAT_FIRST_JOB_TO_PAYMENT.md',
        'tools/verify_titan_money_titan_pay.php',
        'STAGED_SOURCE_NOTICE.md',
    ];

    foreach ($sourceRequirements as $path) {
        if (! is_file($source.'/'.$path)) {
            $errors[] = "Missing staged source file: {$path}";
        }
    }

    foreach (['.env', 'vendor', 'node_modules', 'public/build'] as $forbidden) {
        if (file_exists($source.'/'.$forbidden)) {
            $errors[] = "Forbidden staged path exists: {$forbidden}";
        }
    }

    $count = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file instanceof SplFileInfo && $file->isFile()) {
            $count++;
        }
    }

    // Original archive contains 404 files; staging adds one notice file.
    if ($count !== 405) {
        $errors[] = "Unexpected staged file count: {$count}; expected 405.";
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, "ERROR: {$error}\n");
    }
    exit(1);
}

echo "Titan Money + Titan Pay branch preparation verified.\n";
exit(0);
