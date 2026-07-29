<!doctype html>
<html lang="en-AU">
<head><meta charset="utf-8"><title>{{ $delivery->payload_json['subject'] ?? 'Invoice reminder' }}</title></head>
<body style="font-family:Arial,sans-serif;color:#111827;line-height:1.6">
    @foreach(explode("\n", (string)($delivery->payload_json['body'] ?? '')) as $line)
        @if($line === '')<br>@else<p style="margin:0 0 12px">{{ $line }}</p>@endif
    @endforeach
    @if(!empty($delivery->payload_json['payment_url']))
        <p style="margin:24px 0"><a href="{{ $delivery->payload_json['payment_url'] }}" style="background:#724FF9;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;display:inline-block">View and pay invoice</a></p>
        @if(!empty($delivery->payload_json['qr_url']))
            <p><img src="{{ $delivery->payload_json['qr_url'] }}" width="220" height="220" alt="Titan Pay invoice QR code"></p>
        @endif
        <p style="font-size:13px;color:#4b5563">Preferred methods: PayID, bank transfer, cash, then credit/debit card through PayPal.</p>
    @endif
    <p style="font-size:12px;color:#6b7280">Sent through Titan Money and Titan Pay.</p>
</body>
</html>
