<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Adapters;

use App\Domains\TitanPay\Models\CollectionSession;
use App\Domains\TitanPay\Models\GatewayConnection;

final class CashGatewayAdapter extends AbstractManualAdapter
{
    public function name(): string { return 'cash'; }
    protected function instructions(CollectionSession $session, ?GatewayConnection $connection): array
    {
        return ['message'=>'Record the cash receipt through an authorised TitanPay user. Cash remains pending until reconciled.','reference'=>$session->reference];
    }
}
