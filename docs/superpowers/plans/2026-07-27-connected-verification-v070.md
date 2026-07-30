# Connected Verification v0.7.0 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Produce a deployment-verifiable v0.7.0 checkpoint by running all locally reachable gates, fixing source defects, and adding reproducible connected-environment build and database verification tooling.

**Architecture:** The application remains Laravel 12 with Meetup as communication UI, WorkCore as operational authority, and Titan subsystems behind governed contracts. This pass adds no new business authority; it hardens deployment, database, queue, realtime and frontend verification.

**Tech Stack:** PHP 8.2+, Laravel 12, Composer 2, SQLite/PostgreSQL, Node 22, Vite 7, Pest 3, Docker Compose.

## Global Constraints

- Do not invent or vendor third-party dependencies.
- Do not bypass Composer or npm integrity checks.
- Do not activate live provider adapters or include secrets.
- Preserve all tenancy, Vault, audit, capability and WorkCore authority boundaries.
- Every source fix requires a failing regression test first.
- Final claims require committed-tree and extracted-package verification.

---

### Task 1: Baseline and environment diagnosis
- [ ] Run every standalone PHP/JavaScript/verifier test.
- [ ] Record exact unavailable dependencies and PHP extensions.
- [ ] Verify composer.lock and package metadata consistency.

### Task 2: Deployment harness
- [ ] Add Docker Compose services for PHP, PostgreSQL and Node.
- [ ] Add reproducible setup, migration, rollback, test and build scripts.
- [ ] Add environment preflight and secret checks.

### Task 3: Migration and boot contracts
- [ ] Add static migration-order and foreign-reference checks.
- [ ] Add provider/route/command registration checks.
- [ ] Fix source defects only after failing tests reproduce them.

### Task 4: Frontend and worker contracts
- [ ] Validate source/public asset parity and JavaScript syntax.
- [ ] Add queue, scheduler and broadcasting smoke commands for connected CI.
- [ ] Add CI workflow documentation without embedding secrets.

### Task 5: Release verification and packaging
- [ ] Run all reachable gates from a clean committed tree.
- [ ] Package v0.7.0 without caches, vendor, node_modules or secrets.
- [ ] Extract package and independently rerun critical gates.
- [ ] Produce build report, deployment guide, directory inventory and checksum.
