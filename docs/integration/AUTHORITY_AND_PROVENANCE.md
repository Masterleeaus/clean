# Authority and Provenance

## Purpose

This document records which supplied source owns each part of the integrated Titan Zero application and which material was deliberately excluded from runtime.

## Canonical authority order

1. **Meetup Laravel application** is the executable host and owns authentication, users, company membership, active-company context, conversations, realtime channels, shared infrastructure, private files, queues and the user interface.
2. **Titan Zero host services** own encrypted credentials, audit records, capability discovery, extension discovery and AI orchestration.
3. **Titan Intelligence Runtime** owns company-scoped AI workspace records, memory, skills, agent definitions, connector/provider configuration, routing policy, voice sessions, announcements and onboarding state. It does not own conversations or WorkCore records.
4. **Titan Creative & Marketing** owns company-scoped brand systems, creative projects, templates, generated-asset records, campaigns, content calendars, approvals, publication plans, newsletters, SEO briefs, automation definitions and analytics observations. It does not own CRM contacts, invoices, conversations, credentials or provider execution.
5. **WorkCore** is a first-party domain under `app/Domains/WorkCore` and permanently owns structured operational records and business rules.
6. **Titan Maps Intelligence** is an optional integration extension under `app/Extensions/TitanMapsIntelligence`. It stages external candidates and can request promotion only through WorkCore gateways.
7. **Donor archives** are reference material only unless a capability is explicitly rebuilt and documented.

## Supplied source inventory

| Source | Use in this build | Runtime status |
|---|---|---|
| User-supplied `source_code.zip` | Authoritative Laravel 12 Meetup base | Integrated as application root |
| Project WorkCore v0.48 source | Canonical `System` runtime, selected migrations, vertical manifests and contracts | Rebased to `App\\Domains\\WorkCore` |
| Titan Zero Extension SDK v2 | Manifest and extension-boundary conventions | Used as architectural authority |
| Titan Maps Intelligence v1.0.0 package | Provider-neutral maps extension and tests | Integrated, disabled by default |
| Base App System Extensions archive | Capability inventory reconciled into native Titan Intelligence, optional adapters, deferrals and rejections | Original donor code excluded from runtime |
| AI System Extensions archive | Capability inventory reconciled into native Titan Intelligence, optional adapters, deferrals and rejections | Original donor code excluded from runtime |
| Marketing & Creative Extensions archive | Capability inventory reconciled into native Titan Creative & Marketing, disabled adapters and rejected duplicate authorities | Original donor code excluded from runtime |
| Modules for Titan BOS archive | Assurance, Evidence and selected operational concepts reconciled in earlier passes | Remaining donor code excluded from runtime |
| Compiled Google Maps scraper reference | Functional concept reference only | Never included or copied |

## Integrated file families

- `app/Titan/**`: host tenancy, Vault, audit, capabilities, extension manager, AI orchestration, native Titan Intelligence, native Titan Creative & Marketing, Maps and WorkCore adapters.
- `app/Domains/WorkCore/**`: repaired canonical WorkCore runtime.
- `app/Extensions/TitanMapsIntelligence/**`: optional maps integration.
- `database/migrations/2026_07_25_000*.php`: host foundation.
- `database/migrations/2026_07_25_01*.php`: 27 vetted WorkCore migrations.
- `database/migrations/extensions/titan_maps/**`: 13 Maps migrations.
- `resources/workcore/verticals/**`: 20 vertical metadata manifests.
- `routes/titan.php`: governed company-scoped APIs.
- `resources/views/titan/**` and `resources/js/titan/**`: Titan operations interface.

## Excluded and quarantined material

The following material is not in runtime autoload:

- WorkCore `IntegratedSources` donor copies using global `App\\Models` or controller namespaces.
- Embedded or incomplete Titan Rewind implementation fragments.
- Incomplete WorkCore Intelligence and native AI kernel branches.
- Unreconciled jurisdiction-specific trust, bond, statutory finance and NDIS execution paths.
- Original Marketing and Creative donor code; only clean native capability implementations entered runtime.
- Provider-specific image, video, audio, publishing, newsletter, SEO and social adapters remain disabled until independently activated.
- Original Base App and AI donor code after capability-level reconciliation; only clean native Titan implementations entered runtime.
- Compiled executables, DLLs, PDB files, vendor telemetry and decompilation output.

