@extends('layouts.app')
@section('title','Titan Pay')
@section('content')
@include('titanpay::partials.header')
<main class="p-6">
    <div class="max-w-7xl mx-auto space-y-5">
        <div class="grid md:grid-cols-2 gap-4">
            <div class="bg-white border rounded-xl p-5">
                <p class="text-gray-500">Verified collections</p>
                @forelse($completedByCurrency as $total)
                    <p class="text-3xl font-bold">{{ formatMoneyMinor($total->total_minor, $total->currency_code) }}</p>
                @empty
                    <p class="text-3xl font-bold">{{ formatMoneyMinor(0, 'AUD') }}</p>
                @endforelse
            </div>
            <div class="bg-white border rounded-xl p-5"><p class="text-gray-500">Pending sessions</p><p class="text-3xl font-bold">{{ $pending_count }}</p></div>
        </div>
        <div class="bg-white border rounded-xl">
            <h2 class="p-4 font-bold">Recent sessions</h2>
            <table class="w-full text-sm"><tr><th class="p-3 text-left">Reference</th><th>Invoice</th><th>Amount</th><th>Status</th></tr>
            @foreach($sessions as $s)
                <tr class="border-t"><td class="p-3"><a class="text-purple-700 font-bold" href="{{ route('titanpay.sessions.show',$s) }}">{{ $s->reference }}</a></td><td>{{ $s->invoice?->invoice_number }}</td><td>{{ formatMoneyMinor($s->amount_minor, $s->currency_code) }}</td><td>{{ $s->status->value }}</td></tr>
            @endforeach
            </table>
        </div>
    </div>
</main>
@endsection
