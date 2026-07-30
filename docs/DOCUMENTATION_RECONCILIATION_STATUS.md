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

## Pass 4 — PWA, offline runtime and duplicated Chatbot surfaces

**Status:** Completed for documentation classification and evidence gathering. Runtime removal and security repair remain focused implementation work.

### Canonical documents and evidence

- `docs/architecture/PWA_OFFLINE_AND_CHATBOT_EXTENSION_ARCHITECTURE.md`
- `docs/inventory/PWA_OFFLINE_RUNTIME_INVENTORY.md`
- `docs/inventory/PWA_OFFLINE_RUNTIME_INVENTORY.json`

### Runtime findings

1. `app/Extensions/Chatbot` is the canonical intended Chatbot/PWA extension.
2. The primary extension contains 1,548 files; `app/Extensions/TitanZeroChatbot` contains 1,542.
3. The trees share 1,542 relative paths: 1,541 are byte-identical and only `System/ChatbotServiceProvider.php` differs.
4. The primary contains six additional Titan Train files and its native workspace test.
5. External source references overwhelmingly use the primary namespace and provider; no secondary namespace/provider references were found outside documentation/reference paths.
6. The primary provider uses `TitanZeroFeatureFlags` and conditionally registers WorkCore integrations.
7. The secondary provider registers WorkCore AI/runtime integration unconditionally and is superseded as current bootstrap behaviour.
8. Each tree contains 93 migrations and 40 provider-like files, creating a high duplicate-activation risk.
9. The canonical PWA includes a versioned service worker, IndexedDB version 5, AES-256-GCM device vault, persistent outbox, conflict store, sync inbox and push/pull/acknowledgement engine.
10. The service worker intentionally excludes authenticated APIs and sensitive paths from caching and wakes authenticated clients rather than reading IndexedDB or credentials directly.
11. The generic outbox persists operation headers and bodies directly; production verification must prove no secrets enter queued payloads or add encryption before persistence.
12. The device ID is stored in localStorage and must be treated only as an identifier, never as authorization proof.

### Pass 4 deletion policy

Not deleted:

- either complete Chatbot extension;
- the secondary 1,542-file compatibility tree;
- any IndexedDB store, migration, service worker, outbox or local record;
- extension-local reports or provenance files.

The secondary extension requires a focused source PR with registry, provider, route, migration, asset, installer, updater and rollback evidence before removal.

## Pass 5 — extension platform, manifests and lifecycle

**Status:** Completed for documentation classification and evidence gathering. Registry and installer hardening remain implementation work.

### Canonical documents and evidence

- `docs/architecture/EXTENSION_PLATFORM_AND_LIFECYCLE_ARCHITECTURE.md`
- `docs/inventory/EXTENSION_PLATFORM_INVENTORY.md`
- `docs/inventory/EXTENSION_PLATFORM_INVENTORY.json`
- `docs/inventory/EXTENSION_PLATFORM_GAPS.md`

### Runtime findings

1. There are 95 extension directories and 112 static marketplace provider mappings.
2. `Introduction` and `TitanZeroChatbot` exist on disk but are not mapped; `TitanZeroChatbot` remains compatibility-only and must not be added to the active map.
3. Nineteen static marketplace mappings reference provider classes whose source files are absent.
4. Enabling extension discovery currently registers every loadable mapped provider rather than only installed, entitled, enabled and qualified extensions.
5. The scan found 810 PHP symbols declared in more than one extension directory; 768 are the copied `Chatbot`/`TitanZeroChatbot` symbols.
6. The scan found 93 duplicate migration filenames; 78 are shared between the two Chatbot trees.
7. Every extension directory has a recognised manifest and all `extension.json` files decode successfully with BOM-aware UTF-8 handling.
8. The legacy manifests are valid but minimal and do not declare provider, compatibility, dependencies, capabilities, permissions, migrations, secrets, health checks or integrity evidence.
9. Eighty-eight of 95 extension directories have no detected extension-local test file.
10. Marketplace install and uninstall are exposed as authenticated GET routes.
11. The current installer downloads a remote ZIP and calls `extractTo()` without detected entry traversal/symlink validation or archive signature verification.
12. New extension directories are created with mode `0777`.
13. Installation clears caches, runs forced migrations and force-publishes assets without a detected transactional install or rollback path.
14. The legacy installer can execute package SQL and copy controllers, routes and stubs into application paths.
15. Uninstall deletes extension directories and invokes hooks, but no database rollback was detected and lifecycle exceptions may be swallowed.

### Canonical policy established

- Files on disk do not mean an extension is active.
- Extensions progress through discovered, installed, qualified, enabled, disabled, dormant, compatibility-only, quarantined, superseded and removed states.
- Provider registration must be computed from validated manifests, installed records, qualification, entitlement and enabled state.
- A versioned manifest must declare identity, provider, compatibility, dependencies, capabilities, permissions, tenant scope, lifecycle, health and integrity information.
- Install/uninstall must use authorised state-changing requests, signed archives, safe extraction, staging, rollback and auditable lifecycle records.
- Providers, routes, migrations, permissions, menus and capability keys must register exactly once.

### Pass 5 deletion policy

Not deleted:

- any extension directory;
- any stale marketplace mapping;
- `Introduction`;
- any package manifest;
- any duplicate symbol or migration source.

Stale mappings, extension qualification and runtime removal require focused implementation PRs with dependency, database and rollback evidence.

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

### Pass 6 — communications, channels and consent

1. Inventory host messaging, Chatbot channels, AI-agent channels, voice/telephony and communications reference documents.
2. Establish one conversation, message, contact-binding, consent and delivery authority model.
3. Detect duplicate WhatsApp, Telegram, Messenger, Instagram, SMS, email, voice and notification providers.
4. Map channel credentials to Vault and external callbacks to signed/replay-protected adapters.
5. Consolidate delivery status, retries, templates, presence, session transfer and audit requirements.
6. Produce one source-backed communications architecture specification.
7. Remove only exact duplicate or fully preserved superseded documentation; runtime changes remain focused implementation PRs.
