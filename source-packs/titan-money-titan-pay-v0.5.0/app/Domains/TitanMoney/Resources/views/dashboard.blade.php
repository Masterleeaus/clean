@extends('layouts.app')
@section('title','Titan Money')
@section('titan-money-title','Titan Money')
@section('titan-money-subtitle','Invoices, receivables, payments and recurring billing')
@section('content')
@include('titanmoney::partials.header')
<main class="p-6">
    <div class="max-w-7xl mx-auto space-y-6">
        @if(session('success'))
            <div class="p-3 bg-green-50 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif

        @forelse($metrics['currencies'] as $currencyMetrics)
            <div>
                <p class="mb-2 text-sm font-semibold text-gray-500">{{ $currencyMetrics['currency_code'] }}</p>
                <div class="grid md:grid-cols-3 gap-4">
                    @foreach([
                        ['Invoiced', $currencyMetrics['total_invoiced_minor']],
                        ['Outstanding', $currencyMetrics['outstanding_minor']],
                        ['Paid', $currencyMetrics['paid_minor']],
                    ] as $metric)
                        <div class="bg-white p-5 rounded-xl border">
                            <p class="text-sm text-gray-500">{{ $metric[0] }}</p>
                            <p class="text-2xl font-bold">{{ formatMoneyMinor($metric[1], $currencyMetrics['currency_code']) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white p-5 rounded-xl border text-gray-500">No financial totals yet.</div>
        @endforelse

        <div class="bg-white p-5 rounded-xl border">
            <p class="text-sm text-gray-500">Overdue invoices</p>
            <p class="text-2xl font-bold">{{ $metrics['overdue_count'] }}</p>
        </div>

        <div class="bg-white rounded-xl border overflow-hidden">
            <div class="p-4 flex justify-between">
                <h2 class="font-bold">Recent invoices</h2>
                <a class="text-purple-700" href="{{ route('titanmoney.invoices.create') }}">Create invoice</a>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr><th class="p-3 text-left">Invoice</th><th class="p-3 text-left">Customer</th><th class="p-3 text-right">Balance</th><th class="p-3">Status</th></tr></thead>
                <tbody>
                @forelse($recentInvoices as $invoice)
                    <tr class="border-t"><td class="p-3"><a class="font-semibold text-purple-700" href="{{ route('titanmoney.invoices.show',$invoice) }}">{{ $invoice->invoice_number }}</a></td><td class="p-3">{{ $invoice->customer->name }}</td><td class="p-3 text-right">{{ formatMoneyMinor($invoice->balance_due_minor, $invoice->currency_code) }}</td><td class="p-3 text-center">{{ str_replace('_',' ',$invoice->status->value) }}</td></tr>
                @empty
                    <tr><td class="p-6 text-center text-gray-500" colspan="4">No invoices yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection
