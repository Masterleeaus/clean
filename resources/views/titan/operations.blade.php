@extends('layouts.app')

@section('title', 'Titan Operations')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/titan-operations.css') }}">
@endpush

@section('content')
<div class="titan-operations-shell" data-titan-operations-root data-maps-enabled="{{ $mapsEnabled ? '1' : '0' }}">
    <header class="titan-operations-header">
        <div>
            <p class="titan-eyebrow">Titan Zero · WorkCore</p>
            <h1>Operations command centre</h1>
            <p>Company-scoped communication, operational records, capabilities and governed automation.</p>
        </div>
        <div class="titan-company-switcher" data-titan-company-switcher>
            <label for="titan-company-select">Active company</label>
            <select id="titan-company-select" data-titan-company-select>
                @foreach ($memberships as $membership)
                    <option value="{{ $membership->company_id }}" @selected($membership->company_id === $activeContext->companyId)>
                        {{ $membership->company->name }}
                    </option>
                @endforeach
            </select>
            <span data-titan-company-status>{{ $activeCompany->name }}</span>
        </div>
    </header>

    @if (session('success'))
        <div class="titan-notice titan-notice-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="titan-notice titan-notice-error">{{ $errors->first() }}</div>
    @endif

    <section class="titan-grid titan-grid-stats" data-titan-workcore-summary aria-live="polite">
        @foreach ([
            'customers' => 'Customers', 'premises' => 'Premises', 'work_orders' => 'Work orders',
            'appointments' => 'Appointments', 'workers' => 'Workers', 'assets' => 'Assets',
            'documents' => 'Documents', 'evidence' => 'Evidence', 'inspections' => 'Inspections',
            'findings' => 'Findings', 'risks' => 'Risks', 'incidents' => 'Incidents',
            'agreements' => 'Agreements', 'occupancies' => 'Occupancies', 'reservations' => 'Reservations',
            'stays' => 'Stays', 'housekeeping' => 'Housekeeping',
        ] as $key => $label)
            <article class="titan-card titan-stat-card">
                <span>{{ $label }}</span>
                <strong data-titan-count="{{ $key }}">—</strong>
            </article>
        @endforeach
    </section>

    <section class="titan-card titan-panel titan-assurance-panel" data-titan-assurance-summary aria-live="polite">
        <div class="titan-panel-heading">
            <div>
                <p class="titan-eyebrow">WorkCore Assurance</p>
                <h2>Compliance, quality and incident queue</h2>
            </div>
            <span class="titan-status-pill is-ready">Canonical domain</span>
        </div>
        <div class="titan-assurance-grid">
            @foreach ([
                'pending_inspections' => 'Pending inspections',
                'open_findings' => 'Open findings',
                'overdue_corrective_actions' => 'Overdue corrective actions',
                'high_critical_risks' => 'High or critical risks',
                'open_incidents' => 'Open incidents',
            ] as $key => $label)
                <div class="titan-assurance-metric">
                    <span>{{ $label }}</span>
                    <strong data-titan-assurance-count="{{ $key }}">—</strong>
                </div>
            @endforeach
        </div>
        <p class="titan-help">Inspections, evidence, findings, corrective actions, risk and incidents are company-scoped WorkCore records. Titan Zero can only change them through governed actions.</p>
    </section>

    <section class="titan-card titan-panel titan-accommodation-panel" data-titan-accommodation-summary aria-live="polite">
        <div class="titan-panel-heading">
            <div>
                <p class="titan-eyebrow">WorkCore Property & Accommodation</p>
                <h2>Agreements, occupancy, arrivals and room readiness</h2>
            </div>
            <span class="titan-status-pill is-ready">Premises-owned execution</span>
        </div>
        <div class="titan-assurance-grid">
            @foreach ([
                'arrivals_today' => 'Arrivals today',
                'departures_today' => 'Departures today',
                'in_house' => 'Guests in house',
                'dirty_spaces' => 'Spaces awaiting readiness',
                'active_agreements' => 'Active agreements',
                'active_occupancies' => 'Active occupancies',
            ] as $key => $label)
                <div class="titan-assurance-metric">
                    <span>{{ $label }}</span>
                    <strong data-titan-accommodation-count="{{ $key }}">—</strong>
                </div>
            @endforeach
        </div>
        <p class="titan-help">Long-term agreements and occupancy share the same physical premises and spaces as short-stay reservations. Accommodation checkout can close only records it created; established tenancy records remain protected.</p>
    </section>



    <section class="titan-card titan-panel" data-titan-intelligence-summary aria-live="polite">
        <div class="titan-panel-heading">
            <div><p class="titan-eyebrow">Titan Intelligence Runtime</p><h2>Workspace, memory, skills, agents and connectors</h2></div>
            <span class="titan-status-pill is-ready">First-party authority</span>
        </div>
        <div class="titan-assurance-grid">
            @foreach ([
                'active_agents' => 'Agents',
                'active_connections' => 'Connections',
                'active_memories' => 'Active memories',
                'published_skills' => 'Published skills',
                'active_voice_sessions' => 'Voice sessions',
                'pending_onboarding' => 'Pending onboarding',
            ] as $key => $label)
                <div class="titan-assurance-metric"><span>{{ $label }}</span><strong data-titan-intelligence-count="{{ $key }}">—</strong></div>
            @endforeach
        </div>
        <p class="titan-help">Counts are company-scoped. Memory content, prompts, share tokens, connector credentials and raw provider responses are never exposed in this summary.</p>
    </section>


    <section class="titan-card titan-panel" data-titan-creative-summary aria-live="polite">
        <div class="titan-panel-heading">
            <div><p class="titan-eyebrow">Titan Creative &amp; Marketing</p><h2>Campaigns, content, generation and publishing</h2></div>
            <span class="titan-status-pill is-ready">First-party authority</span>
        </div>
        <div class="titan-assurance-grid">
            @foreach ([
                'active_campaigns' => 'Campaigns',
                'open_projects' => 'Creative projects',
                'generation_queue' => 'Generation queue',
                'pending_approvals' => 'Pending approvals',
                'pending_publications' => 'Pending publications',
                'active_automations' => 'Active automations',
            ] as $key => $label)
                <div class="titan-assurance-metric"><span>{{ $label }}</span><strong data-titan-creative-count="{{ $key }}">—</strong></div>
            @endforeach
        </div>
        <p class="titan-help">Counts are company-scoped. Prompts, generated content, private asset references, provider payloads and connector credentials are excluded from this summary.</p>
    </section>

    <section class="titan-card titan-panel" data-titan-finance-summary aria-live="polite">
        <div class="titan-panel-heading">
            <div><p class="titan-eyebrow">WorkCore Finance</p><h2>Receivables and expenses</h2></div>
            <span class="titan-status-pill is-ready">AUD · accounting authority</span>
        </div>
        <div class="titan-assurance-grid">
            @foreach (['open_receivables_minor' => 'Open receivables', 'overdue_invoices' => 'Overdue invoices', 'draft_expenses_minor' => 'Draft expenses'] as $key => $label)
                <div class="titan-assurance-metric"><span>{{ $label }}</span><strong data-titan-finance-count="{{ $key }}">—</strong></div>
            @endforeach
        </div>
        <p class="titan-help">Invoice, credit, expense and journal records are owned by WorkCore Finance. Amounts use integer minor units and default to Australian dollars (AUD).</p>
    </section>

    <section class="titan-card titan-panel" data-titan-payment-summary aria-live="polite">
        <div class="titan-panel-heading">
            <div><p class="titan-eyebrow">ZeroPay</p><h2>Payment observation and reconciliation</h2></div>
            <span class="titan-status-pill is-ready">Provider-neutral</span>
        </div>
        <div class="titan-assurance-grid">
            @foreach (['pending_sessions' => 'Pending sessions', 'unmatched_observations' => 'Unmatched observations', 'proposed_matches' => 'Proposed matches'] as $key => $label)
                <div class="titan-assurance-metric"><span>{{ $label }}</span><strong data-titan-payment-count="{{ $key }}">—</strong></div>
            @endforeach
        </div>
        <p class="titan-help">Payment providers do not own invoices. ZeroPay records sessions and external observations, then requests governed receivable allocation through WorkCore Finance.</p>
    </section>

    <section class="titan-card titan-panel" data-titan-trust-summary aria-live="polite">
        <div class="titan-panel-heading">
            <div><p class="titan-eyebrow">Trust accounting</p><h2>Client-money controls</h2></div>
            <span class="titan-status-pill is-ready">Segregated ledger</span>
        </div>
        <div class="titan-assurance-grid">
            @foreach (['ledger_balance_minor' => 'Ledger balance', 'open_matters' => 'Open matters', 'pending_disbursements' => 'Pending disbursements', 'reconciliation_exceptions_minor' => 'Reconciliation difference'] as $key => $label)
                <div class="titan-assurance-metric"><span>{{ $label }}</span><strong data-titan-trust-count="{{ $key }}">—</strong></div>
            @endforeach
        </div>
        <p class="titan-help">Trust funds are segregated from operating money. Receipts, allocations, approvals, releases and corrections are append-only and separately permissioned.</p>
    </section>

    <div class="titan-grid titan-grid-main">
        <section class="titan-card titan-panel" data-titan-capabilities>
            <div class="titan-panel-heading">
                <div>
                    <p class="titan-eyebrow">Runtime</p>
                    <h2>Capabilities and extensions</h2>
                </div>
                <span class="titan-status-pill" data-titan-capability-count>Loading</span>
            </div>
            <div class="titan-list" data-titan-capability-list></div>
            <div class="titan-extension-list" data-titan-extension-list></div>
        </section>

        <section class="titan-card titan-panel">
            <div class="titan-panel-heading">
                <div>
                    <p class="titan-eyebrow">Titan Vault</p>
                    <h2>AI provider configuration</h2>
                </div>
                <span class="titan-status-pill {{ $aiConfiguration['configured'] ? 'is-ready' : '' }}">
                    {{ $aiConfiguration['configured'] ? 'Configured' : 'Not configured' }}
                </span>
            </div>

            @if ($canConfigureAi)
                <form method="POST" action="{{ route('titan.ai.configure') }}" class="titan-form">
                    @csrf
                    <label>
                        Provider
                        <select name="provider" required>
                            @foreach (['openai' => 'OpenAI', 'openrouter' => 'OpenRouter', 'gemini' => 'Google Gemini'] as $value => $label)
                                <option value="{{ $value }}" @selected($aiConfiguration['provider'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Model
                        <input name="model" required maxlength="160" value="{{ old('model', $aiConfiguration['model'] ?: 'gpt-4.1-mini') }}">
                    </label>
                    <label>
                        API key
                        <input name="api_key" type="password" autocomplete="new-password" maxlength="4096" placeholder="{{ $aiConfiguration['configured'] ? 'Leave blank to retain the Vault secret' : 'Enter provider key' }}">
                    </label>
                    <p class="titan-help">The key is encrypted in Titan Vault. It is never displayed again or stored in global settings.</p>
                    <button type="submit">Save secure AI configuration</button>
                </form>
            @else
                <p class="titan-muted">Only a company owner or authorised AI administrator can change this configuration.</p>
            @endif
        </section>
    </div>

    <section class="titan-card titan-panel titan-maps-panel" data-titan-maps-panel @if (! $mapsEnabled) hidden @endif>
        <div class="titan-panel-heading">
            <div>
                <p class="titan-eyebrow">Titan Maps Intelligence</p>
                <h2>Provider and business discovery</h2>
            </div>
            <span class="titan-status-pill is-ready">Enabled</span>
        </div>
        <form class="titan-form titan-maps-form" data-titan-maps-search>
            <label class="titan-field-wide">
                Search
                <input name="query" required maxlength="500" placeholder="Emergency plumber near Pascoe Vale">
            </label>
            <label>
                Purpose
                <select name="purpose">
                    <option value="provider_discovery">Provider discovery</option>
                    <option value="supplier_discovery">Supplier discovery</option>
                    <option value="sales_lead_discovery">Sales leads</option>
                    <option value="competitor_analysis">Competitor analysis</option>
                    <option value="accommodation_discovery">Accommodation providers</option>
                    <option value="emergency_sourcing">Emergency sourcing</option>
                </select>
            </label>
            <label>
                Maximum results
                <input name="maximum_results" type="number" min="1" max="100" value="20">
            </label>
            <label>
                Category
                <input name="category" maxlength="120" placeholder="plumber">
            </label>
            <label>
                Radius metres
                <input name="radius_metres" type="number" min="1" max="50000" value="15000">
            </label>
            <label class="titan-checkbox"><input name="open_now" type="checkbox"> Open now</label>
            <button type="submit">Start governed discovery</button>
        </form>
        <div class="titan-search-progress" data-titan-maps-progress aria-live="polite"></div>
        <div class="titan-candidate-grid" data-titan-maps-candidates></div>
    </section>

    @unless ($mapsEnabled)
        <section class="titan-card titan-panel titan-muted-panel">
            <p class="titan-eyebrow">Optional extension</p>
            <h2>Titan Maps Intelligence is installed but disabled</h2>
            <p>Enable the extension through the governed extension registry and add a provider credential through Titan Vault before location discovery becomes available.</p>
        </section>
    @endunless
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('js/titan-operations.js') }}"></script>
@endpush
