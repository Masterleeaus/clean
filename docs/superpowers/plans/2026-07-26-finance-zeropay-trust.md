# Finance, ZeroPay and Trust Accounting Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add governed WorkCore finance, ZeroPay reconciliation and isolated trust-accounting capabilities to the v0.3.0 integrated application.

**Architecture:** WorkCore Finance owns commercial and accounting records. ZeroPay records payment intent and external observations, then requests authorised finance allocations. Trust Accounting is an append-only, separately permissioned client-money ledger. Titan Zero invokes only registered actions and reads through existing governance.

**Tech Stack:** PHP 8.2, Laravel 12 conventions, integer minor-unit money, SQLite/PostgreSQL-compatible migrations, existing WorkCore action/read registries, dependency-free PHP contract tests.

## Global Constraints

- Every record is company scoped.
- Default currency is AUD; money is stored as integer minor units.
- No secrets outside Titan Vault.
- No direct payment-provider network calls in this pass.
- No destructive changes to issued invoices, journal lines or trust ledger entries.
- Existing v0.3 verification gates must remain green.

---

### Task 1: Money and lifecycle domain rules

**Files:**
- Create: `app/Domains/WorkCore/System/Modules/Finance/Domain/Money.php`
- Create: `app/Domains/WorkCore/System/Modules/Finance/Domain/InvoiceState.php`
- Create: `app/Domains/WorkCore/System/Modules/Finance/Domain/QuoteState.php`
- Create: `app/Domains/WorkCore/System/Modules/Finance/Domain/InvoiceCalculator.php`
- Create: `app/Domains/WorkCore/System/Modules/Finance/Domain/BalancedJournal.php`
- Create: `app/Domains/WorkCore/System/Modules/Payments/Domain/PaymentMatchScorer.php`
- Create: `app/Domains/WorkCore/System/Modules/TrustAccounting/Domain/TrustLedgerPolicy.php`
- Test: `tests/Architecture/FinanceMoneyDomainContractTest.php`

**Interfaces:**
- Produces immutable money arithmetic, state transition and policy helpers used by repositories and actions.

- [ ] Write failing dependency-free tests for currency mismatch, invoice calculations, state transitions, balanced journals, payment matching and trust restrictions.
- [ ] Run `php tests/Architecture/FinanceMoneyDomainContractTest.php`; expect failures for missing classes.
- [ ] Implement the minimal domain classes.
- [ ] Re-run the test; expect all checks to pass.
- [ ] Commit the domain boundary.

### Task 2: Persistence and repository contracts

**Files:**
- Create: `database/migrations/2026_07_26_040000_create_tz_finance_payment_trust_tables.php`
- Create: `app/Domains/WorkCore/System/Modules/Finance/Contracts/FinanceRepositoryContract.php`
- Create: `app/Domains/WorkCore/System/Modules/Finance/Repositories/EloquentFinanceRepository.php`
- Create: `app/Domains/WorkCore/System/Modules/Payments/Contracts/PaymentRepositoryContract.php`
- Create: `app/Domains/WorkCore/System/Modules/Payments/Repositories/EloquentPaymentRepository.php`
- Create: `app/Domains/WorkCore/System/Modules/TrustAccounting/Contracts/TrustAccountingRepositoryContract.php`
- Create: `app/Domains/WorkCore/System/Modules/TrustAccounting/Repositories/EloquentTrustAccountingRepository.php`
- Test: `tests/Architecture/FinancePersistenceContractTest.php`

**Interfaces:**
- Produces company-scoped persistence methods for finance, payments and trust actions.

- [ ] Write failing structural tests for required tables, integer amounts, currency, uniqueness, immutable ledger fields and company scope.
- [ ] Run the persistence contract and confirm expected failures.
- [ ] Implement one compatible migration and focused repository contracts/implementations.
- [ ] Re-run the contract and PHP syntax checks.
- [ ] Commit persistence.

### Task 3: Governed finance actions and reads

**Files:**
- Create action handlers under `app/Domains/WorkCore/System/Modules/Finance/Actions/`.
- Create read handlers under `app/Domains/WorkCore/System/Modules/Finance/ReadModels/`.
- Create: `app/Domains/WorkCore/System/Modules/Finance/Providers/WorkFinanceServiceProvider.php`
- Modify: `app/Domains/WorkCore/WorkCoreServiceProvider.php`
- Modify: `config/workcore.php`
- Test: `tests/Architecture/FinanceRuntimeContractTest.php`

**Interfaces:**
- Registers quote, invoice, credit, expense, receivable and journal writes plus bounded reads.

- [ ] Write failing tests for action keys, read keys, permissions, confirmation risk, capabilities and absence of direct provider/payment execution.
- [ ] Run the runtime test and confirm failures.
- [ ] Implement actions, reads and provider registration.
- [ ] Re-run runtime, namespace and syntax tests.
- [ ] Commit finance runtime.

### Task 4: ZeroPay and trust-accounting execution

**Files:**
- Create action/read handlers under `Modules/Payments` and `Modules/TrustAccounting`.
- Create: `app/Domains/WorkCore/System/Modules/Payments/Providers/WorkPaymentsServiceProvider.php`
- Create: `app/Domains/WorkCore/System/Modules/TrustAccounting/Providers/WorkTrustAccountingServiceProvider.php`
- Test: `tests/Architecture/ZeroPayTrustRuntimeContractTest.php`

**Interfaces:**
- Registers payment sessions, observations, matching, reconciliation and trust ledger workflows without transferring accounting authority.

- [ ] Write failing tests for provider-neutral sessions, idempotent observations, authorised allocations, append-only trust corrections and dual-control disbursement.
- [ ] Run and confirm failures.
- [ ] Implement the minimal governed handlers and providers.
- [ ] Re-run contract, syntax and namespace checks.
- [ ] Commit ZeroPay and trust execution.

### Task 5: Host surfaces, provenance and release

**Files:**
- Modify operations UI and configuration surfaces without creating a second dashboard authority.
- Create: `docs/integration/FINANCE_ZEROPAY_TRUST_DELTA.md`
- Modify: `docs/integration/AUTHORITY_AND_PROVENANCE.md`
- Modify: `docs/integration/REMAINING_WORK.md`
- Modify: `README.md`
- Modify: `BUILD_REPORT.md`
- Test: `tests/Architecture/FinanceHostSurfaceTest.php`

**Interfaces:**
- Adds readable operations summaries, approval queues and explicit deployment boundaries.

- [ ] Write failing host-surface and documentation tests.
- [ ] Implement operations cards, permissions, provenance and release docs.
- [ ] Run all source verification gates.
- [ ] Package as `Titan-Zero-Meetup-WorkCore-Integrated-v0.4.0.zip`, extract separately and rerun core gates.
- [ ] Generate SHA-256 and final report.
