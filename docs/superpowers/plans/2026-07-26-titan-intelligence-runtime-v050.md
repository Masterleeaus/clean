# Titan Intelligence Runtime v0.5.0 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add native company-scoped Titan Workspace, Memory, Skills, Agent Runtime, Connector Gateway, Provider Routing, Voice Runtime, announcements and onboarding while preserving Meetup and WorkCore authority.

**Architecture:** A new `App\Titan\Intelligence` first-party subsystem registers governed capability handlers through the existing Titan capability registry. Query-builder repositories persist company-scoped records; credentials remain Vault references; all operational business writes remain in WorkCore.

**Tech Stack:** PHP 8.2+, Laravel 12 conventions, Query Builder, existing Titan tenancy/permissions/Vault/audit/capability registries, standalone PHP contract tests.

## Global Constraints

- Do not copy donor namespaces, controllers, routes, migrations or MagicAI user/conversation/billing models.
- Every persisted record is company-scoped.
- Never accept `company_id` from client payloads as authority.
- Never store API keys, OAuth tokens or telephony credentials outside Titan Vault.
- Agent tools must execute only through registered Titan/WorkCore registries.
- WorkCore remains the sole owner of operational business records.
- Provider adapters are definitions only in this pass; no unconfigured live HTTP calls.
- Marketing and creative generators are deferred.

---

### Task 1: Domain policies

**Files:**
- Create: `app/Titan/Intelligence/Domain/MemoryRetentionPolicy.php`
- Create: `app/Titan/Intelligence/Domain/ModelRoutingPolicy.php`
- Create: `app/Titan/Intelligence/Domain/ShareToken.php`
- Create: `app/Titan/Intelligence/Domain/AgentRunState.php`
- Create: `app/Titan/Intelligence/Domain/VoiceSessionState.php`
- Test: `tests/Standalone/TitanIntelligence/domain.php`

**Interfaces:**
- Produces deterministic policy classes consumed by repositories and handlers.

- [ ] Write failing pure-PHP tests for retention dates, model ranking, token hashing, state transitions and temporary-chat memory exclusion.
- [ ] Run `php tests/Standalone/TitanIntelligence/domain.php`; expect missing-class failure.
- [ ] Implement the five policy classes with no framework dependency.
- [ ] Rerun the test; expect all checks to pass.
- [ ] Commit `feat: add Titan intelligence domain policies`.

### Task 2: Persistence

**Files:**
- Create: `database/migrations/2026_07_26_050000_create_titan_intelligence_runtime_tables.php`
- Create: `app/Titan/Intelligence/Contracts/IntelligenceRepository.php`
- Create: `app/Titan/Intelligence/Repositories/DatabaseIntelligenceRepository.php`
- Test: `tests/Architecture/TitanIntelligencePersistenceContractTest.php`

**Interfaces:**
- Produces company-scoped persistence methods for workspace, memory, skills, agents, connectors, providers, voice, announcements and onboarding.

- [ ] Write a failing structural test that requires all tables, company indexes, Vault references, hashed share tokens and no secret columns.
- [ ] Run the test; expect missing migration/repository failures.
- [ ] Implement the migration, repository contract and database repository.
- [ ] Rerun the structural test; expect pass.
- [ ] Commit `feat: add Titan intelligence persistence`.

### Task 3: Governed capability handlers

**Files:**
- Create focused handlers under `app/Titan/Intelligence/Actions/`
- Create: `app/Titan/Intelligence/Providers/TitanIntelligenceServiceProvider.php`
- Modify: `bootstrap/providers.php`
- Test: `tests/Architecture/TitanIntelligenceRuntimeContractTest.php`

**Interfaces:**
- Produces executable capability ids consumed by `ToolRouter` and listed in conversation context.

- [ ] Write a failing test for capability ids, permissions, server-resolved tenancy, Vault references and no direct WorkCore-table writes.
- [ ] Run the test; expect missing provider/handlers.
- [ ] Implement focused handlers and register them with the capability registry.
- [ ] Bind the repository and register the service provider.
- [ ] Rerun the test; expect pass.
- [ ] Commit `feat: register Titan intelligence capabilities`.

### Task 4: Conversation context and operations surface

**Files:**
- Modify: `app/Titan/AI/ConversationContextBuilder.php`
- Modify: `app/Http/Controllers/Titan/OperationsController.php`
- Modify: `resources/views/titan/operations.blade.php`
- Modify: `public/js/titan-operations.js`
- Test: `tests/Architecture/TitanIntelligenceHostSurfaceTest.php`

**Interfaces:**
- Adds safe counts and summaries without exposing sensitive memories, connector secrets or raw provider responses.

- [ ] Write a failing host-surface test for context summaries and operations panels.
- [ ] Run the test; expect missing surface.
- [ ] Implement bounded summaries and UI sections.
- [ ] Run PHP and JavaScript syntax checks.
- [ ] Rerun the host-surface test; expect pass.
- [ ] Commit `feat: surface Titan intelligence operations`.

### Task 5: Donor registry and release verification

**Files:**
- Create: `config/titan_intelligence.php`
- Create: `docs/integration/BASE_AI_EXTENSION_RECONCILIATION.md`
- Modify: `README.md`
- Modify: `tools/titan_verify.php`
- Create: `BUILD_REPORT.md`

**Interfaces:**
- Records every donor package classification and exposes disabled provider/connector definitions without credentials.

- [ ] Add provider, connector, channel and donor classification configuration.
- [ ] Document accepted, deferred and quarantined packages.
- [ ] Extend the verifier for the new authority and security boundaries.
- [ ] Run all standalone tests, namespace scan, PHP syntax and JS syntax.
- [ ] Package as `Titan-Zero-Meetup-WorkCore-Integrated-v0.5.0.zip`.
- [ ] Extract the ZIP elsewhere and independently rerun critical gates.
- [ ] Generate SHA-256 and final build report.
- [ ] Commit `release: prepare Titan Zero integrated v0.5.0`.
