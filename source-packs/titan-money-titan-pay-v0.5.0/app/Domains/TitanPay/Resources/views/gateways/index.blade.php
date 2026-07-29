@extends('layouts.app')
@section('title','Titan Pay gateways')
@section('content')
@include('titanpay::partials.header')
<main class="p-6">
    <div class="max-w-5xl mx-auto space-y-4">
        <section class="rounded-xl border border-purple-200 bg-purple-50 p-5">
            <h2 class="font-bold text-gray-900">Customer payment preference</h2>
            <p class="mt-1 text-sm text-gray-700">Titan Pay presents configured methods in this order: <strong>PayID → bank transfer → cash → credit/debit card through PayPal</strong>. PayID and bank details are resolved from Titan Vault or protected environment references and are not stored in this form.</p>
            <div class="mt-3 grid gap-2 text-sm md:grid-cols-3">
                <code class="rounded bg-white p-2">env:TITANPAY_PAYID</code>
                <code class="rounded bg-white p-2">env:TITANPAY_BANK</code>
                <code class="rounded bg-white p-2">env:TITANPAY_PAYPAL</code>
            </div>
        </section>

        <section class="bg-white border rounded-xl p-5">
            <h2 class="font-bold mb-3">Add or update payment method</h2>
            <form method="POST" action="{{ route('titanpay.gateways.store') }}" class="grid md:grid-cols-3 gap-3" id="gateway-form">
                @csrf
                <label class="text-sm">Provider
                    <select name="provider" id="gateway-provider" class="mt-1 w-full border rounded p-2">
                        @foreach(['payid'=>'PayID','bank_transfer'=>'Bank transfer','cash'=>'Cash','paypal'=>'PayPal card','stripe'=>'Stripe (optional)'] as $value=>$label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-sm">Display name
                    <input required name="display_name" class="mt-1 w-full border rounded p-2" placeholder="Customer-facing method name">
                </label>
                <label class="text-sm">Merchant/account reference
                    <input name="merchant_account_reference" class="mt-1 w-full border rounded p-2" placeholder="Optional non-secret identifier">
                </label>
                <label class="text-sm md:col-span-2">Titan Vault or environment secret reference
                    <input name="vault_secret_reference" id="gateway-secret-reference" class="mt-1 w-full border rounded p-2" placeholder="env:TITANPAY_PAYID">
                </label>
                <label class="text-sm">Environment
                    <select name="environment" class="mt-1 w-full border rounded p-2"><option value="live">Live</option><option value="sandbox">Sandbox</option></select>
                </label>
                <label class="text-sm">Currency
                    <input name="currency_code" value="AUD" class="mt-1 w-full border rounded p-2" maxlength="3">
                </label>
                <div class="md:col-span-2 flex items-end"><button class="px-4 py-2 bg-purple-600 text-white rounded">Save payment method</button></div>
            </form>
            <p class="mt-3 text-xs text-gray-500">Cash requires no secret reference. Stripe is excluded from the normal customer flow unless <code>TITANPAY_ALLOW_CUSTOMER_STRIPE=true</code>.</p>
        </section>

        @foreach($connections as $gateway)
            <section class="bg-white border rounded-xl p-4 flex justify-between gap-4">
                <div><strong>{{ $gateway->display_name }}</strong><p class="text-sm text-gray-600">{{ $gateway->provider }} · {{ $gateway->environment }} · {{ $gateway->merchant_account_reference ?: 'default account' }}</p><p class="text-xs text-gray-500">{{ $gateway->vault_secret_reference ?: 'No protected secret reference required' }}</p></div>
                <span class="text-sm {{ $gateway->is_active?'text-green-700':'text-gray-500' }}">{{ $gateway->is_active?'Active':'Disabled' }}</span>
            </section>
        @endforeach
    </div>
</main>
<script>
(() => {
    const provider = document.getElementById('gateway-provider');
    const reference = document.getElementById('gateway-secret-reference');
    const defaults = {
        payid: 'env:TITANPAY_PAYID',
        bank_transfer: 'env:TITANPAY_BANK',
        paypal: 'env:TITANPAY_PAYPAL',
        stripe: 'env:TITANPAY_STRIPE',
        cash: ''
    };
    const update = () => {
        if (!reference.value || Object.values(defaults).includes(reference.value)) {
            reference.value = defaults[provider.value] || '';
        }
        reference.placeholder = defaults[provider.value] || 'No secret required';
    };
    provider.addEventListener('change', update);
    update();
})();
</script>
@endsection
