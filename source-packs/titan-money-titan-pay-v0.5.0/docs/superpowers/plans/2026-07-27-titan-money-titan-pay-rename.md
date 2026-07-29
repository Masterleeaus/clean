# Titan Money and Titan Pay Rename Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rename the Finance bounded context and all runtime identifiers to Titan Money while retaining Titan Pay and preserving security and tenancy invariants.

**Architecture:** Perform one exhaustive bounded-context rename rather than adding aliases or a second domain. Rename PHP namespaces, domain files, routes, views, config, permissions, commands, migrations, tables, foreign-key columns, tests, documentation and release packages as one atomic release.

**Tech Stack:** PHP 8.2+, Laravel 12, Blade, Pest/PHPUnit-compatible tests, static PHP verification scripts, ZIP release packaging.

## Global Constraints

- No `App\Domains\Finance` runtime namespace may remain.
- No `finance_*` runtime table may remain.
- No `finance.` route or permission prefix may remain.
- Titan Pay payment verification and idempotency behaviour must remain unchanged.
- The full and delta archives must both pass ZIP integrity tests.

---

### Task 1: Add rename invariants

- [x] Create a failing structural verifier requiring the Titan Money domain and rejecting retired Finance identifiers.
- [x] Run it against the old layout and record the expected failure.

### Task 2: Rename the bounded context

- [x] Rename `app/Domains/Finance` to `app/Domains/TitanMoney`.
- [x] Rename provider, dashboard, audit, outbox, analytics, registry, config, manifest and migration files.
- [x] Replace PHP namespaces and imports.

### Task 3: Rename runtime interfaces

- [x] Change web and API prefixes to `titan-money`.
- [x] Change route and view prefixes to `titanmoney`.
- [x] Change permission prefixes to `titanmoney`.
- [x] Change command prefix to `titan-money`.
- [x] Change config and environment prefixes to Titan Money.

### Task 4: Rename persistence identifiers

- [x] Rename all `finance_*` tables to `titan_money_*`.
- [x] Rename `finance_payment_id` to `titan_money_payment_id`.
- [x] Preserve foreign-key ordering and constraints.

### Task 5: Rename tests, documentation and release metadata

- [x] Rename `tests/Unit/Finance` to `tests/Unit/TitanMoney`.
- [x] Rename seeder, README, architecture documentation and manifests.
- [x] Update final verification report after fresh checks.

### Task 6: Verify and package

- [x] Run core invariant tests.
- [x] Run structural verifier.
- [x] Run PHP syntax scan.
- [x] Scan for retired runtime identifiers.
- [x] Test ZIP integrity.
- [x] Generate checksums.
