# Documentation Reconciliation Status

**Current coordination baseline:** `integration/current-main-reconciliation` at `fa607d769a4f72ba287801b027cc42dcf56aa549`.

## Pass 1 — archive extraction and structural cleanup

**Status:** Completed.

- Both uploaded archives were safety-checked and extracted.
- Extracted collections were moved to `docs/reference/titan-library/`.
- Exact duplicate files removed: **6**.
- Source ZIPs removed after inventory verification: **2**.
- Branch-era and MagicAI v10.91-specific documents were retained under `docs/archive/` with historical banners.
- One current upgrade plan was promoted to `docs/plans/CURRENT_UPGRADE_PLAN.md`.
- Pass 1 catalogue snapshot: **377 documents** and **0 exact duplicate groups**.

Detailed moves and deleted duplicate paths are recorded in `docs/inventory/PASS1_DISPOSITIONS.md` and the machine-readable inventories.

## Root documentation consolidation

**Status:** Completed for the current tree.

The repository root now retains:

- `README.md` as the mandatory repository and agent entry point;
- `AGENTS.md` as the mandatory working agreement;
- `version.txt` because application and updater code consume it;
- recognised legal files;
- dependency, build and machine configuration files that conventionally belong at root.

Project documentation moved from root:

- `EXTENSIONS_IMPORT_MANIFEST.json` → `docs/provenance/root-imported/extensions_import_manifest.json`
- `KNOWN_BASELINE_GAPS.md` → `docs/provenance/root-imported/known_baseline_gaps.md`

Dependent baseline tests, audit tooling and extension documentation were updated to the new manifest path. The exact move register is in:

- `docs/inventory/ROOT_DOCUMENT_CONSOLIDATION.md`
- `docs/inventory/root_document_moves.json`
- `docs/inventory/ROOT_DOCUMENT_REFERENCE_CHECK.md`

## Pass 2 — architecture, authority and trust consolidation

**Status:** Completed.

### Canonical documents established

- `AGENTS.md`
- `docs/architecture/TITAN_ZERO_AUTHORITY_MAP.md`
- `docs/architecture/TENANCY_TRUST_AND_ACTION_EXECUTION.md`
- `docs/plans/CURRENT_UPGRADE_PLAN.md`
- `docs/inventory/PASS2_ARCHITECTURE_CONSOLIDATION.md`

### Decisions established

1. `integration/current-main-reconciliation` is the single shared coordination base.
2. Agents port only unique, verified deltas from frozen old branches.
3. The MagicAI host owns authentication and platform user/company/membership lifecycle.
4. WorkCore consumes host context and is the sole operational record and mutation authority.
5. Interaction Engine owns interaction/wizard governance and command preparation, not operational records.
6. Titan Zero intelligence owns planning and orchestration; confidence cannot grant permission.
7. Chatbot/PWA owns presentation, local drafts, offline state and sync UX, not canonical server truth.
8. Operational finance remains WorkCore-governed and separate from MagicAI platform billing.

No non-identical architecture document was deleted during Pass 2. Extracted architecture documents remain reference-only until each cluster's unique information has been preserved in a source-backed canonical specification.

## Pass 3 — Interaction Engine, Wizard and five-tier intelligence

**Status:** Completed for documentation classification and evidence gathering. Runtime wiring repair remains implementation work.

### Canonical documents and evidence

- `docs/architecture/INTERACTION_WIZARD_AND_FIVE_TIER_INTELLIGENCE.md`
- `docs/inventory/INTERACTION_INTELLIGENCE_RUNTIME_INVENTORY.md`
- `docs/inventory/INTERACTION_INTELLIGENCE_RUNTIME_INVENTORY.json`

### Runtime findings

