# Vertical AI Training Architecture

## Purpose

This document defines how Titan Zero becomes a trained specialist per vertical — transforming from a generic assistant into an expert operator for each service industry.

> Titan Zero doesn't just answer questions. In each vertical, it becomes a practitioner of that trade.

---

## The Problem This Solves

A generic AI assistant gives generic answers:
- "How do I clean a bond property?" → Generic advice
- "What chemicals are safe for solar panels?" → Generic caution

A vertically-trained Titan Zero gives expert answers:
- "This property needs REIA-compliant checklist completion. The agent at Ray White has flagged the oven and carpet. Standard re-clean trigger: carpet receipt required before bond release."
- "Solar panels on this site are monocrystalline. Use deionised water only. Last clean efficiency delta: +4.2%. Weather gate: wind >15km/h blocks roof work today."

The difference is **context loading** — the right knowledge pack, at the right time, for the right vertical.

---

## Architecture Overview

```
Titan Zero (core AI engine)
└── Vertical Context Loader
    ├── Knowledge Pack (industry manuals + standards)
    ├── Terminology Pack (industry vocabulary)
    ├── Compliance Pack (regulations + mandatory reporting)
    ├── Checklist Intelligence (what to look for, common failures)
    ├── Artefact Intelligence (how to complete industry documents)
    └── Pricing Intelligence (vertical-specific cost models)
```

At job creation, Titan Zero initialises with the vertical context pack. All subsequent AI calls within that job context are informed by the pack.

---

## Knowledge Pack Structure

Each vertical has a structured knowledge pack stored in `resources/ai/verticals/{slug}/`.

```
resources/ai/verticals/bond/
├── context.json          — primary context pack (loaded into Titan Zero)
├── standards/
│   ├── reia-checklist.md
│   ├── bond-act-qld.md
│   ├── bond-act-nsw.md
│   └── common-dispute-areas.md
├── checklists/
│   ├── room-by-room-reference.md
│   └── appliance-inspection-reference.md
├── compliance/
│   ├── mandatory-reporting.md
│   └── re-clean-triggers.md
└── artefacts/
    ├── bond-inspection-report-guide.md
    └── condition-report-guide.md
```

---

## Knowledge Pack Content Per Vertical

### Tier 1 — Core Cleaning Verticals

#### Residential Cleaning
```json
{
  "vertical": "residential",
  "knowledge_domains": [
    "common household surfaces and materials",
    "cleaning product safety and compatibility",
    "common stain identification and treatment",
    "client preference management",
    "recurring service planning"
  ],
  "ai_behaviours": [
    "suggest next-clean scheduling gaps",
    "flag recurring complaint patterns",
    "recommend product substitutions for pet/allergy households",
    "estimate clean duration by bedroom/bathroom count"
  ]
}
```

#### Bond Cleaning
```json
{
  "vertical": "bond",
  "knowledge_domains": [
    "REIA-compliant checklist standards (by state)",
    "common agent dispute trigger areas",
    "re-clean cause classification",
    "bond law by state (QLD, NSW, VIC, WA, SA)",
    "carpet cleaning standards and receipt requirements",
    "key custody protocols"
  ],
  "ai_behaviours": [
    "generate pre-clean risk assessment from property notes",
    "flag checklist items most likely to fail inspection",
    "draft re-clean notification to agent",
    "pre-fill bond inspection report from job photos",
    "calculate re-clean probability from property condition notes"
  ]
}
```

#### Commercial Cleaning
```json
{
  "vertical": "commercial",
  "knowledge_domains": [
    "AS/NZS 3733 cleaning standards",
    "SLA definition and measurement",
    "after-hours security lock-up protocols",
    "logbook requirements and format",
    "OHS site induction requirements",
    "inspection scoring methodologies"
  ],
  "ai_behaviours": [
    "generate SLA compliance report from visit data",
    "draft logbook entry from checklist completion",
    "flag SLA breach risk before visit window closes",
    "suggest remediation actions for inspection failures"
  ]
}
```

#### Airbnb / Short-Stay
```json
{
  "vertical": "airbnb",
  "knowledge_domains": [
    "Airbnb Superhost standards",
    "guest experience expectations and common complaints",
    "linen staging standards",
    "platform penalty triggers (late checkout, cleanliness reviews)",
    "damage identification and documentation"
  ],
  "ai_behaviours": [
    "calculate turnover window from checkout/checkin times",
    "flag staging items from previous guest damage notes",
    "draft damage report for host",
    "suggest linen rotation schedule",
    "generate guest-ready sign-off from photo evidence"
  ]
}
```

