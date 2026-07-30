# WorkCore Operational Authority — Agent OS View

```yaml
source:
  type: derived
  canonical_path: docs/architecture/TITAN_ZERO_AUTHORITY_MAP.md
  supporting_sources:
    - docs/architecture/TENANCY_TRUST_AND_ACTION_EXECUTION.md
    - external:WorkCore Technical Architecture Specification.txt
    - external:Workcore.txt
status: active
owner: architecture-authority
last_verified: 2026-07-30
```

## Authority

WorkCore is the sole operational business domain. It owns structured business records, business permissions, validation, governed actions, transactions, operational audit and domain events.

Titan Zero, Claude, ChatGPT agents, Interaction Engine, Chatbot/PWA, extensions and external providers may propose or request actions. They may not directly replace WorkCore’s business mutation boundary.

## Required mutation path

```text
Human or agent intent
→ authenticated host actor and company context
→ Titan Zero / Interaction Engine planning and clarification
→ registered WorkCore action
→ entitlement
→ permission
→ confirmation when required
→ idempotency
→ transactional execution
→ audit and immutable domain events
→ response and synchronisation projection
```

## Context requirements

Every action carries trusted server-derived company and actor context plus relevant device, channel, conversation, correlation and causation identifiers. Request-body tenant identifiers are never treated as authority.

## Agent rule

AI confidence cannot grant entitlement, permission or confirmation. Agents execute with delegated authority and must fail closed when a required action, policy, context or validator is missing.

## Offline rule

Device-local state is a projection and work queue, not a second canonical server authority. Unsynchronised records, conflicts and attachments are preserved. Replayed mutations remain tenant-scoped, authorised, version-aware and idempotent.

## Current evidence caution

The uploaded WorkCore reports describe substantial architecture and a valuable governed dispatcher, but also record unresolved provider authority, missing imports, migration faults, vertical-directory drift, incomplete AI activation and incomplete local-first execution. Those reports are references for planning and validation; current repository source and canonical `/docs` architecture remain authoritative.
