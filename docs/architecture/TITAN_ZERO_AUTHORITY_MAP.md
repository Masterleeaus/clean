# Titan Zero Authority Map

**Status:** Canonical architecture boundary for the current reconciliation programme.

This document separates platform identity, operational authority, interaction governance, AI orchestration and device presentation. A component may consume another component's context without becoming the authority for that context.

## Ownership map

| Component | Owns | Consumes | Must not own |
|---|---|---|---|
| **MagicAI host** | Authentication, users, company and membership lifecycle, active-company selection, subscriptions, platform billing, provider configuration, queues, notifications, extension lifecycle and application shell | WorkCore capabilities and operational read models | Operational domain rules or a second WorkCore mutation path |
| **WorkCore** | Operational records, business rules, operational permissions, governed mutations, transactions, domain events, operational audit, outbox and server sync contracts | Host user, company and membership context | Platform subscription billing, host account lifecycle or presentation ownership |
| **Interaction Engine** | Interaction definitions, sessions, transitions, clarification, confidence, evidence, abstention, approval preparation, wizard execution and command preparation | Host identity, WorkCore capabilities and Titan Zero plans | Direct operational writes, tenant authority or permission authority |
| **Titan Zero intelligence** | Intent, planning, orchestration, delegation, tool selection, model routing, governance and memory coordination | Interaction and WorkCore contracts | Operational business truth or permission escalation |
| **Chatbot/PWA** | Conversation and channel presentation, generative UI, local projections, drafts, device vault, offline state, outbox and sync UX | Host identity, WorkCore APIs/read models and Interaction Engine state | Canonical server records, host identity or financial ledger authority |
| **Titan Money / payment surfaces** | Operational finance workflows, payment sessions, provider observations, settlement and reconciliation within WorkCore governance | Host identity and provider callbacks | MagicAI subscription billing or an independent invoice/ledger authority |
| **Extensions** | Optional capabilities and adapters | Host and WorkCore contracts | Replacement identity, tenancy, permission, messaging, WorkCore or vault systems |

## Identity and tenant distinction

The host owns the lifecycle of users, companies and memberships. WorkCore does not create a parallel tenant authority; it resolves and validates a normalised operational context from the host.

For operational work:

- `company_id` identifies the tenant boundary;
- `user_id` or actor ID identifies who is acting;
- device ID identifies the participating endpoint;
- correlation and causation IDs identify the execution chain.

A user ID must never be treated as a substitute for tenant identity.

## Operational mutation boundary

All operational mutations must follow this path:

```text
User, device, AI, channel or integration
        ↓
MagicAI authentication + active company/membership context
        ↓
Titan Zero planning / Interaction Engine clarification and approval preparation
        ↓
WorkCore BusinessActionDispatcher
        ↓
Tenant and operation-context match
        ↓
Capability entitlement
        ↓
Actor permission
        ↓
Explicit confirmation when required
        ↓
Idempotency replay/reservation
        ↓
Transactional action handler
        ↓
Domain events + audit + outbox/synchronisation
        ↓
Result returned to the originating surface
```

No chatbot controller, AI agent, PWA repository, integration callback or extension may write operational tables directly as an alternative to this boundary.

## Permission, confidence and approval

These are separate decisions:

- **Permission:** whether the actor is allowed to request the action.
- **Entitlement:** whether the tenant has access to the capability.
- **Confidence:** how certain the AI or Interaction Engine is about intent or evidence.
- **Device trust:** whether the endpoint is acceptable for the requested risk level.
- **Confirmation:** whether the user has explicitly approved a consequential action.
- **Policy:** whether the action is allowed in the current domain, state and risk context.

High confidence does not create permission. Permission does not remove confirmation requirements.

## Current source status

### Confirmed and wired

- `App\Providers\TitanZeroServiceProvider` stages core providers through feature flags.
- WorkCore is enabled by default and registered through `App\Domains\WorkCore\WorkCoreServiceProvider`.
- WorkCore provides scoped tenant and operation contexts, capability and tenant middleware, governed action dispatch, entitlement, permission, confirmation, idempotency, audit and domain-event contracts.
- Chatbot activation is independently feature-gated and disabled by default.

### Source present but activation not yet proven

- The Interaction Engine package exists under `packages/titanzero/interaction-engine`.
- Its provider and tests exist.
- The root `composer.json` currently does not register the package as a path repository or require it.
- `interaction_engine_enabled` exists in `config/titan-zero.php`, but `TitanZeroFeatureFlags::coreProviderClassNames()` currently does not add the Interaction Engine provider.

Therefore the Interaction Engine must be described as **source-present and intended**, not as a verified active host service, until dependency registration, provider activation, Laravel boot, routes and tests pass from a clean checkout.

## Compatibility rule

The repository contains embedded or copied runtimes under chatbot paths. These may be used as device code, compatibility shims or extraction references, but they must not shadow the canonical host WorkCore namespace or create a second active operational authority.

## Final rule

Every capability has one owner. Other components may present, propose, coordinate, observe or synchronise that capability, but they may not silently assume its authority.