#### Construction Site Cleaning
```json
{
  "vertical": "construction",
  "knowledge_domains": [
    "construction stage definitions (rough, final, sparkle)",
    "SWMS writing requirements (WHS Act)",
    "builder terminology and trade coordination",
    "zone-based handover protocols",
    "back-charge documentation standards"
  ],
  "ai_behaviours": [
    "generate SWMS from site type and task description",
    "identify which cleaning stage applies from site photos",
    "draft zone handover report from checklist data",
    "flag height safety gate before roof/elevated work"
  ]
}
```

---

### Tier 2 — Specialist High-Margin Verticals

#### Biohazard & Crime Scene
```json
{
  "vertical": "biohazard",
  "knowledge_domains": [
    "COSHH regulations",
    "biohazard classification (Class 1–4)",
    "PPE standards by hazard category",
    "waste disposal certification requirements",
    "chain-of-custody documentation",
    "mandatory regulatory reporting triggers and deadlines"
  ],
  "ai_behaviours": [
    "classify hazard level from site description",
    "generate PPE requirement list for hazard class",
    "draft regulatory notification from incident data",
    "flag missing chain-of-custody steps",
    "generate chain-of-custody log from job events"
  ]
}
```

#### Medical Equipment Cleaning
```json
{
  "vertical": "medical",
  "knowledge_domains": [
    "TGA sanitation guidelines",
    "ATP testing standards and pass/fail thresholds",
    "equipment-specific sanitation cycles",
    "audit trail requirements for healthcare",
    "chemical compatibility with medical-grade surfaces"
  ],
  "ai_behaviours": [
    "interpret ATP test result (pass/fail with context)",
    "suggest remediation if ATP threshold exceeded",
    "generate audit-ready sanitation cycle log",
    "flag chemical incompatibilities with equipment type"
  ]
}
```

#### Solar Panel Cleaning
```json
{
  "vertical": "solar",
  "knowledge_domains": [
    "solar panel types (monocrystalline, polycrystalline, thin-film)",
    "safe cleaning agents by panel type",
    "efficiency benchmarks and soiling loss rates",
    "height safety requirements for roof and ground-mount systems",
    "weather safety thresholds for panel cleaning"
  ],
  "ai_behaviours": [
    "check weather gate before job dispatch",
    "recommend cleaning agent from panel type",
    "estimate efficiency delta from pre/post clean readings",
    "generate panel condition report with zone mapping"
  ]
}
```

#### Industrial Window Cleaning
```json
{
  "vertical": "window",
  "knowledge_domains": [
    "Working at Height legislation",
    "access equipment certification requirements",
    "glass types and cleaning agent compatibility",
    "rope access and cradle safety protocols",
    "permit-to-work requirements"
  ],
  "ai_behaviours": [
    "generate height risk assessment from building data",
    "flag expired access equipment certifications",
    "draft permit-to-work documentation",
    "check weather gate for height work"
  ]
}
```

---

### Tier 3 — Property & Exterior Verticals

#### Pool Maintenance
```json
{
  "vertical": "pool",
  "knowledge_domains": [
    "pool water chemistry (pH, chlorine, alkalinity, stabiliser)",
    "chemical dosing calculations by pool volume",
    "equipment fault diagnosis (pump, filter, chlorinator)",
    "seasonal variation effects on chemistry",
    "pool safety compliance (QLD/NSW/VIC barrier laws)"
  ],
  "ai_behaviours": [
    "interpret water test results and recommend chemical adjustments",
    "calculate chemical doses from pool volume and current readings",
    "diagnose equipment faults from symptom description",
    "generate water quality report from test data"
  ]
}
```

#### Garden & Grounds
```json
{
  "vertical": "garden",
  "knowledge_domains": [
    "Australian native and exotic plant identification",
    "seasonal pruning and maintenance calendar",
    "irrigation system types and maintenance",
    "soil types and fertiliser compatibility",
    "pest and disease identification"
  ],
  "ai_behaviours": [
    "identify plant from description or photo",
    "recommend seasonal tasks based on calendar and location",
    "diagnose plant health issues from symptom description",
    "generate maintenance record with plant register"
  ]
}
```

#### Pressure Cleaning
```json
{
  "vertical": "pressure",
  "knowledge_domains": [
    "surface types and pressure tolerances (concrete, pavers, render, tile)",
    "chemical selection by surface and stain type",
    "water usage estimation",
    "equipment pressure settings by task"
  ],
  "ai_behaviours": [
    "recommend pressure setting from surface type",
    "select chemical treatment from stain/surface combination",
    "estimate water usage for quote",
    "generate before/after evidence pack description"
  ]
}
```

---

### Tier 4 — Mobile Specialty Verticals

