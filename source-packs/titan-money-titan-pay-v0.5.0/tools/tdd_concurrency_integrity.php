<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];
$read = static fn (string $path): string => (string) file_get_contents($root.'/'.$path);
$has = static function (string $needle, string $haystack, string $message) use (&$failures): void {
    if (! str_contains($haystack, $needle)) $failures[] = $message;
};
$not = static function (string $needle, string $haystack, string $message) use (&$failures): void {
    if (str_contains($haystack, $needle)) $failures[] = $message;
};

$invoiceService = $read('app/Domains/TitanMoney/Services/InvoiceService.php');
$has('lockForUpdate()', $invoiceService, 'Invoice issue/update/void transitions must lock the current invoice row.');
$has('InvoiceStatus::Issued', $invoiceService, 'Invoice issue must support idempotent recovery after a partial controller failure.');
$has('return $locked->fresh', $invoiceService, 'Idempotent invoice issue must return the locked current record.');

$approval = $read('app/Domains/TitanMoney/Http/Controllers/AgentApprovalController.php');
$has('DB::transaction', $approval, 'Agent approval decisions must be transactional.');
$has('lockForUpdate()', $approval, 'Agent approval decisions must lock the approval request against approve/reject races.');

$reminders = $read('app/Domains/TitanMoney/Services/InvoiceReminderService.php');
$has("withoutGlobalScope('company')", $reminders, 'Cross-company reminder dispatch must ignore stale console tenant context.');
$has('lockForUpdate()', $reminders, 'Reminder queue transitions must lock and recheck pending status.');

$dispatcher = $read('app/Domains/TitanMoney/Services/ChannelDeliveryDispatcher.php');
$has("'status'=>'processing'", $dispatcher, 'Delivery workers must atomically claim a queued delivery before external send.');
$has("where('status','processing')", $dispatcher, 'Delivery workers must recover stale processing leases.');

$reconciliation = $read('app/Domains/TitanPay/Services/PaymentReconciliationService.php');
$has("'processed_unallocated'", $reconciliation, 'Verified duplicate/overpayments must be retained as unallocated money, not poison webhook retries.');
$has('balance_due_minor > 0', $reconciliation, 'Reconciliation must not allocate to an invoice with no outstanding balance.');

$agent = $read('app/Domains/TitanPay/Services/TitanPayReconciliationAgent.php');
$has("'processed_unallocated'", $agent, 'The reconciliation agent must surface verified unallocated payments for review.');

$api = $read('app/Domains/TitanMoney/Http/Controllers/Api/InvoiceApiController.php');
$has('max(1, min(', $api, 'API pagination must reject/coerce negative and zero page sizes.');

$recurring = $read('app/Domains/TitanMoney/Http/Controllers/RecurringInvoiceController.php');
$has("'next_generation_date'=>'required|date|after_or_equal:start_date'", $recurring, 'Recurring next generation date must not precede its start date.');


$actorContext = $read('app/Domains/Company/Support/ActorContext.php');
$has('Str::isUlid', $actorContext, 'Actor context must reject malformed correlation and causation IDs before ULID database writes.');
$has('normaliseContextValue', $actorContext, 'Actor context must bound untrusted device and conversation header lengths.');

$bridge = $read('app/Domains/TitanMoney/AI/Extension/TitanMoneyAIAgentBridge.php');
$has('interface_exists', $bridge, 'Optional AI workflow integration must verify its action contract before registration.');
$has('AIAgentWorkflowRun', $bridge, 'Optional AI workflow integration must verify all external model dependencies before registration.');

$outbox = $read('app/Domains/TitanMoney/Services/TitanMoneyOutboxPublisher.php');
$has('DB::transaction', $outbox, 'Outbox publication must be transactional per event.');
$has('lockForUpdate()', $outbox, 'Concurrent outbox workers must lock and recheck each event before publishing.');
$has('if ($event === null || $event->published_at !== null)', $outbox, 'A waiting outbox worker must skip an event already published by another worker.');

$lifecycle = $read('app/Domains/TitanMoney/Services/InvoiceLifecycleService.php');
$has('balance_due_minor <= 0', $lifecycle, 'Overdue lifecycle must reject invoices with no outstanding balance.');
$has('due_date === null', $lifecycle, 'Overdue lifecycle must reject invoices without a due date.');
$has('isToday()', $lifecycle, 'Invoices due today must not be marked overdue.');


