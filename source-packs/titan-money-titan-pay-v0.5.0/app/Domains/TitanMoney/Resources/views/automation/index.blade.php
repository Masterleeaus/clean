@extends('layouts.app')

@section('title', 'Titan Money Agents')
@section('titan-money-title', 'Titan Money Agents')
@section('titan-money-subtitle', 'Governed auto-invoicing, receivables follow-up and payment reconciliation')

@section('content')
@include('titanmoney::partials.header')

<main class="p-6">
    <div class="max-w-7xl mx-auto space-y-6">
        @if(session('success'))
            <div class="p-3 bg-green-50 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-900">
            <strong>Bounded automation:</strong> new companies can auto-issue completed-job invoices up to A$1,000 and run staged receivables follow-up. Increase or disable that authority here. Final notices, disputes and payment exceptions remain governed.
        </div>

        <form method="POST" action="{{ route('titanmoney.automation.integration') }}" class="bg-white border rounded-xl p-5 flex flex-col md:flex-row md:items-end gap-3">
            @csrf
            <label class="block text-sm flex-1">
                Linked WorkCore company ID
                <input type="number" min="1" required name="workcore_company_id" value="{{ $company->settings_json['workcore_company_id'] ?? '' }}" class="mt-1 w-full border rounded-lg p-2" placeholder="For example: 1">
            </label>
            <label class="flex items-center gap-2 text-sm md:max-w-xs">
                <input type="checkbox" name="workcore_prices_tax_inclusive" value="1" @checked($company->settings_json['workcore_prices_tax_inclusive'] ?? false)>
                WorkCore service prices already include GST
            </label>
            <button class="px-4 py-2 bg-purple-600 text-white rounded-lg">Save WorkCore link</button>
            <p class="text-xs text-gray-500 md:max-w-md">Titan Zero uses this explicit mapping because WorkCore company IDs and Titan company ULIDs are different authorities. Set the GST price basis before enabling automatic issue.</p>
        </form>

        <div class="grid xl:grid-cols-3 gap-6">
            @foreach($policies as $policy)
                @php
                    $isInvoice = $policy->agent_key === 'invoice_generation';
                    $isReceivables = $policy->agent_key === 'receivables_follow_up';
                    $title = match($policy->agent_key) {
                        'invoice_generation' => 'Invoice Agent',
                        'receivables_follow_up' => 'Receivables Agent',
                        'payment_reconciliation' => 'Titan Pay Reconciliation Agent',
                        default => str_replace('_', ' ', ucfirst($policy->agent_key)),
                    };
                    $description = match($policy->agent_key) {
                        'invoice_generation' => 'Consumes authorised WorkCore billable events and creates or issues invoices idempotently.',
                        'receivables_follow_up' => 'Tracks due and overdue balances, queues staged follow-ups and escalates final notices.',
                        'payment_reconciliation' => 'Surfaces unmatched gateway events and bank deposits without inventing payment confirmation.',
                        default => '',
                    };
                @endphp

                <form method="POST" action="{{ route('titanmoney.automation.update', $policy) }}" class="bg-white border rounded-xl p-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <h2 class="font-bold text-lg">{{ $title }}</h2>
                        <p class="text-sm text-gray-500">{{ $description }}</p>
                    </div>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="enabled" value="1" @checked($policy->enabled)>
                        Enabled
                    </label>

                    <label class="block text-sm">
                        Autonomy
                        <select name="autonomy_level" class="mt-1 w-full border rounded-lg p-2">
                            @foreach(['observe'=>'Observe only','draft'=>'Draft or request approval','bounded'=>'Act within limits','full'=>'Full within policy'] as $value=>$label)
                                <option value="{{ $value }}" @selected($policy->autonomy_level === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    @if($isInvoice)
                        <label class="block text-sm">
                            Maximum automatic issue amount (AUD)
                            <input class="mt-1 w-full border rounded-lg p-2" type="number" min="0" step="0.01" name="max_auto_issue_amount" value="{{ number_format($policy->max_auto_issue_minor / 100, 2, '.', '') }}">
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="auto_issue" value="1" @checked($policy->settings_json['auto_issue'] ?? false)>
                            Automatically issue invoices within authority
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="send_on_issue" value="1" @checked($policy->settings_json['send_on_issue'] ?? true)>
                            Queue invoice delivery when issued
                        </label>
                        <label class="block text-sm">
                            Default payment terms (days)
                            <input class="mt-1 w-full border rounded-lg p-2" type="number" min="0" max="365" name="payment_terms_days" value="{{ $policy->settings_json['payment_terms_days'] ?? 14 }}">
                        </label>
                    @elseif($isReceivables)
                        <div class="text-sm">
                            <p class="font-medium mb-2">Delivery channels</p>
                            @foreach(['customer_app','email','sms','whatsapp','voice','internal'] as $channel)
                                <label class="inline-flex items-center gap-1 mr-3 mb-2">
                                    <input type="checkbox" name="channels[]" value="{{ $channel }}" @checked(in_array($channel, $policy->channels_json ?? ['email'], true))>
                                    {{ $channel === 'customer_app' ? 'Customer app' : ucfirst($channel) }}
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500">Default stages: due date, 3, 7, 14 and 30 days overdue. The final notice requires human approval.</p>
                    @else
                        <div class="text-sm">
                            <p class="font-medium mb-2">Exception channel</p>
                            <label class="inline-flex items-center gap-1">
                                <input type="checkbox" name="channels[]" value="internal" checked>
                                Internal finance queue
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">This agent reviews unmatched evidence and creates reconciliation exceptions. It does not mark invoices paid.</p>
                    @endif

                    <button class="px-4 py-2 bg-gray-900 text-white rounded-lg">Save policy</button>
                </form>
            @endforeach
        </div>

        <div class="bg-white border rounded-xl p-5">
            <div class="flex flex-wrap justify-between gap-3">
                <div>
                    <h2 class="font-bold">Run agents now</h2>
                    <p class="text-sm text-gray-500">Only enabled policies run. Dry-run records decisions without creating invoices, sending follow-ups or raising payment exceptions.</p>
                </div>
                <form method="POST" action="{{ route('titanmoney.automation.run') }}" class="flex flex-wrap gap-2 items-center">
                    @csrf
                    <select name="agent_key" class="border rounded-lg p-2">
                        <option value="">All enabled agents</option>
                        <option value="invoice_generation">Invoice Agent</option>
                        <option value="receivables_follow_up">Receivables Agent</option>
                        <option value="payment_reconciliation">Titan Pay Reconciliation Agent</option>
                    </select>
                    <label class="flex items-center gap-1 text-sm"><input type="checkbox" name="dry_run" value="1"> Dry run</label>
                    <button class="px-4 py-2 bg-gray-900 text-white rounded-lg">Run</button>
                </form>
            </div>
        </div>

        <div class="bg-white border rounded-xl overflow-hidden">
            <div class="p-4"><h2 class="font-bold">Pending approvals</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50"><tr><th class="p-3 text-left">Agent</th><th class="p-3 text-left">Action</th><th class="p-3 text-left">Reason</th><th class="p-3">Decision</th></tr></thead>
                    <tbody>
                    @forelse($approvals as $approval)
                        <tr class="border-t">
                            <td class="p-3">{{ $approval->agent_key }}</td>
                            <td class="p-3">{{ str_replace('_', ' ', $approval->action_key) }}</td>
                            <td class="p-3">{{ $approval->reason }}</td>
                            <td class="p-3">
                                <div class="flex justify-center gap-2">
                                    <form method="POST" action="{{ route('titanmoney.automation.approvals.approve', $approval) }}">@csrf<button class="px-3 py-1 bg-green-700 text-white rounded">Approve</button></form>
                                    <form method="POST" action="{{ route('titanmoney.automation.approvals.reject', $approval) }}">@csrf<button class="px-3 py-1 bg-red-700 text-white rounded">Reject</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-6 text-center text-gray-500">No pending approvals.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="bg-white border rounded-xl p-5"><p class="text-sm text-gray-500">Follow-ups recorded</p><p class="text-3xl font-bold">{{ $followUps->count() }}</p></div>
            <div class="bg-white border rounded-xl p-5"><p class="text-sm text-gray-500">Deliveries tracked</p><p class="text-3xl font-bold">{{ $deliveries->count() }}</p></div>
            <div class="bg-white border rounded-xl p-5"><p class="text-sm text-gray-500">Approvals waiting</p><p class="text-3xl font-bold">{{ $approvals->count() }}</p></div>
        </div>

        <div class="bg-white border rounded-xl overflow-hidden">
            <div class="p-4"><h2 class="font-bold">Recent agent runs</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50"><tr><th class="p-3 text-left">Started</th><th class="p-3 text-left">Agent</th><th class="p-3">Status</th><th class="p-3">Examined</th><th class="p-3">Acted</th><th class="p-3">Escalated</th></tr></thead>
                    <tbody>
                    @forelse($runs as $run)
                        <tr class="border-t"><td class="p-3">{{ $run->started_at?->format('d M Y H:i') }}</td><td class="p-3">{{ $run->agent_key }}</td><td class="p-3 text-center">{{ $run->status }}</td><td class="p-3 text-center">{{ $run->items_examined }}</td><td class="p-3 text-center">{{ $run->items_acted }}</td><td class="p-3 text-center">{{ $run->items_escalated }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-gray-500">No agent runs yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection
