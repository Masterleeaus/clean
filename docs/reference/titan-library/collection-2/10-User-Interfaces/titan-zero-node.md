# Titan Zero — AI Operating Assistant

**Node type:** Decision · Training · Automation  
**Primary users:** All roles (context-adaptive)

---

## Purpose

The intelligence layer of Titan BOS. Titan Zero is the only AI entry point — all other nodes route AI requests through it.

> Remove noise. Reduce complexity. Guide decisions. Generate artefacts. Train staff.

---

## Interface Type

Chat-first Progressive Web App  
Available embedded in every other node  
Standalone full-screen mode for extended sessions

---

## Core Responsibilities

- Quote drafting
- Inspection summary generation
- Staff training delivery
- Compliance explanations
- Automation suggestions
- Workflow interpretation
- Artefact generation (documents, reports, checklists)
- Signal interpretation (explain what just happened and why)

---

## How Titan Zero Works

```
User request (any node)
→ Intent classification
→ Context packing (relevant module data injected)
→ Model selection (via TitanCore adapter)
→ Aegis safety gate (for high-risk actions)
→ Response / tool execution
→ Signal emission (if action taken)
→ Audit log
```

All requests are tenant-scoped. No cross-company data leakage.

---

## Vertical Intelligence Packs

Each vertical loads a context pack into Titan Zero:

| Vertical | Context pack contents |
|---|---|
| Bond | REIA standards, condition report format, common disputes |
| Construction | SWMS requirements, zone handover checklist, builder terminology |
| Biohazard | COSHH sheets, PPE requirements, regulatory reporting format |
| Commercial | SLA definitions, logbook requirements, security procedures |
| Airbnb | Turnover standards, Airbnb host rules, staging checklists |
| Solar | Weather safety thresholds, panel types, cleaning agents |
| NDIS | NDIS Practice Standards, participant rights, incident categories |

---

## Example Outputs by Vertical

| Vertical | Titan Zero artefact |
|---|---|
| Any | Scope of work estimate |
| Bond | Bond pack / condition report |
| Construction | SWMS document |
| Commercial | Periodic inspection summary |
| Airbnb | Turnover completion report |
| Biohazard | Incident report + regulatory notification |
| NDIS | Session note + support log |

---

## AI Execution Model

**Supports:**
- BYO API keys (OpenAI, Anthropic, Google Gemini)
- Local inference (Ollama)
- Managed providers via TitanCore adapters

**Does not:**
- Make direct API calls from any other module
- Resell prompts or responses
- Share context between tenants

---

## Node Relationships

```
Titan Zero (AI layer)
├── embedded in: every other node
├── reads manifests: ai_tools.json from every module
├── executes tools: via module-registered tool contracts
├── routes through: TitanCore (provider adapters)
├── safety gate: Aegis (approval for high-risk actions)
├── context from: any module (tenant-scoped)
└── emits: signals for any action taken
```