$documents = $read('app/Domains/TitanMoney/Services/InvoiceDocumentService.php');
$has('hash_equals', $documents, 'Issued invoice downloads must verify the immutable document hash before serving.');
$has('if ($invoice->document_path && $invoice->document_hash)', $documents, 'Repeated snapshot requests must return the existing immutable document instead of overwriting it.');
$has('Invoice document snapshot is missing', $documents, 'Missing immutable invoice files must fail closed rather than silently rerender with a newer template.');
$template = $read('app/Domains/TitanMoney/Resources/views/invoices/pdf.blade.php');
$not('pending snapshot', $template, 'Issued documents must not permanently display a misleading pending-hash footer.');

$knowledge = '';
foreach (glob($root.'/app/Domains/TitanPay/Knowledge/**/*.md') ?: [] as $file) {
    $knowledge .= file_get_contents($file)."\n";
}
foreach (glob($root.'/app/Domains/TitanPay/Knowledge/*.md') ?: [] as $file) {
    $knowledge .= file_get_contents($file)."\n";
}
$not('`titanpay.update`', $knowledge, 'Titan Pay knowledge must not reference retired titanpay.update permission.');
$not('`titanpay.view`', $knowledge, 'Titan Pay knowledge must not reference retired titanpay.view permission.');
$not('`titanpay.create`', $knowledge, 'Titan Pay knowledge must not reference retired titanpay.create permission.');
$not('`titanpay.agent.use`', $knowledge, 'Titan Pay knowledge must not reference retired titanpay.agent.use permission.');


$completionPath = $root.'/app/Domains/TitanPay/Services/CollectionSessionCompletionService.php';
if (! is_file($completionPath)) {
    $failures[] = 'Collection session completion must be centralised so fully paid invoices cancel other active payment links.';
} else {
    $completion = (string) file_get_contents($completionPath);
    $has("where('invoice_id', \$invoice->id)", $completion, 'Completing a fully paid invoice must target all other sessions for that invoice.');
    $has('CollectionStatus::Cancelled', $completion, 'Other active collection sessions must be cancelled after the invoice is fully paid.');
    $has("whereIn('status'", $completion, 'Only active/incomplete collection sessions may be cancelled.');
}
$has('CollectionSessionCompletionService $completion', $reconciliation, 'Verified gateway payments must use the central collection-session completion service.');
$has('$this->completion->complete(', $reconciliation, 'Gateway reconciliation must cancel stale payment links when the invoice becomes fully paid.');
$claimService = $read('app/Domains/TitanPay/Services/PaymentClaimService.php');
$has('CollectionSessionCompletionService $completion', $claimService, 'Approved manual claims must use the central collection-session completion service.');
$has('$this->completion->complete(', $claimService, 'Manual claim approval must cancel stale payment links when the invoice becomes fully paid.');

$has("'customer_snapshot_json'", $invoiceService, 'Invoice issue must freeze a current customer snapshot.');
$has("'business_snapshot_json'", $invoiceService, 'Invoice issue must freeze a current company snapshot.');
$issueStart = strpos($invoiceService, 'public function issue');
$issueBody = $issueStart === false ? '' : substr($invoiceService, $issueStart, strpos($invoiceService, 'private function validateLineReferences') - $issueStart);
$has('$customer = Customer::query()->findOrFail($locked->customer_id);', $issueBody, 'Invoice issue must reload the customer immediately before freezing the issued snapshot.');
$has('$company = $this->currentCompany->require();', $issueBody, 'Invoice issue must reload the company immediately before freezing the issued snapshot.');


$collectionService = $read('app/Domains/TitanPay/Services/CollectionService.php');
$has('lockForUpdate()', $collectionService, 'Concurrent collection initiation must lock and recheck the current session.');
$has('existingInitiation', $collectionService, 'Collection initiation retries must reuse the stored provider session instead of creating another charge path.');
$stripeAdapter = $read('app/Domains/TitanPay/Adapters/StripeGatewayAdapter.php');
$has('Idempotency-Key', $stripeAdapter, 'Stripe checkout creation must include a stable provider idempotency key.');
$paypalAdapter = $read('app/Domains/TitanPay/Adapters/PayPalGatewayAdapter.php');
$has('PayPal-Request-Id', $paypalAdapter, 'PayPal order creation must include a stable provider idempotency key.');


$has("whereHas('session'", $claimService, 'Pending manual claims must be capped across every collection session for the same invoice.');

if ($failures !== []) {
    fwrite(STDERR, "RED: concurrency/integrity drift detected:\n - ".implode("\n - ", $failures)."\n");
    exit(1);
}

echo "GREEN: concurrency, lifecycle, scheduler and permission-drift checks pass.\n";
