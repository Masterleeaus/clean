<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];

$expectContains = static function (string $path, string $needle, string $message) use (&$failures, $root): void {
    $full = $root.'/'.$path;
    $contents = is_file($full) ? file_get_contents($full) : false;
    if ($contents === false || ! str_contains($contents, $needle)) {
        $failures[] = $message." ({$path})";
    }
};

$expectContains('public/js/chat-app.js', 'function renderOperationalMetadata', 'Chat UI must render operational metadata');
$expectContains('public/js/chat-app.js', 'metadata.qr_url', 'Chat UI must render Titan Pay QR URLs');
$expectContains('public/js/chat-app.js', 'metadata.payment_url', 'Chat UI must render Titan Pay payment links');
$expectContains('app/Domains/TitanMoney/Http/Controllers/Api/ChannelReceiptController.php', 'hash_hmac', 'Receipt callbacks must be HMAC authenticated');
$expectContains('app/Domains/TitanMoney/Http/Controllers/Api/ChannelReceiptController.php', 'hash_equals', 'Receipt callback signatures must use constant-time comparison');
$expectContains('app/Domains/TitanMoney/Http/Controllers/Api/ChannelReceiptController.php', '$delivery->delivered_at !== null', 'Out-of-order receipts must not downgrade delivered messages');
$expectContains('app/Domains/TitanMoney/Http/Controllers/Api/ChannelReceiptController.php', '$delivery->failed_at !== null', 'Out-of-order receipts must not reopen terminal failures');
$expectContains('app/Domains/TitanMoney/Routes/api.php', 'channel-receipts', 'Titan Money API must expose a receipt callback');
$expectContains('config/titan_channels.php', "'receipt_secret'", 'Titan Channels config must define a receipt secret');
$expectContains('.env.example', 'TITAN_CHANNELS_RECEIPT_SECRET=', 'Sample environment must expose the receipt secret');
$expectContains('app/Domains/TitanPay/Database/Migrations/2026_07_27_000300_add_reusable_tokens_and_qr_constraints.php', 'havingRaw', 'QR migration must deduplicate legacy rows before adding uniqueness');
$expectContains('app/Domains/TitanMoney/Services/ChannelDeliveryService.php', "'delivery_exception'", 'Missing customer contact routes must create an internal delivery exception');
$expectContains('app/Domains/TitanMoney/Services/Transports/WebhookChannelTransport.php', 'Pay securely:', 'SMS and WhatsApp bodies must carry the invoice link even when a provider ignores structured fields');

$expectContains('app/Domains/TitanMoney/Services/ChannelDeliveryService.php', 'payment_link_preparation_failed', 'Payment-link failures must create a staff exception instead of silent linkless delivery');

if ($failures !== []) {
    fwrite(STDERR, "Chat/channel experience regression failures:\n- ".implode("\n- ", $failures)."\n");
    exit(1);
}

echo "Chat/channel experience regression checks passed.\n";
