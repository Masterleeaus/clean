# Titan Creative and Marketing v0.6 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a governed first-party creative and marketing runtime from the donor concepts while preserving Titan, Meetup and WorkCore authorities.

**Architecture:** Add `App\Titan\Creative` beside Titan Intelligence. Store creative and marketing workflow state in company-scoped tables; delegate provider selection and credentials to Titan Intelligence and Titan Vault. Expose registered capabilities and bounded summaries only.

**Tech Stack:** PHP 8.2+, Laravel 12 conventions, SQLite/PostgreSQL-compatible migrations, standalone PHP contract tests, Vite JavaScript.

## Global Constraints

- No donor directory may enter runtime autoload.
- No plaintext provider credential may be stored.
- No direct provider HTTP client is activated in this pass.
- No duplicate CRM, conversation, billing, user or WorkCore authority is created.
- Every record is server-scoped by active company context.
- Every capability has an explicit permission.
- Summary surfaces expose counts only.

---

### Task 1: Domain policies
- [ ] Write failing standalone tests for lifecycle, approval, campaign dates and provider references.
- [ ] Implement focused domain classes.
- [ ] Run domain tests and commit.

### Task 2: Persistence
- [ ] Write failing schema/repository tests.
- [ ] Add the 21-table migration, repository contract and database repository.
- [ ] Run persistence tests and commit.

### Task 3: Governed capabilities
- [ ] Write failing capability-registration tests.
- [ ] Add action handlers and `TitanCreativeServiceProvider`.
- [ ] Register the provider with the host.
- [ ] Run runtime tests and commit.

### Task 4: Host surface
- [ ] Write failing host-interface tests.
- [ ] Add bounded creative/marketing counts to Titan summary, conversation context and Operations.
- [ ] Run host and JavaScript syntax tests and commit.

### Task 5: Reconciliation and release
- [ ] Record all donor decisions in configuration and documentation.
- [ ] Extend the release verifier.
- [ ] Generate `APP_DIRECTORY_TREE.txt` and `APP_DIRECTORY_SUMMARY.md`.
- [ ] Run full source verification, package, extract and independently retest.
