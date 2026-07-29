<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Adapters;

use App\Domains\TitanPay\Contracts\GatewayAdapter;
use App\Domains\TitanPay\Contracts\SecretResolver;
use App\Domains\TitanPay\Models\CollectionSession;
use App\Domains\TitanPay\Models\GatewayConnection;
use App\Domains\TitanPay\Security\StripeWebhookVerifier;
use App\Domains\TitanPay\ValueObjects\GatewayInitiation;
use App\Domains\TitanPay\ValueObjects\WebhookVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class StripeGatewayAdapter implements GatewayAdapter
{
    public function __construct(private readonly SecretResolver $secrets, private readonly StripeWebhookVerifier $verifier) {}
    public function name(): string { return 'stripe'; }
    public function isAvailable(?GatewayConnection $connection = null): bool
    {
        if (! $connection?->vault_secret_reference) return false;
        return isset($this->secrets->resolve($connection->vault_secret_reference)['secret_key']);
    }
    public function initiate(CollectionSession $session, ?GatewayConnection $connection = null): GatewayInitiation
    {
        if (! $connection) throw new RuntimeException('Stripe gateway connection is required.');
        $secret = $this->secrets->resolve($connection->vault_secret_reference);
        $response = Http::asForm()
            ->withToken((string) ($secret['secret_key'] ?? ''))
            ->withHeaders(['Idempotency-Key' => 'titanpay-'.$session->id])
            ->post('https://api.stripe.com/v1/checkout/sessions', [
            'mode'=>'payment',
            'success_url'=>route('titanpay.public.return',['token'=>$session->getAttribute('public_token_once')]).'?provider=stripe',
            'cancel_url'=>route('titanpay.public.show',['token'=>$session->getAttribute('public_token_once')]),
            'client_reference_id'=>$session->reference,
            'metadata[collection_session_id]'=>$session->id,
            'line_items[0][quantity]'=>1,
            'line_items[0][price_data][currency]'=>strtolower($session->currency_code),
            'line_items[0][price_data][unit_amount]'=>$session->amount_minor,
            'line_items[0][price_data][product_data][name]'=>'Invoice '.$session->invoice?->invoice_number,
        ]);
        if (! $response->successful()) throw new RuntimeException('Stripe checkout session creation failed.');
        $data=$response->json();
        return new GatewayInitiation('processing',(string)$data['id'],(string)$data['url'],raw:$data);
    }
    public function verifyWebhook(Request $request, ?GatewayConnection $connection = null): WebhookVerification
    {
        if (! $connection) return new WebhookVerification(false,'missing-connection','unknown',failureReason:'Gateway connection not found.');
        $secret=$this->secrets->resolve($connection->vault_secret_reference);
        $payload=$request->getContent();
        $valid=$this->verifier->verify($payload,(string)$request->header('Stripe-Signature'),(string)($secret['webhook_secret']??''));
        $data=json_decode($payload,true) ?: [];
        $object=$data['data']['object']??[];
        $metadata=$object['metadata']??[];
        $paid=($object['payment_status']??null)==='paid' || ($object['status']??null)==='succeeded';
        return new WebhookVerification(
            $valid,
            (string)($data['id']??hash('sha256',$payload)),
            (string)($data['type']??'unknown'),
            (string)($object['client_reference_id']??$metadata['collection_reference']??''),
            (string)($object['payment_intent']??$object['id']??''),
            isset($object['amount_total'])?(int)$object['amount_total']:(isset($object['amount_received'])?(int)$object['amount_received']:null),
            isset($object['currency'])?strtoupper((string)$object['currency']):null,
            $valid&&$paid?'completed':'ignored',
            $data,
            $valid?($paid?null:'Stripe event is not a completed payment.'):'Invalid Stripe signature.',
        );
    }
    public function refund(string $transactionReference,int $amountMinor,string $currencyCode,?GatewayConnection $connection=null): array
    {
        if (! $connection) throw new RuntimeException('Stripe gateway connection is required.');
        $secret=$this->secrets->resolve($connection->vault_secret_reference);
        $response=Http::asForm()->withToken((string)($secret['secret_key']??''))->post('https://api.stripe.com/v1/refunds',['payment_intent'=>$transactionReference,'amount'=>$amountMinor]);
        if (! $response->successful()) throw new RuntimeException('Stripe refund failed.');
        return $response->json();
    }
}
