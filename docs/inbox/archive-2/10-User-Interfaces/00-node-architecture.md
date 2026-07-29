# Titan BOS — 9-Node + 12-Vertical Architecture

## Overview

Titan BOS is structured as **9 base nodes** (surface layer) riding on top of the Titan platform engine (module layer). Each node is a purpose-built interface for a specific role. The same backend modules power all nodes — the nodes are presentation and routing layers, not separate systems.

A **12-vertical overlay system** adapts every node to a specific service industry without duplicating logic.

---

## The 9 Nodes

| # | Node | Role | Primary User |
|---|---|---|---|
| 1 | **Titan Pro** | Owner Command Centre | Owner, Operator, Manager |
| 2 | **Ground Zero** | Dispatch Control Panel | Scheduler, Dispatcher |
| 3 | **Titan Go** | Field Worker App | Cleaner, Technician |
| 4 | **Zero Fuss** | Customer App | Client, End Customer |
| 5 | **Titan Zero** | AI Operating Assistant | All roles |
| 6 | **ZeroPay** | Financial Engine | Owner, Accounts |
| 7 | **Titan Studio** | Growth Surface | Owner, Marketing |
| 8 | **Titan Solo** | Single Operator Mode | Owner-operator |
| 9 | **Titan Hello** | Receptionist & Omni Gateway | All inbound channels |

---

## Node Role Map

```
                    ┌─────────────────────────────────┐
                    │        Titan BOS Platform        │
                    │   (Modules + Signals + Engine)   │
                    └────────────────┬────────────────-┘
                                     │
          ┌──────────────────────────┼──────────────────────────┐
          │                          │                          │
    ┌─────▼──────┐          ┌────────▼───────┐        ┌────────▼────────┐
    │ Titan Pro  │          │  Ground Zero   │        │   Titan Hello   │
    │  (Admin)   │          │  (Dispatch)    │        │   (Gateway)     │
    └─────┬──────┘          └────────┬───────┘        └────────┬────────┘
          │                          │                          │
    ┌─────▼──────┐          ┌────────▼───────┐        ┌────────▼────────┐
    │  ZeroPay   │          │   Titan Go     │        │   Zero Fuss     │
    │ (Finance)  │          │  (Field App)   │        │  (Client App)   │
    └─────┬──────┘          └────────┬───────┘        └────────┬────────┘
          │                          │                          │
    ┌─────▼──────┐          ┌────────▼───────┐        ┌────────▼────────┐
    │Titan Studio│          │  Titan Solo    │        │   Titan Zero    │
    │  (Growth)  │          │  (Solo Op)     │        │    (AI Core)    │
    └────────────┘          └────────────────┘        └─────────────────┘
```

---

## The 12 Verticals

Each vertical is an overlay that adapts node behaviour, checklists, compliance logic, AI context packs, and artefact generation — without duplicating backend logic.

| # | Vertical | Key differentiators |
|---|---|---|
| 1 | **Residential** | Regular maintenance, recurring schedules, key management |
| 2 | **Bond / End-of-Lease** | Condition reports, inspection packs, real estate compliance |
| 3 | **Commercial** | Site rotations, logbooks, security clearances, SLAs |
| 4 | **Construction / Builder's Clean** | Zone verification, SWMS, progressive handover stages |
| 5 | **Airbnb / Short-Stay** | Turnover windows, linen tracking, staging photos, guest readiness |
| 6 | **Biohazard / Trauma** | PPE compliance, COSHH, incident documentation, regulatory reporting |
| 7 | **Solar Panel Cleaning** | Weather validation, height safety, panel condition reporting |
| 8 | **Pool / Spa** | Water chemistry, equipment checks, service history |
| 9 | **Gardens / Grounds** | Seasonal scheduling, plant registers, irrigation checks |
| 10 | **Carpet / Upholstery** | Pre-inspection, drying time, warranty documentation |
| 11 | **Window Cleaning** | Height risk assessment, access equipment, streak reporting |
| 12 | **NDIS / Disability Support** | Participant plans, support ratios, incident reporting, funding codes |

---

## How Overlays Work

```
Base Node (e.g. Titan Go)
└── Vertical Overlay (e.g. Bond Cleaning)
    ├── Checklist set: bond cleaning room-by-room
    ├── AI context pack: bond cleaning standards, REIA requirements
    ├── Artefact: bond inspection report template
    ├── Compliance: condition report sign-off workflow
    └── Terminology: "bond pack", "entry condition", "final inspection"
```

Overlays inject into nodes via the vertical config without modifying base node code.

---

## Architectural Rules

- Each node is a **surface only** — all business logic lives in backend modules
- Nodes consume module APIs and signals — they never own domain data
- Vertical overlays attach to nodes via config, not code forks
- All nodes share the same Titan Zero AI layer
- All nodes respect the Zero Philosophy (see `docs/philosophy/00-zero-philosophy.md`)

---

## Module → Node Mapping

| Backend Module | Primary Node(s) |
|---|---|
| BookingModule | Titan Pro, Ground Zero, Zero Fuss, Titan Go |
| ManagedPremises | Titan Pro, Titan Go, Ground Zero |
| Payroll | Titan Pro, Titan Solo |
| CleanQuality | Titan Pro, Titan Go, Ground Zero |
| ClientFeedback | Zero Fuss, Titan Pro |
| TitanReach | Titan Hello, Zero Fuss |
| TitanHello | Titan Hello node surface |
| Accountings / EInvoice | ZeroPay |
| TitanZero | Titan Zero node + all nodes (AI layer) |
| TitanGo | Titan Go node surface |
| TitanTalk | Titan Pro, Ground Zero |
| TitanIntegrations | Titan Pro, ZeroPay, Titan Studio |
| CleanerRecruit | Titan Pro |
| Performance | Titan Pro, Ground Zero |
| SupplyChain | Titan Pro, Titan Go |
| TitanDocs | Titan Pro, Zero Fuss, Titan Go |
| Webhooks | TitanIntegrations bridge |
