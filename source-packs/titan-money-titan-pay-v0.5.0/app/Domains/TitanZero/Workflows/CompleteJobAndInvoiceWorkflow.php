<?php

declare(strict_types=1);

namespace App\Domains\TitanZero\Workflows;

use App\Domains\Company\Models\Company;
use App\Domains\Company\Support\ActorContext;
use App\Domains\Company\Support\CompanyPermission;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\Services\AutomationPolicyService;
use App\Domains\TitanMoney\Services\BillableEventService;
use App\Domains\TitanMoney\Services\ChannelDeliveryService;
use App\Domains\TitanMoney\Services\InvoiceAutomationAgent;
use App\Domains\TitanMoney\Services\WorkCoreCustomerProjectionService;
use App\Domains\TitanMoney\Support\DecimalParser;
use App\Domains\TitanPay\Services\CollectionService;
use App\Domains\TitanPay\Services\QrCodeService;
use App\Domains\TitanPay\Support\PaymentMethodOrder;
use App\Domains\TitanZero\Chat\OperationalIntent;
use App\Domains\TitanZero\WorkCore\Contracts\WorkCoreJobGateway;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Throwable;

final class CompleteJobAndInvoiceWorkflow
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
        private readonly ActorContext $actorContext,
        private readonly CompanyPermission $permissions,
        private readonly WorkCoreJobGateway $workCore,
        private readonly WorkCoreCustomerProjectionService $customers,
        private readonly BillableEventService $billableEvents,
        private readonly AutomationPolicyService $policies,
        private readonly InvoiceAutomationAgent $invoiceAgent,
        private readonly CollectionService $collections,
        private readonly QrCodeService $qrCodes,
        private readonly PaymentMethodOrder $paymentMethods,
        private readonly ChannelDeliveryService $deliveries,
    ) {}

    public function execute(Company $company, User $user, int|string $conversationId, OperationalIntent $intent): CompleteJobAndInvoiceResult
    {
        $this->currentCompany->set($company);
        $correlationId = (string) Str::ulid();
        $this->actorContext->setHumanForConversation($user, $conversationId, $correlationId);

        if (! $this->permissions->allows($user, 'workcore.work_order.complete')) {
            throw new AuthorizationException('You are not permitted to complete WorkCore jobs.');
        }

        $resolution = $this->workCore->resolve($company, $intent->jobQuery);
        if ($resolution->status === 'unavailable') {
            return new CompleteJobAndInvoiceResult(false, (string) $resolution->message, ['status'=>'workcore_unavailable'], 409);
        }
        if ($resolution->status === 'not_found') {
            return new CompleteJobAndInvoiceResult(false, 'I could not find a WorkCore job matching “'.$intent->jobQuery.'”. Please include the job number or customer name.', ['status'=>'job_not_found'], 404);
        }
        if ($resolution->status === 'ambiguous') {
            $lines = array_map(static fn ($candidate): string => sprintf(
                '%s — %s for %s%s',
                $candidate->number,
                $candidate->title,
                $candidate->customerName,
                $candidate->premisesName ? ' at '.$candidate->premisesName : '',
            ), $resolution->candidates);

            return new CompleteJobAndInvoiceResult(false, "I found several matching jobs. Reply with one job number:\n• ".implode("\n• ", $lines), [
                'status'=>'job_ambiguous',
                'candidates'=>array_map(static fn ($candidate) => $candidate->toArray(), $resolution->candidates),
            ], 409);
        }

        $job = null;
        $invoice = null;
        $collection = null;
        $paymentUrl = null;
        $qrUrl = null;

        try {
            $job = $this->workCore->complete($company, $user, $resolution->job->publicId, $correlationId);
            $customer = $this->customers->project($job);
            $workCorePricesTaxInclusive = (bool) (($company->settings_json ?? [])['workcore_prices_tax_inclusive'] ?? false);
            $lines = $this->invoiceLines($job->services, $company->gst_registered);

            if ($lines === []) {
                return new CompleteJobAndInvoiceResult(true,
                    'Job '.$job->number.' is complete, but it has no priced billable services. I did not create a zero-value invoice.',
                    ['status'=>'job_completed_no_billable_lines','work_order_public_id'=>$job->publicId,'work_order_number'=>$job->number],
                );
            }

            $event = $this->billableEvents->ingest([
                'source_type' => 'completed_job',
                'source_id' => $job->publicId,
                'source_version' => $job->sourceVersion,
                'idempotency_key' => 'workcore:completed_job:'.$job->publicId.':'.$job->sourceVersion,
                'customer_id' => $customer->id,
                'occurred_at' => $job->completedAt ?: now(),
                'payload' => [
                    'currency_code' => strtoupper((string) ($company->currency_code ?: 'AUD')),
                    'tax_inclusive' => $workCorePricesTaxInclusive,
                    'work_order_id' => $job->publicId,
                    'property_id' => $job->premises['public_id'] ?? null,
                    'notes' => 'Generated from completed WorkCore job '.$job->number.'.',
                    'customer_message' => 'Thank you. Your job has been completed.',
                    'lines' => $lines,
                ],
            ]);

            $policy = $this->policies->ensureDefaults($company)['invoice_generation'];
            $run = $this->invoiceAgent->runForEvent($policy, $event, false, 'chat');
            $event->refresh();
            $invoice = $event->invoice_id ? Invoice::query()->find($event->invoice_id) : null;

            if (! $invoice) {
                $eventResult = (array) (($run->result_json ?? [])[$event->id] ?? []);
                $decision = (string) ($eventResult['decision'] ?? 'unknown');

                if ($decision === 'failed' || $run->status === 'failed') {
                    return new CompleteJobAndInvoiceResult(false,
                        'Job '.$job->number.' is complete, but the Invoice Agent could not create the invoice. The job was not rolled back and no payment was confirmed. Reference: '.$correlationId,
                        [
                            'status'=>'job_completed_invoice_failed',
                            'agent_run_id'=>$run->id,
                            'work_order_public_id'=>$job->publicId,
                            'work_order_number'=>$job->number,
                            'correlation_id'=>$correlationId,
                        ],
                        200,
                    );
                }

                $reason = $eventResult['reason'] ?? $run->error_message ?? 'The company policy did not permit invoice creation.';
                return new CompleteJobAndInvoiceResult(true,
                    'Job '.$job->number.' is complete, but no invoice was created. '.$reason,
                    [
                        'status'=>'job_completed_invoice_not_created',
                        'agent_run_id'=>$run->id,
                        'work_order_public_id'=>$job->publicId,
                        'work_order_number'=>$job->number,
                        'correlation_id'=>$correlationId,
                    ],
                );
            }

            $metadata = [
                'status'=>'invoice_'.$invoice->status->value,
                'work_order_public_id'=>$job->publicId,
                'work_order_number'=>$job->number,
                'invoice_id'=>$invoice->id,
                'invoice_number'=>$invoice->invoice_number,
                'amount_minor'=>$invoice->total_minor,
                'currency_code'=>$invoice->currency_code,
                'agent_run_id'=>$run->id,
                'correlation_id'=>$correlationId,
            ];

            if (! in_array($invoice->status->value, ['issued','partially_paid','overdue'], true) || ! $intent->sendInvoice) {
                return new CompleteJobAndInvoiceResult(true,
                    sprintf('Job %s is complete. Invoice %s was created with status %s.', $job->number, $invoice->invoice_number, $invoice->status->value),
                    $metadata,
                );
            }

            $collection = $this->collections->createForInvoice($invoice, $this->paymentMethods->customerDefault(), 60 * 24 * 14);
            $this->qrCodes->record($collection['session'], $collection['token']);
            $paymentUrl = route('titanpay.public.show', ['token'=>$collection['token']]);
            $qrUrl = $this->qrCodes->imageUrl($collection['token']);
            $queued = $this->deliveries->queueIssued($invoice, 'chat:'.$correlationId);

            $metadata += [
                'collection_session_id'=>$collection['session']->id,
                'payment_url'=>$paymentUrl,
                'qr_url'=>$qrUrl,
                'payment_methods'=>$collection['session']->allowed_methods_json,
                'deliveries_queued'=>$queued,
            ];

            return new CompleteJobAndInvoiceResult(true,
                sprintf(
                    "Job %s is complete and invoice %s for %s has been issued. The customer payment link and QR code are ready. Preferred methods: PayID, bank transfer, cash, then card through PayPal.",
                    $job->number,
                    $invoice->invoice_number,
                    $this->formatMoney($invoice->total_minor, $invoice->currency_code),
                ),
                $metadata,
            );
        } catch (Throwable $error) {
            report($error);

            if ($invoice instanceof Invoice && in_array($invoice->status->value, ['issued', 'partially_paid', 'overdue'], true)) {
                $metadata = [
                    'status'=>'invoice_issued_collection_failed',
                    'work_order_public_id'=>$job?->publicId,
                    'work_order_number'=>$job?->number,
                    'invoice_id'=>$invoice->id,
                    'invoice_number'=>$invoice->invoice_number,
                    'amount_minor'=>$invoice->total_minor,
                    'currency_code'=>$invoice->currency_code,
                    'correlation_id'=>$correlationId,
                ];

                if (is_array($collection) && isset($collection['session'], $collection['token'])) {
                    $metadata['collection_session_id'] = $collection['session']->id;
                    $metadata['payment_url'] = $paymentUrl ?: route('titanpay.public.show', ['token'=>$collection['token']]);
                    $metadata['qr_url'] = $qrUrl;
                    $metadata['payment_methods'] = $collection['session']->allowed_methods_json;
                }

                return new CompleteJobAndInvoiceResult(false,
                    sprintf(
                        'Job %s is complete and invoice %s was issued, but I could not finish the payment-link or delivery step. The invoice remains valid and no payment was confirmed. Reference: %s',
                        $job?->number ?? 'unknown',
                        $invoice->invoice_number,
                        $correlationId,
                    ),
                    $metadata,
                    200,
                );
            }

            if ($job !== null) {
                return new CompleteJobAndInvoiceResult(false,
                    sprintf(
                        'Job %s is complete, but automatic invoicing did not finish. I did not roll back the completed job and no payment was confirmed. Reference: %s',
                        $job->number,
                        $correlationId,
                    ),
                    [
                        'status'=>'job_completed_invoice_failed',
                        'work_order_public_id'=>$job->publicId,
                        'work_order_number'=>$job->number,
                        'invoice_id'=>$invoice?->id,
                        'invoice_number'=>$invoice?->invoice_number,
                        'correlation_id'=>$correlationId,
                    ],
                    200,
                );
            }

            return new CompleteJobAndInvoiceResult(false,
                'I could not finish that workflow safely. No payment was confirmed. Reference: '.$correlationId,
                ['status'=>'workflow_failed','correlation_id'=>$correlationId],
                422,
            );
        }
    }

    /** @param array<int,array<string,mixed>> $services @return array<int,array<string,mixed>> */
    private function invoiceLines(array $services, bool $gstRegistered): array
    {
        $lines = [];
        foreach ($services as $index => $service) {
            if (($service['unit_price'] ?? null) === null || DecimalParser::minorUnits((string) $service['unit_price']) <= 0) {
                continue;
            }
            $quantity = (string) ($service['quantity'] ?? '1');
            if (DecimalParser::quantity($quantity) <= 0) {
                continue;
            }
            $lines[] = [
                'line_type' => 'service',
                'description' => (string) ($service['service_name'] ?? $service['description'] ?? 'Service'),
                'quantity' => $quantity,
                'unit_price_minor' => DecimalParser::minorUnits((string) $service['unit_price']),
                'discount_minor' => 0,
                'tax_code' => $gstRegistered ? 'GST' : 'NO_GST',
                'tax_rate_basis_points' => $gstRegistered ? 1000 : 0,
                'metadata_json' => ['workcore_service_public_id'=>$service['public_id'] ?? null],
                'display_order' => (int) ($service['sort_order'] ?? $index),
            ];
        }

        return $lines;
    }

    private function formatMoney(int $minor, string $currency): string
    {
        return strtoupper($currency).' '.number_format($minor / 100, 2, '.', ',');
    }
}