## Non-negotiable ownership rules

- Request body `company_id` values are never tenant authority.
- Titan Zero and extensions do not update WorkCore tables directly.
- WorkCore is not represented as an optional extension.
- WorkCore Finance owns invoices and accounting records; ZeroPay owns payment orchestration and reconciliation only.
- Trust accounting is a segregated, separately permissioned WorkCore ledger.
- Credentials are stored only as encrypted Vault records; company settings retain references.
- Optional extensions boot only when their manifest is compatible and their registry record is enabled.
- Titan Intelligence is a first-party host subsystem, not an extension and not a replacement for Meetup conversations or WorkCore operational truth.
- Missing dependencies disable a capability rather than shifting ownership to another component.
- Titan Creative & Marketing cannot create or alter WorkCore CRM, finance, trust, booking or operational records directly.
- Creative generation and publication adapters resolve provider or connector references through Titan Intelligence and Titan Vault; no provider secret belongs in Creative persistence.

## Finance authority added in v0.4.0

- `app/Domains/WorkCore/System/Modules/Finance/**`: quotes, invoices, credits, expenses, receivables, periods, accounts and journals.
- `app/Domains/WorkCore/System/Modules/Payments/**`: ZeroPay sessions, observations, matching and reconciliation.
- `app/Domains/WorkCore/System/Modules/TrustAccounting/**`: trust accounts, matters, receipts, approvals, disbursements and ledger corrections.
- `database/migrations/2026_07_26_040000_create_tz_finance_payment_trust_tables.php`: company-scoped finance, payment and trust schema.

## Titan Intelligence authority added in v0.5.0

- `app/Titan/Intelligence/**`: native workspace, memory, skills, agents, connector/provider records, routing, voice, announcements and onboarding.
- `database/migrations/2026_07_26_050000_create_titan_intelligence_runtime_tables.php`: company-scoped intelligence persistence.
- `config/titan_intelligence.php`: connector/provider allowlists and all 75 Base App and AI donor decisions.
- `docs/integration/BASE_AI_EXTENSION_RECONCILIATION.md`: evidence and merge decisions.
- Provider, connector and voice credentials remain Titan Vault references.
- Agent tools must be registered Titan capabilities or governed WorkCore actions/reads.


## Titan Creative and Marketing authority added in v0.6.0

- `app/Titan/Creative/**`: native brand, campaign, creative-project, generation, approval, publication, newsletter, SEO, automation and analytics execution.
- `database/migrations/2026_07_26_060000_create_titan_creative_marketing_tables.php`: 21 company-scoped Creative and Marketing tables.
- `config/titan_creative.php`: provider-adapter definitions and all 30 Marketing and Creative donor decisions.
- `docs/integration/MARKETING_CREATIVE_RECONCILIATION.md`: accepted concepts, rejected authorities and activation boundary.
- Private asset binaries remain in authorised private storage; database records retain private storage references.
- Provider and publication connectors remain disabled until credentials, scopes, terms and integration tests are complete.
- Campaign budget values use integer minor units with `AUD` as the default currency; WorkCore Finance remains the accounting authority.


## Deployment verification added in v0.7.0

- `docker-compose.yml` and `docker/php/**`: reproducible PHP 8.4, Node 22 and PostgreSQL verification environment.
- `bin/titan-preflight`: required command, extension, writable path and production-key checks.
- `bin/titan-verify-offline`: complete dependency-free regression and lint suite.
- `bin/titan-verify-connected`: Laravel boot, migration, rollback, queue, schedule, framework-test and frontend-build acceptance command.
- `tools/titan_migration_order.php`: migration foreign-reference order verification.
- `tools/titan_route_provider_scan.php`: route/bootstrap App-class resolution verification.
- `.github/workflows/titan-verify.yml`: connected CI acceptance workflow.

Deployment tooling does not create a new business authority and cannot bypass tenancy, Vault, audit, capability or WorkCore governance.
