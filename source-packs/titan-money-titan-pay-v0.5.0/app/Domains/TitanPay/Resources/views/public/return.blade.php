<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Payment status</title>
</head>
<body class="bg-gray-100 min-h-screen p-4">
<main class="max-w-lg mx-auto bg-white border rounded-2xl p-8 mt-12 text-center">
    <h1 class="text-2xl font-bold">Payment status</h1>
    @if(session('success'))
        <div class="mt-4 p-3 bg-green-50 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    @if(($paypalOrderId ?? '') !== '' && $session->status->value !== 'completed')
        <p class="mt-3">PayPal approved the order. Complete the server-side capture, then Titan Pay will wait for the signed provider webhook.</p>
        <form method="POST" action="{{ route('titanpay.public.paypal.capture', $token) }}" class="mt-5">
            @csrf
            <input type="hidden" name="order_id" value="{{ $paypalOrderId }}">
            <button class="px-5 py-3 bg-purple-600 text-white rounded-lg">Finalise PayPal payment</button>
        </form>
    @else
        <p class="mt-3">Titan Pay is waiting for verified confirmation from the payment provider.</p>
    @endif
    <p class="mt-4 text-xl font-semibold">{{ ucwords(str_replace('_',' ',$session->status->value)) }}</p>
    <p class="text-sm text-gray-500 mt-2">Returning to this page or requesting capture cannot mark an invoice paid. Only a verified webhook or approved evidence can do that.</p>
    <a href="{{ route('titanpay.public.show',$token) }}" class="inline-block mt-5 px-4 py-2 border rounded">Refresh details</a>
</main>
</body>
</html>
