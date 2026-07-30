# Titan Zero Integrated Upgrade Design

## Purpose

Prepare one controlled development line that turns the imported MagicAI, WorkCore, chatbot/PWA, five-tier AI, Interaction Engine material and extension inventory into a coherent Titan Zero home and field service operating system.

## Source model

The branch starts from source commit `a76eee53af7b72b9f740adb3fa757b3f4d527bd6`. That commit contains the expanded application source and imported extension tree. The current default branch is not treated as the source authority for this work because it was reinitialised after the source import.

## Architectural authorities

### MagicAI host

MagicAI remains responsible for tenancy, users, authentication, plans, the host application lifecycle and shared UI infrastructure.

### WorkCore

`app/Domains/WorkCore` is the authoritative operational domain. It owns operational records and action semantics for customers, work, scheduling, jobs, tasks, quotes, invoices, workforce, property and commercial operations.

### Interaction Engine

Reusable interaction, wizard, confidence, authority and execution logic converges into `app/Domains/InteractionEngine`. It may depend on WorkCore contracts and host adapters, but it must not depend on chatbot presentation or device-storage implementations.

### Chatbot extension

`app/Extensions/Chatbot` owns chatbot routes, views, channels, PWA/device adapters, generative UI presentation and compatibility bridges to the five-tier AI runtime. Its current service provider must be decomposed into focused providers while preserving behaviour.

### Extension catalogue

The imported `app/Extensions` tree is a catalogue, not an enablement list. Extensions are scanned without execution, validated, classified as Green/Amber/Red/Quarantined and enabled progressively.

## Data and execution flow

```text
User or device
  → MagicAI authentication and tenant context
  → Chatbot/operational UI
  → Interaction Engine decision and authority pipeline
  → five-tier AI capability routing where needed
  → WorkCore action or read model
  → audit/outbox/domain event
  → generative UI or operational response
  → encrypted local storage/outbox when offline
  → idempotent synchronisation when connectivity returns
```

Every state-changing path carries tenant, user, device, correlation and idempotency identifiers.

## Interface design

The product uses one responsive application shell rather than a separate dashboard and chatbot application. The shell contains:

1. A compact header with Titan Zero identity and device/connectivity state.
2. A persistent chat/input surface.
3. A primary operational workspace driven by the selected WorkCore capability.
4. Contextual navigation derived from permissions, capability availability and extension health.
5. A gear control for settings and a responsive hamburger for secondary links.

Build Web Apps work extends the existing Blade, React, Alpine, Vite and Tailwind structure. It does not create a new standalone frontend.

## Failure and safety design

- Boot flags allow WorkCore, chatbot, Interaction Engine and extension discovery to be repaired independently.
- Provider discovery validates manifests and class existence before executing extension code.
- Offline mutations remain local until acknowledged by an idempotent server operation.
- Amber authority decisions require confirmation; Red decisions block or escalate.
- Quarantined extensions cannot contribute routes, providers, migrations or menu items.
- Queue failures retain tenant context and move poison messages to reviewable failed states.
- Credentials remain outside logs, browser caches and ordinary synchronisation payloads.

## Testing strategy

Testing proceeds from narrow to broad:

1. Source and dependency architecture tests.
2. Laravel boot and provider graph tests.
3. WorkCore contract and tenancy tests.
4. Interaction Engine ownership and authority tests.
5. Chatbot/five-tier AI execution tests.
6. Offline PWA and synchronisation tests.
7. Extension-family qualification tests.
8. Build Web Apps browser tests for mobile, tablet and desktop.
9. Security, queue and release tests.

## Delivery design

Each programme pass produces a reviewable commit series and an independently testable result. Release archives are produced only from tagged commits after CI succeeds. The default branch is updated only through a reviewed merge after the complete source baseline and branch history are confirmed.
