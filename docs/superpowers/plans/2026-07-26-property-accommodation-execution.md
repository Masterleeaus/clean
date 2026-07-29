# Property and Accommodation Execution Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add governed long-term occupancy/agreement and short-stay accommodation execution to the canonical WorkCore Premises domain.

**Architecture:** Premises remains the physical authority. A single repository owns party roles, agreements, occupancies, access authorisations and the accommodation subdomain. Actions and reads register through existing WorkCore registries and inherit host tenancy, permissions, idempotency, audit and domain events.

**Tech Stack:** PHP 8.2, Laravel 12 conventions, Query Builder, SQLite/PostgreSQL-compatible migrations, dependency-free PHP contract tests.

## Global Constraints
- Tables use `tz_` names and include `company_id`.
- Public references use 26-character ULIDs.
- Default money currency is `AUD`; no payment or invoice authority is added.
- No donor routes, tenancy, identity, finance or permission systems enter runtime.
- Reservation windows are half-open `[arrival, departure)`.
- All writes execute through `BusinessActionDispatcher`; reads use `ReadModelExecutor`.

---

### Task 1: Domain policies
**Files:**
- Create: `app/Domains/WorkCore/System/Modules/Premises/Domain/Property/*.php`
- Create: `app/Domains/WorkCore/System/Modules/Premises/Domain/Accommodation/*.php`
- Test: `tests/WorkCore/property_accommodation_domain.php`

- [ ] Write tests for agreement, reservation, stay and housekeeping transitions, overlap, nights and AUD rate calculations.
- [ ] Run `php tests/WorkCore/property_accommodation_domain.php` and verify failure because classes are absent.
- [ ] Implement deterministic stateless policies.
- [ ] Rerun and verify all assertions pass.
- [ ] Commit.

### Task 2: Persistence and repository
**Files:**
- Create: `database/migrations/2026_07_26_030000_create_tz_property_accommodation_tables.php`
- Create: `app/Domains/WorkCore/System/Modules/Premises/Contracts/PropertyAccommodationRepositoryContract.php`
- Create: `app/Domains/WorkCore/System/Modules/Premises/Repositories/EloquentPropertyAccommodationRepository.php`
- Test: `tests/Architecture/PropertyAccommodationPersistenceContractTest.php`

- [ ] Write structural assertions for twelve tenant-scoped tables, foreign keys, managed-record flags and no invoice/payment tables.
- [ ] Run the test and verify failure.
- [ ] Implement schema and repository methods for agreements, occupancy, reservations, check-in/out, housekeeping, charges and reads.
- [ ] Run syntax and structural tests.
- [ ] Commit.

### Task 3: Governed actions and reads
**Files:**
- Create: `app/Domains/WorkCore/System/Modules/Premises/Actions/*Agreement*.php`
- Create: `app/Domains/WorkCore/System/Modules/Premises/Actions/*Occupancy*.php`
- Create: `app/Domains/WorkCore/System/Modules/Premises/Application/Accommodation/Actions/*.php`
- Create: `app/Domains/WorkCore/System/Modules/Premises/Application/Accommodation/ReadModels/*.php`
- Modify: `app/Domains/WorkCore/System/Modules/Premises/Providers/WorkPremisesServiceProvider.php`
- Modify: `config/workcore.php`
- Test: `tests/Architecture/PropertyAccommodationRuntimeContractTest.php`

- [ ] Write assertions for action/read registration, permissions, confirmation risk and finance separation.
- [ ] Verify failure.
- [ ] Implement action wrappers and read handlers over the repository.
- [ ] Register capability, permissions, action metadata and typed events.
- [ ] Rerun tests and commit.

### Task 4: Host surface, documentation and release
**Files:**
- Modify: `resources/views/titan/operations.blade.php`
- Modify: `app/Http/Controllers/Titan/TitanOperationsController.php`
- Create: `docs/integration/PROPERTY_ACCOMMODATION_DELTA.md`
- Modify: `README.md`, `BUILD_REPORT.md`, `config/workcore.php`

- [ ] Add tenant-safe agreement, occupancy and accommodation summary data to Operations.
- [ ] Document accepted donor concepts, rejected duplicate authorities and finance boundary.
- [ ] Run `php tools/titan_verify.php`, namespace scan, AI tests, Maps tests, all new tests and PHP syntax checks.
- [ ] Package v0.3.0, extract independently, rerun the gates and create SHA-256.
- [ ] Commit release checkpoint.
