# Vertical Overlay Architecture

## What Verticals Are

A vertical overlay adapts every Titan BOS node to a specific service industry without forking base node code. Overlays are configuration — not separate codebases.

```
Base Platform (fixed)
└── Vertical Overlay (injected at boot)
    ├── Layer 1: Terminology Translation
    ├── Layer 2: Workflow Lifecycle Model
    ├── Layer 3: Compliance Layer
    ├── Layer 4: Checklist Engine
    ├── Layer 5: Artefact Generator Layer
    └── Layer 6: AI Training Layer
```

> See `vertical-registry.md` for the full list of supported verticals.

---

## The 6-Layer Overlay System

### Layer 1 — Terminology Translation

Every vertical has its own language. Titan BOS translates the platform vocabulary into industry-native terms.

| Platform Term | Bond | Commercial | Airbnb | Construction |
|---|---|---|---|---|
| `booking` | bond clean | service visit | turnover | builder clean |
| `job_complete` | passed inspection | logbook signed | guest-ready | zone cleared |
| `client` | landlord/agent | facility manager | host | site supervisor |
| `re_clean` | re-entry clean | callback | reset | defect clean |
| `site` | rental property | premises | listing | build site |
| `report` | bond inspection report | SLA report | staging report | zone handover |

**Implementation:** Terminology keys are loaded from the vertical config and replace all UI label strings via the translation layer. No hardcoded strings in components.

---

### Layer 2 — Workflow Lifecycle Model

Each vertical has a different job lifecycle. The overlay defines states, transitions, and triggers.

**Bond Cleaning lifecycle:**
```
Enquiry → Quote → Deposit Paid → Scheduled → In Progress
→ Completion Photo Submitted → Inspection Pending
→ Passed / Re-clean Required → Invoice → Paid
```

**Airbnb Turnover lifecycle:**
```
Checkout Confirmed (PMS trigger) → Auto-Scheduled → Dispatched
→ Arrived (QR check-in) → In Progress → Staged
→ Photo Submitted → Host Notified → Booking Ready
```

**Construction lifecycle:**
```
Stage Gate Approved → Rough Clean → Final Clean → Sparkle Clean
→ Zone Signoff → Handover Pack Generated → Builder Acceptance
```

**Biohazard lifecycle:**
```
Emergency Callout → Site Assessment → Containment Setup
→ PPE Don Log → Decontamination → Waste Disposal Log
→ Regulatory Notification → Site Clearance Certificate
```

**Implementation:** Each lifecycle is a named state machine config. The Workflow engine loads the vertical's state machine at job creation.

---

### Layer 3 — Compliance Layer

Compliance gates enforce mandatory actions before state transitions can proceed. These are vertical-specific and cannot be bypassed.

| Vertical | Compliance Gates |
|---|---|
| Construction | SWMS sign-off before site entry, height safety cert check before elevated work |
| Biohazard | PPE don log required, regulatory notification mandatory, chain-of-custody at every transition |
| Window Cleaning | Height risk assessment filed, access equipment cert verified |
| Solar Panel | Weather validation gate (wind speed, moisture), height cert check |
| NDIS | Participant consent recorded, NDIS worker screening verified, incident mandatory reporting |
| Medical | ATP test result recorded, cycle log completed before sign-off |
| Bond | Carpet receipt captured, key custody logged |
| Pool | Water chemistry test recorded, chemical quantities logged |

**Implementation:** Compliance gates are policy classes evaluated at workflow transition. A gate failure blocks the transition and triggers a compliance alert in Titan Zero.

---

### Layer 4 — Checklist Engine

Checklists are vertical-specific and room/zone/stage aware. The overlay injects the correct checklist set at job creation.

**Residential checklist structure:**
```
Kitchen → Living Areas → Bedrooms → Bathrooms → Laundry → Outdoor
(each room: standard item list with condition scoring)
```

**Bond checklist additions over Residential:**
```
Appliance condition report
Carpet condition report (with stain mapping)
Wall mark inspection
Window frame and track inspection
Blind and curtain check
Oven deep clean verification
Key return confirmation
```

**Commercial checklist structure:**
```
Reception → Open Plan → Offices → Bathrooms → Kitchen/Breakroom
→ Entry/Exit (security lock-up) → Logbook entry
```

**Construction zone checklist:**
```
Per-zone: [Zone name] → Debris removed → Surfaces wiped
→ Windows cleaned → Fixtures polished → Trade waste removed
→ Photo evidence → Zone sign-off
```

**Implementation:** Checklist templates are stored per vertical in the overlay config. The Checklist engine assembles the job checklist on booking creation using the site type + vertical combination.

---

### Layer 5 — Artefact Generator Layer

Artefacts are output documents generated from job data. Each vertical has required artefacts that are auto-generated on job completion.

| Vertical | Artefacts Generated |
|---|---|
| Bond | Bond inspection report, condition report, before/after photo pack |
| Commercial | Logbook entry, periodic inspection report, SLA compliance summary |
| Construction | SWMS document, zone handover report, photographic evidence pack |
| Biohazard | Incident report, regulatory notification, chain-of-custody log, disposal certificate |
| Solar | Panel condition report, efficiency delta estimate, height cert copy |
| Window | Height risk assessment form, completion certificate |
| NDIS | Session note, support log, incident report, service agreement |
| Airbnb | Turnover completion report, staging photos, linen log, damage report |
| Pool | Water quality report, service history log, chemical usage record |
| Medical | Sanitation cycle log, ATP test record, audit certificate |
| Bond | Bond inspection report, condition report, before/after pack |
| Carpet | Pre-inspection report, treatment record, warranty document |

