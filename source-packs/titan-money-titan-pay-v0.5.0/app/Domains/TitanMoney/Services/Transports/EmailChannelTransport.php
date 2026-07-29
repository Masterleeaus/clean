<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services\Transports;

use App\Domains\TitanMoney\Contracts\ChannelTransport;
use App\Domains\TitanMoney\Mail\InvoiceFollowUpMail;
use App\Domains\TitanMoney\Models\ChannelDelivery;
use App\Domains\TitanMoney\Services\DeliveryReceipt;
use Illuminate\Support\Facades\Mail;
use LogicException;

final class EmailChannelTransport implements ChannelTransport
{
    public function supports(string $channel): bool { return $channel === 'email'; }

    public function send(ChannelDelivery $delivery): DeliveryReceipt
    {
        if (! $delivery->recipient_address) {
            throw new LogicException('No customer email address is available.');
        }
        Mail::to($delivery->recipient_address)->send(new InvoiceFollowUpMail($delivery));

        return new DeliveryReceipt('laravel-mail', null, 'sent', ['recipient'=>$delivery->recipient_address]);
    }
}
