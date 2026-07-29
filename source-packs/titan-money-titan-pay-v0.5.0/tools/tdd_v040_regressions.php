<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];

function check(bool $condition, string $message): void
{
    global $failures;
    if (! $condition) {
        $failures[] = $message;
    }
}

require_once $root.'/app/Domains/TitanMoney/Support/DecimalParser.php';
require_once $root.'/app/Domains/TitanMoney/Services/InvoiceCalculator.php';
$calculator = new App\Domains\TitanMoney\Services\InvoiceCalculator();
$discountResult = $calculator->calculate([
    ['description'=>'A','quantity'=>'1.0000','unit_price_minor'=>100,'discount_minor'=>200,'tax_rate_basis_points'=>0],
    ['description'=>'B','quantity'=>'1.0000','unit_price_minor'=>100,'discount_minor'=>0,'tax_rate_basis_points'=>0],
]);
check($discountResult['discount_minor'] === 100, 'Line discount must be capped at that line gross.');
check($discountResult['total_minor'] === 100, 'Excess discount on one line must not erase another line.');

$composer = json_decode(file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$lock = json_decode(file_get_contents($root.'/composer.lock'), true, 512, JSON_THROW_ON_ERROR);
$lockedPackages = array_column(array_merge($lock['packages'] ?? [], $lock['packages-dev'] ?? []), 'name');
foreach (array_keys($composer['require'] ?? []) as $package) {
    if ($package === 'php' || str_starts_with($package, 'ext-')) {
        continue;
    }
    check(in_array($package, $lockedPackages, true), "Required package {$package} must exist in composer.lock.");
}

$rootRoutes = file_get_contents($root.'/routes/web.php');
check(! str_contains($rootRoutes, "Route::get('catch-clear'"), 'Public cache-clear GET route must not exist.');
check(! str_contains($rootRoutes, "Route::get('storage-link'"), 'Public storage-link GET route must not exist.');
check(str_contains($rootRoutes, "Route::post('/login'") && str_contains($rootRoutes, "throttle:6,1"), 'Login must be rate limited.');

$apiRoutes = file_get_contents($root.'/app/Domains/TitanPay/Routes/api.php');
check(str_contains($apiRoutes, "company.permission:titanpay.session.create"), 'Titan Pay API session creation must require the create permission.');

check(is_file($root.'/app/Domains/TitanPay/Policies/CollectionEligibility.php'), 'Collection eligibility policy must exist.');
if (is_file($root.'/app/Domains/TitanPay/Policies/CollectionEligibility.php')) {
    require_once $root.'/app/Domains/TitanMoney/Enums/InvoiceStatus.php';
    require_once $root.'/app/Domains/TitanPay/Enums/CollectionStatus.php';
    require_once $root.'/app/Domains/TitanPay/Policies/CollectionEligibility.php';
    $policy = new App\Domains\TitanPay\Policies\CollectionEligibility();
    check($policy->canCreateForInvoice(App\Domains\TitanMoney\Enums\InvoiceStatus::Issued), 'Issued invoices must be collectible.');
    check(! $policy->canCreateForInvoice(App\Domains\TitanMoney\Enums\InvoiceStatus::Draft), 'Draft invoices must not be collectible.');
    check(! $policy->canInitiate(App\Domains\TitanPay\Enums\CollectionStatus::Completed), 'Completed sessions must not be re-initiated.');
    check(! $policy->canSubmitClaim(App\Domains\TitanPay\Enums\CollectionStatus::Expired), 'Expired sessions must reject claims.');
}

check(is_file($root.'/app/Domains/TitanPay/Support/PayPalWebhookParser.php'), 'PayPal webhook parser must exist.');
if (is_file($root.'/app/Domains/TitanPay/Support/PayPalWebhookParser.php')) {
    require_once $root.'/app/Domains/TitanPay/Support/PayPalWebhookParser.php';
    $parsed = (new App\Domains\TitanPay\Support\PayPalWebhookParser())->parse([
        'id'=>'WH-1',
        'event_type'=>'PAYMENT.CAPTURE.COMPLETED',
        'resource'=>[
            'id'=>'CAPTURE-1',
            'amount'=>['value'=>'12.34','currency_code'=>'AUD'],
            'supplementary_data'=>['related_ids'=>['order_id'=>'ORDER-1']],
        ],
    ]);
    check($parsed['session_reference'] === 'ORDER-1', 'PayPal completed-capture webhook must resolve the order ID.');
    check($parsed['amount_minor'] === 1234, 'PayPal amount must parse to exact minor units.');
}

$claimService = file_get_contents($root.'/app/Domains/TitanPay/Services/PaymentClaimService.php');
check(substr_count($claimService, 'lockForUpdate()') >= 2, 'Claim review must lock both claim and session.');
check(str_contains($claimService, 'refresh()') || str_contains($claimService, 'fresh('), 'Claim approval must reload invoice state after allocation.');

$allocation = file_get_contents($root.'/app/Domains/TitanMoney/Services/PaymentAllocationService.php');
check(str_contains($allocation, 'InvoiceStatus::Issued') && str_contains($allocation, 'InvoiceStatus::Overdue'), 'Allocations must explicitly limit eligible invoice states.');

$reconciliation = file_get_contents($root.'/app/Domains/TitanPay/Services/PaymentReconciliationService.php');
check(str_contains($reconciliation, "'invalid:'"), 'Invalid webhook events must use a payload-derived identity and not reserve a valid provider event ID.');

$config = file_get_contents($root.'/app/Domains/TitanPay/Config/titanpay.php');
check(str_contains($config, "'secrets'"), 'Titan Pay config must contain config-cache-safe secret mappings.');

$bootstrap = file_get_contents($root.'/bootstrap/app.php');
check(str_contains($bootstrap, '$middleware->web('), 'Last-seen middleware must run in the web middleware group.');
check(! str_contains($bootstrap, '$middleware->append(UpdateUserLastSeen::class)'), 'Last-seen middleware must not be global.');

$outbox = file_get_contents($root.'/app/Domains/TitanMoney/Services/TitanMoneyOutboxPublisher.php');
check(str_contains($outbox, 'receivablesFollowUpEnabled'), 'Scheduled non-pre-due reminders must be suppressed when the Receivables Agent owns follow-up.');

$pdf = file_get_contents($root.'/app/Domains/TitanMoney/Resources/views/invoices/pdf.blade.php');
check(str_contains($pdf, "gst_registered") && str_contains($pdf, "TAX INVOICE") && str_contains($pdf, "INVOICE"), 'Invoice heading must depend on GST registration.');


$importer = file_get_contents($root.'/app/Domains/TitanMoney/Console/Commands/ImportInvoixProCommand.php');
check(! str_contains($importer, 'InvoiceStatus::Paid'), 'Legacy invoice status must never become a verified paid invoice.');
check(str_contains($importer, 'legacy-invoice-status-'), 'Legacy paid labels must create reconciliation claims when no verified payment exists.');

$claimService = file_get_contents($root.'/app/Domains/TitanPay/Services/PaymentClaimService.php');
check(str_contains($claimService, 'remainingClaimableMinor'), 'Manual payment claims must be capped by current invoice balance and outstanding submitted claims.');
check(str_contains($claimService, 'sanitizeOriginalName'), 'Payment evidence filenames must be normalised before persistence/download.');


$invoiceAgent = file_get_contents($root.'/app/Domains/TitanMoney/Services/InvoiceAutomationAgent.php');
check(! str_contains($invoiceAgent, '[\'error\'=>$error->getMessage()]'), 'Agent result payloads must not expose raw internal exception messages.');
$agentApi = file_get_contents($root.'/app/Domains/TitanMoney/Http/Controllers/Api/AgentRunApiController.php');
check(str_contains($agentApi, "->only(['id','agent_key','status'"), 'Agent run API must return an explicit safe field allowlist.');
$runService = file_get_contents($root.'/app/Domains/TitanMoney/Services/AgentRunService.php');
check(str_contains($runService, 'Log::error'), 'Agent failures must retain detailed diagnostics in server logs.');
check(! str_contains($runService, "'error_message'=>mb_substr(\$error->getMessage()"), 'Agent run records must not expose raw exception messages to normal UI/API serialization.');


$helpers = file_get_contents($root.'/app/Helpers/helpers.php');
check(str_contains($helpers, "function formatMoneyMinor"), 'All finance/payment views must use one currency-aware minor-unit formatter.');
$moneyViews = array_merge(
    glob($root.'/app/Domains/TitanMoney/Resources/views/**/*.blade.php') ?: [],
    glob($root.'/app/Domains/TitanMoney/Resources/views/*.blade.php') ?: [],
    glob($root.'/app/Domains/TitanPay/Resources/views/**/*.blade.php') ?: [],
    glob($root.'/app/Domains/TitanPay/Resources/views/*.blade.php') ?: [],
);
foreach (array_unique($moneyViews) as $moneyView) {
    $content = file_get_contents($moneyView);
    check(! str_contains($content, '${{ number_format('), 'Finance/payment views must not hard-code a dollar sign: '.str_replace($root.'/', '', $moneyView));
}
$followUp = file_get_contents($root.'/app/Domains/TitanMoney/Services/FollowUpContentFactory.php');
check(str_contains($followUp, 'new Money('), 'Automated invoice follow-ups must format the invoice currency rather than concatenate a generic code and decimal.');
$analytics = file_get_contents($root.'/app/Domains/TitanMoney/Services/TitanMoneyAnalyticsService.php');
check(str_contains($analytics, "'currencies'"), 'Titan Money dashboard totals must be grouped by currency rather than summed across currencies.');
$titanPayDashboard = file_get_contents($root.'/app/Domains/TitanPay/Http/Controllers/TitanPayDashboardController.php');
check(str_contains($titanPayDashboard, "groupBy('currency_code')"), 'Titan Pay verified collections must be grouped by currency rather than summed across currencies.');

if ($failures !== []) {
    fwrite(STDERR, "RED: v0.4 regression checks failed as expected\n- ".implode("\n- ", $failures)."\n");
    exit(1);
}

fwrite(STDOUT, "GREEN: v0.4 regression checks passed.\n");
