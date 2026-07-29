<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];
$read = static fn (string $path): string => is_file($root.'/'.$path) ? (string) file_get_contents($root.'/'.$path) : '';
$has = static function (string $path, string $needle, string $message) use (&$failures, $read): void {
    if (! str_contains($read($path), $needle)) $failures[] = $message.' ('.$path.')';
};

$has('app/Domains/TitanPay/Adapters/PayIdGatewayAdapter.php', 'SecretResolver', 'PayID instructions must resolve protected payment details');
$has('app/Domains/TitanPay/Adapters/PayIdGatewayAdapter.php', 'isAvailable', 'PayID must only be offered when it is configured');
$has('app/Domains/TitanPay/Adapters/BankTransferGatewayAdapter.php', 'SecretResolver', 'Bank instructions must resolve protected account details');
$has('app/Domains/TitanPay/Adapters/BankTransferGatewayAdapter.php', 'isAvailable', 'Bank transfer must only be offered when it is configured');
$has('app/Domains/TitanPay/Config/titanpay.php', "'TITANPAY_PAYID'", 'PayID secrets must survive config caching');
$has('app/Domains/TitanPay/Config/titanpay.php', "'TITANPAY_BANK'", 'Bank secrets must survive config caching');
$has('.env.example', 'TITANPAY_PAYID_VALUE=', 'Sample environment must document PayID configuration');
$has('.env.example', 'TITANPAY_BANK_BSB=', 'Sample environment must document bank transfer configuration');
$has('app/Domains/TitanMoney/Http/Controllers/CustomerController.php', 'communication_preferences', 'Customer channel consent must be editable');
$has('app/Domains/TitanMoney/Resources/views/customers/form.blade.php', 'communication_channels[]', 'Customer form must expose channel preferences');
$has('app/Domains/TitanPay/Services/CollectionService.php', 'lockForUpdate()', 'Collection link creation must lock the invoice against duplicate active links');
$has('app/Domains/TitanPay/Services/CollectionService.php', 'CollectionStatus::Cancelled', 'Superseded or undecryptable collection links must be cancelled');


$payid = $read('app/Domains/TitanPay/Adapters/PayIdGatewayAdapter.php');
$bank = $read('app/Domains/TitanPay/Adapters/BankTransferGatewayAdapter.php');
$controller = $read('app/Domains/TitanPay/Http/Controllers/GatewayConnectionController.php');
if (str_contains($payid, 'configuration_json')) $failures[] = 'PayID must not fall back to plaintext connection configuration.';
if (str_contains($bank, 'configuration_json')) $failures[] = 'Bank transfer must not fall back to plaintext connection configuration.';
if (str_contains($controller, "'configuration_json'=>'nullable|array'")) $failures[] = 'Gateway settings must not accept arbitrary plaintext secret configuration.';

if ($failures !== []) {
    fwrite(STDERR, "Payment/channel configuration failures:\n- ".implode("\n- ", $failures)."\n");
    exit(1);
}

echo "Payment/channel configuration checks passed.\n";
