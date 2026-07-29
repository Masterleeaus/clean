<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];
$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (! $condition) $failures[] = $message;
};
$contains = static function (string $path, string $needle) use ($root): bool {
    $file = $root.'/'.$path;
    return is_file($file) && str_contains((string) file_get_contents($file), $needle);
};

$required = [
    'app/Domains/TitanZero/WorkCore/Contracts/WorkCoreJobGateway.php',
    'app/Domains/TitanZero/WorkCore/NativeWorkCoreJobGateway.php',
    'app/Domains/TitanZero/Workflows/CompleteJobAndInvoiceWorkflow.php',
    'app/Domains/TitanZero/Chat/TitanOperationalToolRouter.php',
    'app/Domains/TitanPay/Services/QrCodeService.php',
    'app/Domains/TitanPay/Http/Controllers/QrCodeController.php',
    'app/Domains/TitanMoney/Services/ChannelTransportRegistry.php',
    'app/Domains/TitanMoney/Services/Transports/CustomerAppChannelTransport.php',
    'app/Domains/TitanMoney/Services/Transports/WebhookChannelTransport.php',
];
foreach ($required as $file) $assert(is_file($root.'/'.$file), "Missing {$file}");

$assert($contains('app/Http/Controllers/ChatController.php', 'TitanOperationalToolRouter'), 'ChatController is not wired to operational tools.');
$assert($contains('app/Domains/TitanZero/WorkCore/NativeWorkCoreJobGateway.php', 'workcore.work_order.change_status'), 'WorkCore completion action is not used.');
$assert($contains('app/Domains/TitanZero/WorkCore/NativeWorkCoreJobGateway.php', 'confirmationId:'), 'Chat completion does not carry explicit WorkCore confirmation.');
$assert($contains('app/Domains/TitanZero/WorkCore/NativeWorkCoreJobGateway.php', 'App\\\\Domains\\\\WorkCore\\\\System'), 'Bridge does not support native WorkCore domain placement.');
$assert($contains('app/Domains/TitanZero/WorkCore/NativeWorkCoreJobGateway.php', 'App\\\\Extensions\\\\WorkCore\\\\System'), 'Bridge does not support the current WorkCore package placement.');
$assert($contains('app/Domains/TitanZero/WorkCore/NativeWorkCoreJobGateway.php', "Schema::hasTable('tz_premises')"), 'Optional WorkCore premises support is not schema-safe.');
$assert($contains('app/Domains/TitanZero/Workflows/CompleteJobAndInvoiceWorkflow.php', "workcore_prices_tax_inclusive"), 'WorkCore GST price basis is not configurable.');
$assert(! $contains('app/Domains/TitanZero/WorkCore/NativeWorkCoreJobGateway.php', "->update(['status' => 'completed'"), 'WorkCore is updated directly.');
$assert($contains('app/Domains/TitanPay/Routes/web.php', 'qr.svg'), 'QR route is missing.');
$assert(is_file($root.'/resources/python/titanpay_qr/generate.py'), 'Bundled QR renderer is missing.');
$assert($contains('app/Domains/TitanPay/Config/titanpay.php', "'payid', 'bank_transfer', 'cash', 'paypal'"), 'Default payment order is wrong.');
$assert(! $contains('app/Domains/TitanMoney/Services/ChannelDeliveryDispatcher.php', "'handed_off'"), 'Dispatcher still records unverified handoff as success.');
$assert($contains('app/Models/Message.php', 'metadata_json'), 'Chat messages cannot store operational result metadata.');
$assert($contains('app/Domains/TitanMoney/Services/InvoiceAutomationAgent.php', 'runForEvent'), 'Invoice agent cannot process a single chat event.');
$assert($contains('app/Domains/TitanMoney/Services/ChannelDeliveryService.php', 'CustomerChannelPreferenceResolver'), 'Delivery does not use customer channel preferences.');
$assert($contains('app/Domains/TitanZero/Workflows/CompleteJobAndInvoiceWorkflow.php', "'job_completed_invoice_failed'"), 'Partial failure after job completion is not reported truthfully.');
$assert($contains('app/Domains/TitanZero/Workflows/CompleteJobAndInvoiceWorkflow.php', "=== 'failed'"), 'Agent failures are not distinguished from disabled automation.');
$assert($contains('app/Domains/TitanMoney/Services/InvoiceAutomationAgent.php', 'correlationId: $this->actorContext->correlationId()'), 'Chat correlation is not preserved through the Invoice Agent run.');
$assert($contains('app/Domains/TitanZero/Workflows/CompleteJobAndInvoiceWorkflow.php', "'invoice_issued_collection_failed'"), 'Partial failure after invoice issue is not reported truthfully.');

if ($failures !== []) {
    fwrite(STDERR, "CHAT WORKFLOW WIRING FAILURES:\n- ".implode("\n- ", $failures)."\n");
    exit(1);
}

echo "chat-workflow-wiring: ok\n";