**Implementation:** Artefacts are TitanDocs templates. On job completion, the artefact generator runs, populates fields from job data, and delivers the artefact via TitanReach to the configured recipient (agent, client, regulatory body).

---

### Layer 6 — AI Training Layer

Titan Zero loads a vertical-specific knowledge pack at context initialisation. This transforms the generic assistant into a vertical expert.

**Knowledge pack contents per vertical:**

| Vertical | AI Knowledge Pack Includes |
|---|---|
| Bond | REIA checklists, common agent dispute areas, re-clean trigger conditions, bond law by state |
| Commercial | AS/NZS cleaning standards, SLA definitions, logbook requirements, OHS site protocols |
| Construction | SWMS writing guides, WHS Act requirements, builder terminology, stage definitions |
| Biohazard | COSHH data, biohazard classification categories, regulatory reporting triggers, PPE standards |
| Solar | Panel type identification, cleaning agent compatibility, efficiency benchmarks, height safety |
| NDIS | NDIS Practice Standards, participant rights, incident categories, mandatory reporting rules |
| Airbnb | Airbnb host standards, guest experience expectations, platform penalty triggers, linen standards |
| Pool | Pool chemistry reference, chemical dosing guides, equipment fault diagnosis, seasonal variations |
| Garden | Plant identification, seasonal calendar, irrigation standards, equipment operation |
| Medical | TGA guidelines, ATP testing interpretation, sanitation cycle standards, audit preparation |

**AI behaviours activated per vertical:**
- Quote generation using vertical-specific pricing logic
- Compliance warning generation (e.g., "SWMS not signed — cannot proceed to site entry")
- Artefact pre-fill from job conversation
- Escalation detection tuned to vertical (e.g., NDIS incident triggers immediate mandatory notification)
- Objection handling trained on vertical customer concerns

---

## Injection Points Per Dashboard

Each dashboard node receives the overlay at different integration depths.

| Dashboard | What the Overlay Changes |
|---|---|
| **Titan Pro** | Reports use vertical terminology, KPIs show vertical metrics (bond pass rate, SLA compliance %), navigation surfaces vertical-specific modules |
| **Ground Zero** | Dispatch map shows vertical site markers, job cards use vertical terminology, compliance alerts visible per job |
| **Titan Go** | Checklist shown is vertical-specific, artefact capture flow matches vertical requirements, compliance gates block completion |
| **Titan Zero** | AI context pack is vertical-specific, prompts and suggestions tuned to vertical, artefact generation uses vertical templates |
| **Titan Hello** | Intake conversation uses vertical terminology, lead qualification captures vertical-specific fields |
| **ZeroPay** | Invoice templates use vertical billing model (deposit+completion, progress claims, NDIS funding codes) |
| **Zero Fuss** | Customer portal shows vertical-relevant job history and artefacts (bond report, staging photos, water quality log) |
| **Titan Studio** | Marketing content generated uses vertical-specific language and value propositions |
| **Titan Solo** | Simplified checklist and artefact flow for single-operator vertical delivery |

---

## Overlay Data Model

All verticals share the same underlying data primitives. The overlay does not change the schema — it configures how the schema is used.

```
Shared Primitives
├── Customer (client, agent, host, facility manager — same record, different label)
├── Site (property, premises, listing, build site — same record, different label)
├── ServiceJob (bond clean, turnover, logbook visit — same record, different lifecycle)
├── Visit (occurrence of a ServiceJob — checklist, time, crew, completion)
├── Artefact (output document — type varies by vertical)
├── ComplianceRecord (gate logs, certifications, PPE records)
└── AIInteraction (all Titan Zero exchanges — vertical context tagged)
```

This means:
- Reports span verticals without schema changes
- Multi-vertical companies share one customer/site record
- Billing is unified regardless of vertical mix

---

## Overlay Activation Model

```php
// At booking creation — vertical selected per job
$vertical = $booking->vertical ?? $company->default_vertical;

// Overlay loaded
$overlay = VerticalOverlay::load($vertical);

// Injected into active systems
TitanGo::applyOverlay($overlay);
TitanZero::loadContextPack($overlay->ai_pack);
TitanDocs::loadTemplates($overlay->artefact_templates);
CleanQuality::loadChecklists($overlay->checklists);
WorkflowEngine::loadLifecycle($overlay->lifecycle_model);
ComplianceEngine::loadGates($overlay->compliance_rules);
```

---

## Multi-Vertical Company Support

A company can operate across multiple verticals simultaneously. Example — exterior services operator:

```
Company: BrightEdge Services
├── Vertical: Commercial Cleaning  (80% of bookings)
├── Vertical: Pressure Cleaning    (10% of bookings)
├── Vertical: Window Cleaning      (7% of bookings)
└── Vertical: Solar Panel Cleaning (3% of bookings)
```

**Per-booking vertical selection** means:
- Each job activates the correct overlay
- A single cleaner can run a commercial job then a window job in the same day
- Reports aggregate across verticals with correct terminology per line

---

## Adding a New Vertical

New verticals require no backend code changes for simple cases:

1. Create vertical config file (`config/verticals/{slug}.json`)
2. Write AI context pack (`resources/ai/verticals/{slug}/context.json`)
3. Write checklist templates (`resources/checklists/{slug}/`)
4. Write artefact templates (`resources/artefacts/{slug}/`)
5. Define lifecycle state machine (`config/lifecycles/{slug}.php`)
6. Register compliance gates (`config/compliance/{slug}.php`)

For verticals requiring new compliance logic or unique artefact generators, a thin service class is added — still no node code changes.
