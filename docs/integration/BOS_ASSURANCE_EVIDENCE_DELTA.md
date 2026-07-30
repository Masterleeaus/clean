# BOS Assurance and Evidence Delta

## Purpose

This pass reconciles the compliance, quality, trust, document and evidence concepts found in the supplied Titan BOS module archive against the canonical Meetup + Titan Zero + WorkCore application.

The BOS archive was treated as a capability and domain-design donor. Its duplicate company models, routes, finance logic, storage authorities and module-specific tenancy were not imported into runtime.

## Accepted capability delta

Two missing first-party WorkCore domains were added.

### Documents and evidence

Canonical runtime path:

`app/Domains/WorkCore/System/Modules/Documents`

The module owns:

- company-scoped document metadata
- immutable document versions
- operational record links
- comments
- approval requests and decisions
- evidence registration
- evidence sign-offs
- append-oriented evidence chain events
- document and evidence search read models

Binary content remains on the host private-storage authority. WorkCore stores storage references, hashes, metadata and provenance rather than creating a competing file service.

### Assurance

Canonical runtime path:

`app/Domains/WorkCore/System/Modules/Assurance`

The module owns:

- reusable inspection templates and template items
- scheduled and ad hoc inspections
- inspection results and weighted scoring
- nonconformance findings
- corrective actions
- 5×5 operational risk records
- incidents
- assurance summaries and search read models

The rules are vertical-neutral. Cleaning, facilities, property, accommodation, trade and field-service verticals consume the same assurance records rather than duplicating quality systems.

## Integrated trust rules

- Evidence hashes use deterministic SHA-256 canonical payloads.
- Evidence without a valid checksum cannot be signed off.
- Void evidence cannot be signed off.
- Every evidence sign-off is append-only.
- Chain events preserve actor, time, action and payload hash.
- Critical inspection failures force a failed inspection outcome.
- Failed inspection items create nonconformance findings once.
- High and critical findings require due dates.
- Risk score equals likelihood multiplied by consequence, with bounded 1–5 inputs.
- Company identifiers in action and read payloads are rejected.

## Governed read execution

WorkCore read models were previously registered and advertised to Titan but lacked a shared execution boundary. This pass adds `ReadModelExecutor` and the protected API route:

`POST /api/titan/workcore/reads/{read}`

Every read now requires:

- an authenticated host user
- active company middleware
- a registered read-model definition
- company capability entitlement
- delegated permission where declared
- server-resolved company and actor identifiers
- bounded pagination

Titan Zero's tool router uses the same executor. API and AI reads therefore cannot diverge in tenancy, entitlement or permission behaviour.

## Rejected or quarantined donor areas

The following BOS material remains outside runtime:

- duplicate company, user, role and tenant authorities
- duplicate document storage services
- duplicate route sets and standalone dashboards
- duplicate work-order, asset, customer and provider models
- module-local audit ledgers that conflict with Titan Audit
- finance, settlement and trust-account ownership
- incomplete accommodation, NDIS and regulated-care workflows
- compiled or generated artefacts not needed by the Laravel host

## Database additions

The pass adds two migrations and sixteen company-scoped tables.

### Document and evidence tables

- `tz_documents`
- `tz_document_versions`
- `tz_document_links`
- `tz_document_comments`
- `tz_document_approvals`
- `tz_evidence_items`
- `tz_evidence_signoffs`
- `tz_evidence_chain_events`

### Assurance tables

- `tz_inspection_templates`
- `tz_inspection_template_items`
- `tz_inspections`
- `tz_inspection_results`
- `tz_assurance_findings`
- `tz_corrective_actions`
- `tz_risk_register`
- `tz_incidents`

## Current verification boundary

The dependency-free suites verify schema structure, registration, tenant rejection, hashing, sign-off policy, scoring, risk classification, UI exposure and read execution wiring. Live Laravel boot, migration execution, database constraints and queue behaviour still require Composer dependencies and configured SQLite/PostgreSQL test databases.
