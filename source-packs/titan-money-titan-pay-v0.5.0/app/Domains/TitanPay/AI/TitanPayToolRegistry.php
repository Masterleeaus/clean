<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\AI;

final class TitanPayToolRegistry
{
    /** @return array<string,array<string,mixed>> */
    public function tools(): array
    {
        return [
            'titanpay.create_collection_session' => ['permission'=>'titanpay.session.create','approval'=>'none','reversible'=>true],
            'titanpay.submit_payment_claim' => ['permission'=>'titanpay.claim.submit','approval'=>'none','reversible'=>true],
            'titanpay.review_payment_claim' => ['permission'=>'titanpay.claim.review','approval'=>'required','reversible'=>true],
            'titanpay.reconcile_transaction' => ['permission'=>'titanpay.reconciliation.resolve','approval'=>'required','reversible'=>true],
            'titanpay.refund_payment' => ['permission'=>'titanpay.refund.approve','approval'=>'required','reversible'=>false],
            'titanpay.configure_gateway' => ['permission'=>'titanpay.gateway.manage','approval'=>'required','reversible'=>true],
        ];
    }
}