#### Car Detailing
```json
{
  "vertical": "detailing",
  "knowledge_domains": [
    "paint correction stages (compound, polish, seal)",
    "ceramic coating application and curing requirements",
    "interior material types (leather, fabric, vinyl, alcantara)",
    "paint swirl and scratch identification",
    "fleet maintenance scheduling"
  ],
  "ai_behaviours": [
    "recommend detailing package from vehicle condition description",
    "generate vehicle condition report from inspection notes",
    "calculate curing time for coating application",
    "suggest upsell packages from vehicle history"
  ]
}
```

#### Pet Grooming (Mobile)
```json
{
  "vertical": "pet",
  "knowledge_domains": [
    "dog breed coat types and maintenance requirements",
    "flea and tick treatment protocols",
    "grooming frequency by breed and coat type",
    "common skin conditions and sensitivities"
  ],
  "ai_behaviours": [
    "recommend grooming schedule from breed profile",
    "flag treatment sensitivities from pet notes",
    "generate treatment reminder schedule",
    "draft post-visit care notes for owner"
  ]
}
```

#### Appliance Deep Cleaning
```json
{
  "vertical": "appliance",
  "knowledge_domains": [
    "oven types and self-clean cycle considerations",
    "rangehood filter types and cleaning requirements",
    "fridge/freezer sanitation protocols",
    "chemical safety for food contact surfaces"
  ],
  "ai_behaviours": [
    "select cleaning method from appliance type and condition",
    "flag chemical restrictions for food-contact surfaces",
    "estimate time by appliance count and condition grade",
    "generate before/after appliance condition report"
  ]
}
```

---

## Context Loading at Runtime

```php
// Titan Zero initialisation per job
class TitanZeroContextLoader
{
    public function loadForJob(Job $job): ContextPack
    {
        $vertical = $job->vertical ?? $job->company->default_vertical;

        return new ContextPack(
            knowledge: $this->loadKnowledge($vertical),
            terminology: $this->loadTerminology($vertical),
            compliance: $this->loadCompliance($vertical),
            checklistIntelligence: $this->loadChecklistRules($vertical),
            artefactIntelligence: $this->loadArtefactGuides($vertical),
            pricingIntelligence: $this->loadPricingRules($vertical),
            jobContext: $this->buildJobContext($job),
        );
    }
}
```

The context pack is passed to every Titan Zero call within the job session. No module makes direct AI calls — all AI flows through `TitanZero::query($contextPack, $prompt)`.

---

## AI Behaviours by Dashboard

| Dashboard | AI Capability (Vertical-Trained) |
|---|---|
| **Titan Go** | Checklist guidance ("common fail point at this stage"), artefact pre-fill, compliance gate warnings |
| **Titan Zero** | Full expert consultation, report drafting, compliance advice, artefact generation |
| **Titan Hello** | Lead qualification using vertical terminology, vertical-specific intake questions |
| **Ground Zero** | Dispatch anomaly detection tuned to vertical SLAs, real-time compliance alerts |
| **Titan Solo** | Simplified suggestions — next action, quote draft, follow-up reminder |
| **Titan Studio** | Marketing copy using vertical value propositions and terminology |
| **ZeroPay** | Cashflow advice using vertical billing patterns (deposit/completion, progress claims) |
| **Titan Pro** | Performance analysis using vertical KPIs (bond pass rate, SLA %, turnover time) |
| **Zero Fuss** | Customer-facing summaries using vertical-appropriate language |

---

## Knowledge Pack Governance

Knowledge packs are versioned and auditable:

```
resources/ai/verticals/{slug}/
├── context.json          (v2.1 — last updated: 2026-03)
├── CHANGELOG.md          (what changed and why)
└── standards/            (source documents that informed the pack)
```

**Update triggers:**
- Regulatory change (new compliance requirement)
- Industry standard revision
- Observed AI failure pattern (captured via Aegis)
- Customer feedback identifying incorrect AI guidance

**Quality gate:** Titan Aegis monitors AI outputs per vertical. Outputs that trigger safety or accuracy flags are reviewed against the knowledge pack. Pack updates are tested against a regression suite before deployment.

---

## Vertical Expert Benchmarks

Each vertical knowledge pack is benchmarked against:

| Benchmark | Description |
|---|---|
| Terminology accuracy | Does Titan Zero use the correct industry terms? |
| Compliance coverage | Does it know all mandatory gates for this vertical? |
| Artefact completeness | Can it generate all required artefacts from job data? |
| Pricing intelligence | Does it price correctly for this vertical's billing model? |
| Escalation accuracy | Does it detect compliance failures and escalate correctly? |

Benchmarks are run before each pack version is promoted to production.
