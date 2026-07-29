<!DOCTYPE html>
<html lang="en-AU">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Titan Pay — {{ $session->invoice?->invoice_number ?? $session->reference }}</title>
</head>
<body class="min-h-screen bg-gray-100 p-4">
<main class="mx-auto mt-8 max-w-xl rounded-2xl border bg-white p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Titan Pay</h1>
            <p class="text-gray-500">Secure invoice collection</p>
        </div>
        <span class="rounded-full bg-gray-100 px-3 py-1 text-sm">{{ str_replace('_', ' ', $session->status->value) }}</span>
    </div>

    <div class="my-6 rounded-xl bg-gray-950 p-5 text-white">
        <p class="text-sm text-gray-300">Amount due</p>
        <p class="text-4xl font-bold">{{ formatMoneyMinor($session->amount_minor, $session->currency_code) }}</p>
        <p class="mt-1 text-sm text-gray-300">{{ $session->currency_code }} · {{ $session->reference }}</p>
        @if($session->invoice)
            <p class="mt-1 text-sm text-gray-300">Invoice {{ $session->invoice->invoice_number }}</p>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 rounded bg-green-50 p-3 text-green-800">{{ session('success') }}</div>
    @endif

    <section class="mb-6 rounded-xl border p-4 text-center">
        <img
            src="{{ route('titanpay.public.qr', ['token' => $token]) }}"
            alt="QR code for this secure Titan Pay invoice"
            class="mx-auto h-56 w-56"
        >
        <p class="mt-2 text-sm text-gray-600">Scan this code to reopen this secure invoice payment page.</p>
    </section>

    @if(isset($initiation))
        <div class="mb-5 rounded bg-blue-50 p-4">
            <p>{{ $initiation->instructions['message'] ?? 'Follow the payment instructions.' }}</p>
            @foreach($initiation->instructions as $key => $value)
                @if(!is_array($value) && $key !== 'message' && $value)
                    <p><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</p>
                @endif
            @endforeach
        </div>
    @endif

    @if(!in_array($session->status->value, ['completed','expired','cancelled'], true))
        <form class="space-y-3" method="POST" action="{{ route('titanpay.public.initiate', $token) }}">
            @csrf
            <label class="block font-medium">
                Payment method
                <select name="method" class="mt-1 w-full rounded border p-3">
                    @foreach($session->allowed_methods_json as $method)
                        <option value="{{ $method }}">
                            @switch($method)
                                @case('payid') PayID — preferred @break
                                @case('bank_transfer') Bank transfer @break
                                @case('cash') Cash @break
                                @case('paypal') Credit/debit card through PayPal @break
                                @case('stripe') Credit/debit card through Stripe @break
                                @default {{ ucwords(str_replace('_', ' ', $method)) }}
                            @endswitch
                        </option>
                    @endforeach
                </select>
            </label>
            <button class="w-full rounded-lg bg-purple-600 p-3 font-semibold text-white">Continue</button>
        </form>

        <details class="mt-5">
            <summary class="cursor-pointer font-semibold">I have already paid by cash, PayID or bank transfer</summary>
            <form method="POST" enctype="multipart/form-data" action="{{ route('titanpay.public.claim', $token) }}" class="mt-3 space-y-3">
                @csrf
                <input type="hidden" name="amount" value="{{ number_format($session->amount_minor / 100, 2, '.', '') }}">
                <select name="method" class="w-full rounded border p-2">
                    <option value="payid">PayID</option>
                    <option value="bank_transfer">Bank transfer</option>
                    <option value="cash">Cash</option>
                </select>
                <input name="reference" class="w-full rounded border p-2" placeholder="Payment reference">
                <input name="payer_name" class="w-full rounded border p-2" placeholder="Your name">
                <input type="email" name="payer_email" class="w-full rounded border p-2" placeholder="Email">
                <input type="file" name="evidence" accept="image/jpeg,image/png,application/pdf" class="w-full rounded border p-2">
                <textarea name="notes" class="w-full rounded border p-2" placeholder="Notes"></textarea>
                <button class="w-full rounded border p-3">Submit payment claim</button>
                <p class="text-xs text-gray-500">Evidence does not automatically mark the invoice paid. Titan Pay verifies and reconciles it first.</p>
            </form>
        </details>
    @else
        <p class="mt-5 text-center font-semibold">This collection session is {{ str_replace('_', ' ', $session->status->value) }}.</p>
    @endif
</main>
</body>
</html>
