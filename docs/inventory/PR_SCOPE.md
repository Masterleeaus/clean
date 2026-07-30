# Documentation Reconciliation Pull Request Scope

## Branches

- Repository: `Masterleeaus/clean`
- Base: `integration/current-main-reconciliation`
- Verified base SHA: `fa607d769a4f72ba287801b027cc42dcf56aa549`
- Head: `agent/documentation-reconciliation`
- Pull request: `#25`

## Pass 1 — archive extraction and structural cleanup

- Safety-checked and extracted two uploaded documentation archives.
- Recovered 296 archive documents.
- Moved the extracted library under `docs/reference/titan-library/`.
- Removed the two ZIPs after recording archive membership and hashes.
- Removed six exact byte-for-byte duplicates.
- Archived branch-era and MagicAI v10.91-specific plans, reports, setup and status documents.
- Added documentation policy, catalogues and disposition records.

## Root document governance

- Replaced the root README with a mandatory agent entry point.
- Strengthened `AGENTS.md` with required reading and documentation duties.
- Established `docs/README.md` as the governed document index.
- Moved root provenance documents into `docs/provenance/root-imported/`.
- Updated dependent tests, audit tooling and extension documentation to the new manifest path.
- Retained `version.txt` at root because runtime/updater code consumes it.
- Added cumulative root-document move and reference reports.

## Pass 2 — canonical authority and trust

- Established the shared multi-agent coordination base and delta-porting rule.
- Rewrote the authority map against current source.
- Added the canonical tenancy, device trust and governed action model.
- Rebaselined the current upgrade plan.
- Kept non-identical doctrine and architecture sources as reference-only evidence.

## Pass 3 — Interaction, Wizard and five-tier intelligence

- Inventoried the universal Interaction Engine package, WorkCore Wizards and both Chatbot TitanAI trees.
- Established `packages/titanzero/interaction-engine` as the canonical package source.
- Removed the empty metadata-only duplicate package root under `packages/titan-zero/interaction-engine`.
- Confirmed WorkCore Wizards as the operational-domain wizard module.
- Confirmed the two TitanAI trees contained 864 byte-identical files at inventory time.
- Froze the secondary TitanAI tree as compatibility/reference material pending a focused source reconciliation.
- Added the canonical Interaction, Wizard and five-tier architecture specification.
- Recorded that connected Interaction Engine host activation remains unverified because root Composer and provider activation are not coherent with existing tests.

## Safety boundary

This PR does not:

- activate the Interaction Engine;
- change controllers, routes, migrations or operational domain behaviour;
- bulk-delete either Chatbot extension or its 864-file TitanAI tree;
- merge old agent branches;
- delete non-identical doctrine or architecture documents;
- claim unexecuted tests have passed.

The only non-document source removal is the single metadata-only duplicate `composer.json` under an otherwise empty competing package path. The only source-reference edits point baseline tooling to the relocated provenance manifest.

## Evidence

- `docs/DOCUMENTATION_RECONCILIATION_STATUS.md`
- `docs/inventory/archive_inventory.json`
- `docs/inventory/documentation_catalogue.json`
- `docs/inventory/ROOT_DOCUMENT_CONSOLIDATION.md`
- `docs/inventory/root_document_moves.json`
- `docs/inventory/INTERACTION_INTELLIGENCE_RUNTIME_INVENTORY.md`
- `docs/inventory/INTERACTION_INTELLIGENCE_RUNTIME_INVENTORY.json`

## Next pass

Pass 4 will inventory and consolidate PWA/offline architecture and the duplicated Chatbot extension surfaces. Runtime deletion or activation will remain in focused implementation PRs with dependency and test evidence.
