# Titan Zero Documentation

This directory separates current engineering guidance from evidence, historical records and the extracted Titan reference library.

## Start here

1. [Agent working agreement](../AGENTS.md)
2. [Current upgrade plan](plans/CURRENT_UPGRADE_PLAN.md)
3. [Canonical authority map](architecture/TITAN_ZERO_AUTHORITY_MAP.md)
4. [Tenancy, trust and governed action execution](architecture/TENANCY_TRUST_AND_ACTION_EXECUTION.md)
5. [Documentation policy](governance/DOCUMENTATION_POLICY.md)
6. [Documentation reconciliation status](DOCUMENTATION_RECONCILIATION_STATUS.md)

## Evidence and provenance

- [MagicAI v11.00 import audit](audits/magicai-v1100-source-inventory.md)
- [Pass 1 dispositions](inventory/PASS1_DISPOSITIONS.md)
- [Pass 2 architecture consolidation](inventory/PASS2_ARCHITECTURE_CONSOLIDATION.md)
- `inventory/archive_inventory.json` records the two uploaded archive contents and hashes.
- `inventory/documentation_catalogue.json` is the generated Pass 1 catalogue snapshot.

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

## Reconciliation progress

- Pass 1: archives extracted, exact duplicates removed, historical records separated.
- Pass 2: ownership, tenancy, trust and action execution consolidated into source-backed canonical documents.
- Remaining clusters: Interaction/AI, PWA/offline, extensions/modules, communications, automation and data architecture.
