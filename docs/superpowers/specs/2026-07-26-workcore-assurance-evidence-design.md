# WorkCore Assurance and Evidence Design

## Objective

Add canonical WorkCore-owned document, evidence, inspection, risk, incident, finding and corrective-action capabilities to the Meetup/Titan Zero application. The implementation closes gaps identified in the WorkCore architecture while preserving existing form evidence, work-order evidence, tenant authority, audit, action-dispatch and extension boundaries.

## Donor provenance

The following Titan BOS packages were analysed as design and delta sources: `compliance-auditing`, `CleanQuality`, `QualityControl`, `TitanTrust`, `TitanVault`, `TitanDocs`, `Documents`, and `StaffCompliance`.

Useful structures are normalised into WorkCore. Donor tenancy, permissions, routes, finance assumptions, duplicate document stores, generated placeholder tests, Filament surfaces and direct host table writes are not imported.

## Authority

- WorkCore owns assurance records and operational documents.
- Meetup owns chat, notifications and presentation.
- Titan Zero orchestrates actions through the WorkCore action and read-model registries.
- Titan Vault continues to own secrets; the new document domain is operational content, not credential storage.
- Existing `tz_form_evidence` and `tz_work_order_evidence` remain valid source records and can be linked into the canonical evidence register.

## Modules

### Documents

Owns document metadata, immutable versions, entity links, comments, approval decisions, evidence records and evidence sign-offs. Binary content remains in private host storage; WorkCore stores only governed metadata and checksums.

### Assurance

Owns inspection templates, inspections, inspection results, findings, corrective actions, risk records and incidents. Failed inspection items may create findings; findings may create governed Work Orders through existing WorkCore operations.

### Integrity

Canonical payload hashing uses deterministic JSON normalisation. Evidence sign-off stores the signed checksum and rejects a sign-off if evidence has no checksum or is no longer signable.

## Data flow

1. Titan Zero or UI submits a governed action.
2. WorkCore dispatcher supplies active company and actor context.
3. Module repository validates company-scoped references.
4. Repository writes inside a transaction.
5. Action emits a domain event and audit entry.
6. Read models expose company-scoped summaries and search results.

## Safety rules

- No request may supply authoritative `company_id`.
- Every table is company scoped.
- Cross-company references are rejected.
- Evidence storage paths are metadata only and must refer to private host storage.
- Checksums are SHA-256 hex values.
- Approval and sign-off history is append-only.
- Completed inspections are immutable except through explicit reopen actions.
- Risk score is likelihood × consequence, each constrained to 1–5.
- High and critical findings require corrective-action due dates.
- No donor routes or service providers enter runtime autoload.

## Deliverables

- Two WorkCore modules and providers.
- Canonical migrations.
- Actions, repositories, services and read models.
- Titan capabilities and permissions.
- Operations summary integration.
- Standalone behaviour and structural verification.
- Donor delta report and v0.2.0 release package.
