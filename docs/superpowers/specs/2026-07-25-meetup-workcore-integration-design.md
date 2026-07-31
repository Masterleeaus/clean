# Meetup + WorkCore Canonical Integration Design

## Status
Approved by the user instruction to use the supplied Meetup source as the application base and merge WorkCore and related Titan components into it.

## Goal
Turn the supplied Laravel 12 Meetup chat application into the authoritative Titan Zero host while integrating a repaired, first-party WorkCore operational domain and SDK-governed optional extensions.

## Authority boundaries

- **Meetup/Titan host:** authentication, users, companies, memberships, active-company context, permissions, conversations, realtime messaging, shared files, queues, notifications, extension discovery, Vault, audit and capability registration.
- **Titan Zero:** conversational orchestration and AI delegation. It may invoke registered WorkCore actions and extension tools but may not write operational tables directly.
- **WorkCore:** customers, contacts, premises, work orders, appointments, dispatch, workforce, assets, inventory, forms, evidence and operational workflows.
- **Optional extensions:** provider and integration capabilities loaded through the host registry. They do not own tenancy or operational records.
- **Quarantined donors:** WorkCore `IntegratedSources`, partial TitanRewind fragments, and unselected BOS/AI extension archives remain outside runtime autoload until individually reconciled.

## Chosen approach

Use a controlled in-place host integration rather than a blind file copy or Composer path-package conversion.

1. Preserve the supplied Meetup application as the Laravel root.
2. Add host-owned company, membership, permission, active-context, Vault, audit and capability services.
3. Add an SDK-compatible extension registry that discovers manifests and boots only enabled compatible extensions.
4. Rebase the canonical WorkCore `System` namespace from `App\\Extensions\\WorkCore` to `App\\Domains\\WorkCore` and install it under `app/Domains/WorkCore`.
5. Import only the vetted root WorkCore migrations needed for the initial property and field-service runtime.
6. Replace MagicAI-specific tenancy, permission, menu, notification, storage and entitlement defaults with Meetup host adapters.
7. Install Titan Maps Intelligence as an optional integration extension and bind it to host Vault, company context, permissions, audit, storage and WorkCore gateway contracts.
8. Add chat-facing APIs and dashboard surfaces that expose current company context, operational summaries, action/capability inventory and extension status.

## Initial WorkCore runtime scope

Enabled in the first merged application:

- companies and memberships through the host;
- governed actions, confirmation, idempotency, audit and outbox;
- CRM customers, contacts and leads;
- premises and service locations;
- service catalogue;
- work orders and tasks;
- appointments and dispatch;
- workers, skills, availability and rosters;
- assets and equipment;
- inventory and materials;
- forms, checklists and evidence;
- attendance and leave;
- support, knowledge and reviews;
- supply, fleet and repair foundations;
- property, cleaning, field-service and trade vertical manifests.

Disabled or quarantined until dependencies are complete:

- donor `IntegratedSources` classes in global `App\\Models`, controllers and notifications;
- partial embedded TitanRewind branch execution;
- incomplete finance/Titan Money ownership;
- incomplete NDIS and accommodation execution paths if unresolved imports remain;
- native AI kernel tools whose actions or read models are absent;
- modules whose service providers or record contracts are missing.

## Data flow

1. Authentication resolves a user.
2. Host active-company middleware resolves company context from server-side membership, never from request body authority.
3. Titan Zero or UI requests a registered action/read model/tool.
4. Permission and capability gates run.
5. WorkCore performs validated operational work transactionally.
6. Audit, domain-event and outbox records are written.
7. Optional extensions stage external data and request WorkCore promotion through contracts.
8. Conversation/UI receives a safe structured result.

## Security

- Company IDs in request bodies are never authoritative.
- Cross-company queries are denied by default.
- Secrets are encrypted with Laravel Crypt and returned only through scoped resolver contracts.
- Extension providers boot only when enabled and compatible.
- Raw provider errors and secrets are not returned to clients.
- Operational mutations require registered permissions and confirmation where risk requires it.
- Public maintenance routes from the original Meetup app are removed.
- Attachments and exports use private storage and authorised download paths.

## Error handling

- Missing active company returns a stable `TENANT_CONTEXT_REQUIRED` response.
- Incompatible or invalid extensions remain disabled and appear in diagnostics.
- Incomplete WorkCore modules remain disabled rather than causing application boot failure.
- External provider errors are normalised.
- Failed actions retain correlation, causation and audit identifiers.

## Verification

Because the source package contains no `vendor` directory and Composer may not be available, verification is layered:

1. PHP syntax validation for every runtime PHP file.
2. Static namespace/import resolution for the integrated runtime.
3. Manifest schema and collision checks.
4. Migration identifier, ordering and duplicate-table checks.
5. Custom executable integration guards.
6. Laravel/Pest tests when Composer dependencies can be installed.
7. NPM/Vite build when JavaScript dependencies can be installed.

No claim of a successful Laravel boot or migration run will be made unless those commands execute successfully.

## Deliverable

A complete merged ZIP containing the authoritative Meetup base, repaired WorkCore runtime, host foundation, extension system, Titan Maps Intelligence integration, tests, diagnostics, documentation and a precise remaining-issues report.
