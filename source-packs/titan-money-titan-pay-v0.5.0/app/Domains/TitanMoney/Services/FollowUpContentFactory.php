<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\TitanMoney\Models\Invoice;
use App\Domains\TitanMoney\ValueObjects\Money;

final class FollowUpContentFactory
{
    /** @return array{subject:string,body:string} */
    public function make(Invoice $invoice, string $templateKey, string $tone, int $daysOverdue): array
    {
        $customer = $invoice->customer_snapshot_json['name'] ?? $invoice->customer?->name ?? 'there';
        $amount = (new Money((int) $invoice->balance_due_minor, (string) $invoice->currency_code))
            ->format((string) config('app.locale', 'en_AU'));
        $due = $invoice->due_date?->format('j M Y') ?? 'the due date';

        return match ($templateKey) {
            'invoice_issued' => [
                'subject' => "Invoice {$invoice->invoice_number} from ".($invoice->business_snapshot_json['trading_name'] ?? $invoice->business_snapshot_json['name'] ?? 'Titan Money'),
                'body' => "Hi {$customer},\n\nInvoice {$invoice->invoice_number} has been issued for {$amount} and is due on {$due}.\n\nThank you.",
            ],
            'invoice_due_today' => [
                'subject' => "Invoice {$invoice->invoice_number} is due today",
                'body' => "Hi {$customer},\n\nA friendly reminder that invoice {$invoice->invoice_number} for {$amount} is due today.\n\nPlease disregard this message if payment has already been made.",
            ],
            'invoice_overdue_friendly' => [
                'subject' => "Friendly reminder: invoice {$invoice->invoice_number} is overdue",
                'body' => "Hi {$customer},\n\nInvoice {$invoice->invoice_number} for {$amount} was due on {$due} and is now {$daysOverdue} days overdue.\n\nPlease let us know if payment has already been made or if you need help.",
            ],
            'invoice_overdue_firm', 'invoice_overdue_escalation' => [
                'subject' => "Action required: invoice {$invoice->invoice_number} remains overdue",
                'body' => "Hi {$customer},\n\nOur records show invoice {$invoice->invoice_number} for {$amount} remains unpaid {$daysOverdue} days after its due date of {$due}.\n\nPlease arrange payment or contact us promptly to discuss the account.",
            ],
            'invoice_final_notice' => [
                'subject' => "Final payment notice: invoice {$invoice->invoice_number}",
                'body' => "Hi {$customer},\n\nInvoice {$invoice->invoice_number} for {$amount} remains unpaid {$daysOverdue} days after its due date. This is a final automated notice pending human review.\n\nPlease pay or contact us immediately if the balance is disputed or assistance is required.",
            ],
            default => [
                'subject' => "Invoice reminder: {$invoice->invoice_number}",
                'body' => "Hi {$customer},\n\nThis is a reminder that {$amount} remains outstanding on invoice {$invoice->invoice_number}.\n\nPlease disregard this message if payment has already been made.",
            ],
        };
    }
}
