<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Mail;

use App\Domains\TitanMoney\Models\ChannelDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class InvoiceFollowUpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly ChannelDelivery $delivery) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: (string) ($this->delivery->payload_json['subject'] ?? 'Invoice reminder'));
    }

    public function content(): Content
    {
        return new Content(view: 'titanmoney::emails.invoice-follow-up');
    }
}
