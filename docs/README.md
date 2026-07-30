# Titan Zero Documentation

This directory separates current engineering guidance from evidence, historical records and the extracted Titan reference library.

## Mandatory reading for every agent

Before changing code or documentation, read the root [`README.md`](../README.md), [`AGENTS.md`](../AGENTS.md), this file, every canonical document listed below, and every document in the subject cluster affected by the work. New project documentation must be added under `docs/`, not at the repository root.

## Start here

1. [Agent working agreement](../AGENTS.md)
2. [Current upgrade plan](plans/CURRENT_UPGRADE_PLAN.md)
3. [Canonical authority map](architecture/TITAN_ZERO_AUTHORITY_MAP.md)
4. [Tenancy, trust and governed action execution](architecture/TENANCY_TRUST_AND_ACTION_EXECUTION.md)
5. [Interaction Engine, Wizard and five-tier intelligence architecture](architecture/INTERACTION_WIZARD_AND_FIVE_TIER_INTELLIGENCE.md)
6. [PWA, offline runtime and Chatbot extension architecture](architecture/PWA_OFFLINE_AND_CHATBOT_EXTENSION_ARCHITECTURE.md)
7. [Documentation policy](governance/DOCUMENTATION_POLICY.md)
8. [Documentation reconciliation status](DOCUMENTATION_RECONCILIATION_STATUS.md)

## Evidence and provenance

- [MagicAI v11.00 import audit](audits/magicai-v1100-source-inventory.md)
- [Pass 1 dispositions](inventory/PASS1_DISPOSITIONS.md)
- [Pass 2 architecture consolidation](inventory/PASS2_ARCHITECTURE_CONSOLIDATION.md)
- [Root document consolidation](inventory/ROOT_DOCUMENT_CONSOLIDATION.md)
- [Interaction, Wizard and five-tier runtime inventory](inventory/INTERACTION_INTELLIGENCE_RUNTIME_INVENTORY.md)
- [PWA, offline and Chatbot extension runtime inventory](inventory/PWA_OFFLINE_RUNTIME_INVENTORY.md)
- `inventory/archive_inventory.json` records the two uploaded archive contents and hashes.
- `inventory/documentation_catalogue.json` is the generated Pass 1 catalogue snapshot.
- `inventory/root_document_moves.json` records documentation moved from the repository root.
- `inventory/INTERACTION_INTELLIGENCE_RUNTIME_INVENTORY.json` contains the file-level Pass 3 evidence.
- `inventory/PWA_OFFLINE_RUNTIME_INVENTORY.json` contains the file-level Pass 4 evidence.

## Where agents add documentation

| Change type | Destination |
|---|---|
| Canonical architecture or system boundaries | `architecture/` |
| Current implementation or upgrade plan | `plans/` |
| Security, engineering or documentation policy | `governance/` |
| Verified audit or test evidence | `audits/` |
| Source origins, checksums or import decisions | `provenance/` |
| Generated catalogues, comparisons and disposition registers | `inventory/` |
| Installation, operations or deployment guidance | `setup/` or an appropriate current operations folder |
| Superseded plans, status reports and obsolete setup material | `archive/` after unique information is preserved |
| Doctrine, proposals, product concepts and unverified blueprints | `reference/titan-library/` |

Do not add project plans, status files, architecture notes, audit reports or provenance records to the repository root. The only documentation entry points retained there are `README.md` and `AGENTS.md`.

## Directory guide

| Directory | Purpose | Authority |
|---|---|---|
| `architecture/` | Current source-backed boundaries, trust and ownership | Canonical when linked above |
| `plans/` | Current coordinated implementation plan | Current guidance |
| `governance/` | Documentation, security and engineering rules | Canonical policy |
| `audits/` | Evidence-based findings and verification | Evidence |
| `provenance/` | Source origins, checksums and import decisions | Evidence |
| `inventory/` | Documentation catalogues, cluster reviews and dispositions | Generated/review evidence |
| `reference/titan-library/` | Extracted doctrine, blueprints, proposed architecture and product concepts | Reference-only |
| `archive/` | Superseded plans, branch reports and old setup instructions | Historical only |

## Authority rule

A reference document does not become current merely because it says `canonical`, `final`, `locked`, has a newer-looking filename or is larger than another file. Current authority requires alignment with accepted ownership boundaries, current source paths, connected runtime behaviour and verification evidence.

Planned, source-present, partially wired and operational are different states. Documentation must state the evidence-supported state precisely.

## Reconciliation progress

- Pass 1: archives extracted, exact duplicates removed and historical records separated.
- Pass 2: ownership, tenancy, trust and action execution consolidated into source-backed canonical documents.
- Pass 3: Interaction Engine, WorkCore Wizards and five-tier intelligence inventoried; one metadata-only duplicate package root removed; canonical runtime roles established; connected Interaction Engine activation remains unverified.
- Pass 4: complete Chatbot extension trees and offline runtimes inventoried; primary Chatbot established as canonical intended extension; secondary near-exact tree frozen; service-worker, IndexedDB, vault, outbox and sync boundaries documented.
- Remaining clusters: extensions/modules, communications, automation, data architecture and release/deployment guidance.
