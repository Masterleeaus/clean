# WorkCore Assurance and Evidence Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add canonical WorkCore Documents/Evidence and Assurance domains to the integrated Meetup/Titan Zero application.

**Architecture:** New modules follow the existing action registry, read-model registry, tenant-scoped repository and host capability patterns. Existing form and work-order evidence remain source records and are linked rather than replaced.

**Tech Stack:** PHP 8.2+, Laravel 12 source conventions, Illuminate query builder, ULIDs, standalone dependency-free PHP verification.

## Global Constraints

- WorkCore remains canonical owner of operational records.
- Company authority comes only from server-resolved context.
- No direct extension or AI writes to WorkCore tables.
- No donor service providers, tenancy, finance or route files are imported.
- New storage paths are private-storage metadata only.
- All checksums use SHA-256 hexadecimal values.

---

### Task 1: Behaviour contracts

**Files:**
- Create: `tests/Standalone/WorkCoreAssurance/run.php`
- Create: `app/Domains/WorkCore/System/Modules/Documents/Services/CanonicalPayloadHasher.php`
- Create: `app/Domains/WorkCore/System/Modules/Documents/Services/EvidenceSignoffPolicy.php`
- Create: `app/Domains/WorkCore/System/Modules/Assurance/Services/InspectionScoringPolicy.php`
- Create: `app/Domains/WorkCore/System/Modules/Assurance/Services/RiskMatrix.php`

- [ ] Write tests for deterministic hashing, evidence signability, inspection scoring and risk classification.
- [ ] Run the tests and confirm failure because classes do not exist.
- [ ] Implement the minimal policy classes.
- [ ] Run the tests and confirm all behaviour contracts pass.

### Task 2: Canonical schema

**Files:**
- Create: `database/migrations/2026_07_26_020000_create_tz_documents_and_evidence_tables.php`
- Create: `database/migrations/2026_07_26_020010_create_tz_assurance_tables.php`
- Modify: `tests/Standalone/WorkCoreAssurance/run.php`

- [ ] Add structural tests for required company-scoped tables, indexes and foreign keys.
- [ ] Run and confirm failure.
- [ ] Implement the migrations.
- [ ] Run and confirm structural tests pass.

### Task 3: Documents and evidence module

**Files:**
- Create module contracts, repository, actions, provider and read actions under `app/Domains/WorkCore/System/Modules/Documents/`.
- Modify: `app/Domains/WorkCore/WorkCoreServiceProvider.php`
- Modify: `config/workcore.php`
- Modify: `tests/Standalone/WorkCoreAssurance/run.php`

- [ ] Add structural tests for action keys, capability ownership and repository tenant guards.
- [ ] Run and confirm failure.
- [ ] Implement document create/version/link/comment/approval, evidence register/sign-off and search/read actions.
- [ ] Run and confirm tests pass.

### Task 4: Assurance module

**Files:**
- Create module contracts, repository, actions, provider and read actions under `app/Domains/WorkCore/System/Modules/Assurance/`.
- Modify: `app/Domains/WorkCore/WorkCoreServiceProvider.php`
- Modify: `config/workcore.php`
- Modify: `tests/Standalone/WorkCoreAssurance/run.php`

- [ ] Add structural tests for inspection, finding, corrective action, risk and incident action keys.
- [ ] Run and confirm failure.
- [ ] Implement governed actions, repository transactions and search/read actions.
- [ ] Run and confirm tests pass.

### Task 5: Host surfaces and verification

**Files:**
- Modify: `app/Http/Controllers/Titan/WorkCoreSummaryController.php`
- Modify: `resources/views/titan/operations.blade.php`
- Modify: `tools/titan_verify.php`
- Create: `docs/integration/BOS_ASSURANCE_EVIDENCE_DELTA.md`
- Modify: `BUILD_REPORT.md`
- Modify: `README.md`

- [ ] Add summary and structural verification checks.
- [ ] Implement assurance/evidence dashboard counts and documentation.
- [ ] Run standalone tests, full structural verifier, PHP syntax and namespace scan.
- [ ] Package and extraction-retest v0.2.0.