1. `packages/titanzero/interaction-engine` is the canonical universal package source.
2. Before cleanup it contained 386 files, including the provider, routes, migrations, tests, offline TypeScript and imported engine library.
3. `packages/titan-zero/interaction-engine` contained only one competing `composer.json` for the same package name, with no implementation files, routes, migrations or tests.
4. The metadata-only duplicate package root was removed after its unique provider metadata and conflict were recorded.
5. WorkCore Wizards exists under `app/Domains/WorkCore/System/Modules/Wizards` and is the canonical operational-domain wizard module.
6. The primary five-tier runtime is intended to remain under `app/Extensions/Chatbot/System/TitanAI`.
7. `app/Extensions/TitanZeroChatbot/System/TitanAI` was an exact 864-file byte-for-byte copy of the primary TitanAI tree at inventory time.
8. The secondary TitanAI tree is frozen as compatibility/reference material pending a focused source reconciliation; it was not bulk-deleted during this documentation pass.
9. The embedded Chatbot WorkCore server copy is compatibility/reference-only and must never shadow `app/Domains/WorkCore`.

### Connected Interaction Engine activation gap

The universal package remains **source-present but not connected-host verified** because:

- root `composer.json` does not register the canonical package as a path repository;
- root `composer.json` does not require `titanzero/interaction-engine`;
- `interaction_engine_enabled` exists but is not currently used to register the provider;
- the explicit-registration unit test expects one provider reference in `config/app.php`;
- repository search did not find that explicit provider registration;
- the host boot test expects the provider to load.

The repository therefore contains source, tests and provider expectations that are not yet coherent. A focused implementation PR must select exactly one activation model and verify it from a clean checkout.

### Pass 3 deletion policy

Deleted:

- the metadata-only duplicate `packages/titan-zero/interaction-engine/composer.json`.

Not deleted:

- any non-identical doctrine or reference document;
- the canonical Interaction Engine package;
- WorkCore Wizards;
- either 864-file Chatbot TitanAI tree;
- package-local build reports or changelogs that preserve source lineage.

## Pass 1 exact duplicates removed

- `docs/reference/titan-library/collection-1/01-Ecosystem-Vision/Titan Zero Doctrine.pdf`
- `docs/reference/titan-library/collection-1/05-Engine-Platform/Worksuite_Module_Doctrine_v1.0_LOCKED.pdf`
- `docs/reference/titan-library/collection-2/13-Blueprints-Patterns/Titan Core Engine Map (Master Blueprint).pdf`
- `docs/reference/titan-library/collection-2/13-Blueprints-Patterns/Titan Zero architecture blueprint .pdf`
- `docs/reference/titan-library/collection-2/08-Communications-Channels/Titan Engine APIs & Contracts (Implementation Guide).pdf`
- `docs/reference/titan-library/collection-1/00-Foundation/TitanZero ╬ôC╠ºo╠ê Database Doctrine & Schema Model.pdf`

## Pass 1 historical moves

- moved the two extracted archive trees to `docs/reference/titan-library/collection-1` and `collection-2`;
- moved branch and pass status documents to `docs/archive/status/2026-07/`;
- moved superseded subsystem plans to `docs/archive/plans/2026-07/`;
- moved MagicAI v10.91 setup and import records to `docs/archive/setup/` and `docs/archive/provenance/`;
- moved old merge, rejected and superseded reports to `docs/archive/reports/2026-07/`;
- moved current Titan Money/Titan Pay provenance to `docs/provenance/`.

The complete per-file move register remains in the Pass 1 disposition report and Git history.

## Next pass

### Pass 4 — PWA, offline runtime and duplicated Chatbot surfaces

1. Inventory service workers, manifests, IndexedDB schemas, vaults, outboxes, retry and conflict implementations.
2. Compare `app/Extensions/Chatbot` with `app/Extensions/TitanZeroChatbot` beyond the TitanAI subtree.
3. Establish the canonical PWA extension and compatibility boundary.
4. Map device DTOs and sync envelopes to canonical WorkCore contracts.
5. Identify duplicate service-worker caches, local databases, routes, providers and assets.
6. Produce one source-backed PWA/offline architecture specification.
7. Remove only exact duplicates or fully preserved superseded documentation; leave runtime deletion to focused source PRs with dependency evidence.
