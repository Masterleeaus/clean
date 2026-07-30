# Tenancy, Trust and Governed Action Execution

**Status:** Canonical security and execution model for Titan Zero operational actions.

## Purpose

Titan Zero spans web sessions, APIs, PWAs, local/offline state, AI orchestration, channels, queues and integrations. Authentication alone is not enough. Every operational action must carry explicit tenant, actor, capability, trust, confirmation and audit context.

## Canonical identities

| Identity | Meaning | Never substitute with |
|---|---|---|
| `company_id` | Operational tenant boundary | User ID, device ID or visible UI selection |
| actor/user ID | Human or system principal performing the action | Tenant identity |
| device ID | Registered endpoint participating in local/offline operation | User or tenant identity |
| channel/integration ID | External delivery or callback principal | Human identity unless explicitly bound |
| correlation ID | End-to-end execution trace | Idempotency key |
| causation ID | Immediate parent action/event | Correlation ID |
| idempotency key | Safe replay identity for a mutation request | Permission or confirmation |

## Host-to-WorkCore tenant resolution

The MagicAI host authenticates the user and owns company/membership lifecycle. WorkCore resolves a requested company and verifies active membership before establishing its scoped tenant context.

The current resolver considers, in order:

1. the configured company request header;
2. the configured session company key;
3. the authenticated user's active-company attribute;
4. automatic selection when exactly one active WorkCore membership exists.

A candidate company is accepted only when the authenticated user has an active matching membership. Failed resolution must not fall back to another tenant.

## Required operational context

Before an operational command is dispatched, the runtime must know:

- company/tenant ID;
- actor ID and actor type;
- source surface or channel;
- device ID when relevant;
- requested capability and permission;
- correlation and causation IDs;
- confirmation evidence when required;
- idempotency key for replayable mutations;
- target record tenant where a record already exists.

Queued, scheduled and offline-replayed work must restore the same context rather than infer a new one at execution time.

## Guard sequence

WorkCore's governed dispatcher establishes the required order:

1. resolve the registered action definition;
2. require tenant context to match the request company and actor;
3. require operation context to match the request company and actor;
4. verify tenant capability entitlement;
5. verify actor permission;
6. verify explicit confirmation when the action definition requires it;
7. return a completed prior result when the idempotency store recognises a safe replay;
8. reserve the idempotency request;
9. run the action handler inside a database transaction;
10. record domain events;
11. record completed audit evidence;
12. complete the idempotency record and return the correlated result.

Failures are recorded where possible and must preserve the original policy decision even when secondary audit persistence fails.

## Trust is additional to permission

Permission answers whether an actor may request an action. Trust answers whether the current environment is acceptable for the action's risk.

Recommended trust states for device and channel execution are:

- `untrusted`;
- `registered`;
- `verified`;
- `high_trust`;
- `restricted`;
- `revoked`.

The current source does not yet prove a single fully wired trust-state enforcement service across every surface. Until that exists, trust requirements are an implementation obligation, not a claim of completed coverage.

## Confidence is not authority

AI confidence, prediction quality or Interaction Engine evidence may affect whether clarification or abstention is required. It must never:

- grant a missing permission;
- widen tenant scope;
- bypass entitlement;
- satisfy a required human confirmation by itself;
- bypass idempotency, audit or domain-event recording;
- convert an offline draft into an authoritative server mutation without WorkCore acceptance.

## Risk and confirmation classes

Suggested action classes:

| Class | Examples | Minimum handling |
|---|---|---|
| Read-only | Search, summaries, dashboards | Tenant + permission checks |
| Safe draft | Draft note, proposed response, unsubmitted form | Tenant + permission; remain non-authoritative |
| Operational mutation | Create/update customer, job, schedule or form | Full WorkCore dispatch and idempotency |
| Financial | Quote acceptance, invoice mutation, payment allocation | Full dispatch plus explicit confirmation and strong audit |
| Compliance/high-impact | Incident, certification, payroll, bulk communication | Full dispatch, approval policy and stronger trust/re-authentication where configured |
| Irreversible/destructive | Delete, void, bulk close, credential revocation | Explicit confirmation, policy gate and durable audit |

## Route and API rules

- Tenant-sensitive record binding must resolve within company scope.
- Operational APIs must use authenticated, tenant-aware middleware.
- Rate limits must be keyed by company plus actor or device, not by IP alone.
- Chat and AI tools use the same action policies as web and API surfaces.
- External callbacks require provider/integration identity, signature verification and replay protection.
- UI visibility is not an authorisation boundary.

## Offline and PWA rules

The device runtime may own drafts, projections and pending commands. It may not declare an operational mutation successful until the server accepts it.

Every pending command must retain:

- client-generated UUID;
- company, actor and device IDs;
- action key and schema version;
- correlation and idempotency identifiers;
- local creation time;
- confirmation evidence or confirmation-required state;
- retry and conflict status;
- server acknowledgement when accepted.

Secrets and sensitive provider responses must never enter service-worker Cache Storage.

## Audit questions

Every sensitive action should answer:

- who initiated it;
- which company was targeted;
- which surface, device, channel or integration originated it;
- which entitlement and permission were evaluated;
- whether AI proposed or shaped it;
- what confidence, policy and confirmation decisions occurred;
- which handler executed;
- which domain events were emitted;
- whether the result was new or an idempotent replay;
- whether the action completed, failed or was denied.

## Known implementation gaps

The current source provides strong WorkCore tenant and action-dispatch foundations, but these items still require connected proof:

1. cross-tenant route/model-binding tests for every operational module;
2. queue and scheduler context-restoration tests;
3. one consistent device-trust enforcement service;
4. connected Interaction Engine activation and confirmation handoff;
5. offline command replay through the same WorkCore dispatcher;
6. signed and replay-protected external callback coverage;
7. durable failure-outbox handling for audit/idempotency persistence failures;
8. proof that embedded chatbot runtimes cannot become alternate mutation paths.

## Final rule

An action is executable only when tenant, actor, entitlement, permission, trust, policy, confirmation and replay safety all agree. No single signal—including authentication or AI confidence—is sufficient on its own.
