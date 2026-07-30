# Interaction Engine, Wizard and Five-Tier Intelligence Architecture

**Status:** Canonical architecture and current-source status for the reconciliation programme.

**Current source baseline:** `main` at `fa607d769a4f72ba287801b027cc42dcf56aa549`, reconciled on `agent/documentation-reconciliation`.

This document separates three related but different systems:

1. the universal Interaction Engine package;
2. the WorkCore operational Wizard module;
3. the Titan Zero five-tier intelligence runtime carried by the Chatbot/PWA.

They may cooperate, but they must not become competing operational authorities.

## 1. Physical runtime inventory

| Runtime | Current path | Evidence status | Intended role |
|---|---|---|---|
| Universal Interaction Engine | `packages/titanzero/interaction-engine` | Source present: 386 files before reconciliation cleanup, including provider, routes, migrations, tests, offline TypeScript and 80-engine library | Interaction definitions, sessions, clarification, confidence, evidence, policy preparation, wizard execution and command preparation |
| Removed duplicate package metadata | `packages/titan-zero/interaction-engine` | Superseded and removed during Pass 3; contained only one conflicting `composer.json` for the same package name | None |
| WorkCore Wizards | `app/Domains/WorkCore/System/Modules/Wizards` | Source present and provider referenced by WorkCore configuration; 33 files | Operational-domain wizard definitions, read models, APIs and governed WorkCore actions |
| Primary Chatbot TitanAI runtime | `app/Extensions/Chatbot/System/TitanAI` | Source present: 864 files | Titan Zero orchestration, managers, assistants, specialists, action agents, tools, skills and device-facing integration |
| Secondary Chatbot TitanAI copy | `app/Extensions/TitanZeroChatbot/System/TitanAI` | Exact file-for-file duplicate of the primary TitanAI tree at inventory time: 864 of 864 files identical | Compatibility/reference copy only until separately removed or proven necessary |
| Standalone app domain | `app/Domains/InteractionEngine` | Does not exist | Must not be invented as a parallel implementation while the package remains canonical |

The full generated evidence is in:

- `docs/inventory/INTERACTION_INTELLIGENCE_RUNTIME_INVENTORY.md`
- `docs/inventory/INTERACTION_INTELLIGENCE_RUNTIME_INVENTORY.json`

## 2. Canonical logical ownership

### 2.1 Universal Interaction Engine

The Interaction Engine owns:

- interaction and wizard definitions;
- session and transition state;
- question resolution and validation;
- clarification and abstention;
- confidence and evidence processing;
- authority and approval preparation;
- command preparation and mapping;
- offline-safe interaction drafts and command outbox behaviour;
- cognitive observations, decisions and outcomes;
- rendering contracts and local guidance.

It does **not** own:

- host users, companies or memberships;
- operational permissions;
- customers, jobs, appointments, invoices or other WorkCore records;
- direct operational table mutation;
- an independent finance ledger;
- unrestricted AI authority.

The Interaction Engine may prepare a proposed command. WorkCore decides whether an operational action may execute.

### 2.2 WorkCore Wizards

WorkCore Wizards is an operational bounded module inside WorkCore. It owns:

- WorkCore-specific wizard capabilities;
- operational wizard configuration;
- wizard-facing WorkCore APIs and read models;
- mapping validated operational inputs into registered WorkCore actions;
- WorkCore permission, entitlement and tenant-aware execution behaviour.

WorkCore Wizards is not a replacement for the universal Interaction Engine. It is the operational-domain adapter and execution surface used when a wizard affects WorkCore truth.

### 2.3 Titan Zero five-tier intelligence

The logical five-tier model is:

| Tier | Responsibility | Current physical evidence |
|---|---|---|
| Tier 0 | Titan Zero orchestrator: intent, mode selection, planning and delegation | `tier0/` contains the orchestrator |
| Tier 1 | Managers coordinating broad business domains | `tier1/` contains 30 files |
| Tier 2 | Assistants and specialists performing scoped reasoning | `tier2/` contains 85 files |
| Tier 3 | Action agents preparing or requesting concrete actions | `tier3/` contains 69 tier-associated files in the generated inventory |
| Tier 4 | Governed deterministic tools and capability adapters | Represented by `tools/`, capability registries and WorkCore action bridges; there is no verified standalone `tier4/` directory |

A missing physical `tier4/` folder does not authorise agents to create a parallel tool system. Tier 4 is the governed execution boundary, not merely a directory name.

## 3. Required execution path

```text
User, device, channel or integration
        ↓
MagicAI authentication and active company membership
        ↓
Titan Zero Tier 0 planning and delegation
        ↓
Tier 1 manager / Tier 2 assistant or specialist
        ↓
Interaction Engine clarification, evidence and approval preparation
        ↓
Tier 3 action agent prepares a registered command
        ↓
Tier 4 governed tool or WorkCore bridge
        ↓
WorkCore BusinessActionDispatcher
        ↓
Tenant context match
        ↓
Capability entitlement
        ↓
Actor permission
        ↓
Confirmation and risk policy
        ↓
Idempotent transactional action
        ↓
Audit, domain events, outbox and synchronisation
```

No tier may bypass the lower execution boundary. Confidence, model quality or manager status does not create permission.

## 4. Activation truth

### 4.1 Interaction Engine source is present

The canonical package contains:

- `TitanZero\Interaction\Providers\InteractionServiceProvider`;
- web and API routes;
- interaction and wizard engines;
- migrations in two package migration locations;
- local/offline TypeScript and built assets;
- package tests and host tests;
- the imported 80-engine library.

### 4.2 Connected host activation remains unproven

At the current baseline:

- root `composer.json` does not register `packages/titanzero/interaction-engine` as a path repository;
- root `composer.json` does not require `titanzero/interaction-engine`;
- `config/titan-zero.php` contains `interaction_engine_enabled`;
- `TitanZeroFeatureFlags::coreProviderClassNames()` does not currently add the Interaction Engine provider;
- `tests/Unit/InteractionEnginePackageContractTest.php` expects one explicit provider registration in `config/app.php`;
- repository search does not find that explicit provider registration in `config/app.php`;
- `tests/Feature/HostBootTest.php` expects the provider to be loaded.

Therefore the source, tests and host wiring are drifted. The package must be described as **source-present but not connected-host verified** until one intentional activation model is implemented and its tests pass from a clean checkout.

### 4.3 Required activation decision

Choose exactly one model:

#### Explicit host registration

- register and require the canonical package through root Composer;
- keep package auto-discovery disabled;
- register `InteractionServiceProvider` exactly once through the Titan Zero host/provider graph;
- make `interaction_engine_enabled` control that registration;
- keep the explicit-registration contract test.

#### Composer auto-discovery

- register and require the canonical package through root Composer;
- add the package provider under `extra.laravel.providers`;
- remove any explicit duplicate registration;
- update tests to enforce one auto-discovered provider.

The repository currently contains evidence of both intended approaches but a complete implementation of neither. Do not combine them.

## 5. WorkCore Wizard activation

WorkCore configuration references `WorkWizardsServiceProvider`, and the provider exists under the canonical WorkCore domain. This makes WorkCore Wizards a stronger active-runtime candidate than the universal package at the current baseline.

Activation still requires connected verification of:

- provider loading exactly once;
- route names and middleware;
- capability and permission registration;
- tenant resolution;
- action dispatch through `BusinessActionDispatcher`;
- no direct model mutation from wizard controllers or AI helpers.

## 6. Chatbot TitanAI duplication

The generated comparison found:

- 864 files in the primary Chatbot TitanAI tree;
- 864 files in the TitanZeroChatbot TitanAI tree;
- 864 common relative paths;
- 864 byte-identical files;
- zero divergent files.

The current canonical location is:

`app/Extensions/Chatbot/System/TitanAI`

The duplicate location is:

`app/Extensions/TitanZeroChatbot/System/TitanAI`

Until a dedicated extension reconciliation confirms every route, provider, asset, manifest and external caller:

- the primary Chatbot tree is the only intended active TitanAI runtime;
- the secondary tree is compatibility/reference-only;
- extension discovery must not activate both;
- no new work may be added only to the secondary copy;
- changes must not be duplicated manually across both trees;
- removal must occur in a focused source PR, not by deleting 864 files without dependency tracing.

## 7. Embedded WorkCore runtime rule

The Chatbot TitanAI tree contains an embedded `workcore-runtime/native-runtime` copy with WorkCore namespaces, providers and migrations.

That copy is compatibility or device-runtime material only. It must not:

- autoload as a second `App\Domains\WorkCore` implementation;
- load server migrations independently;
- register a second WorkCore AI provider graph;
- write operational tables directly;
- override the canonical WorkCore domain under `app/Domains/WorkCore`.

Unique device contracts may be extracted. Server authority remains with canonical WorkCore.

## 8. Migration and persistence risks

The Interaction Engine package currently contains migrations under both:

- `database/migrations/`;
- `src/Migrations/`.

Several filenames are dated after the current reconciliation date. Before activation:

1. build a table ownership map;
2. confirm migration ordering and supported database engines;
3. detect duplicate table creation across package, WorkCore and Chatbot copies;
4. establish whether package migrations are publishable, loadable in place or both;
5. test fresh installation and upgrade installation;
6. ensure tenant, actor and device identifiers use compatible types.

Future-dated filenames are not proof of failure, but they are a release-ordering risk that must be tested.

## 9. Provider and registry rules

- One canonical Interaction Engine provider may load.
- One canonical Titan Zero orchestrator may resolve.
- One authoritative WorkCore action registry may execute operational actions.
- WorkCore Wizards may register operational wizard capabilities but not a second universal Interaction Engine.
- Chatbot providers may present and adapt capabilities but may not claim host or WorkCore authority.
- Capability registries must have explicit ownership and collision detection.
- Null or in-memory implementations are test/offline fallbacks unless production configuration explicitly selects them.

## 10. Documentation disposition

Reference documents under `docs/reference/titan-library/` remain source material, not runtime truth.

Package-local changelogs and build reports remain with the package because they explain cumulative source lineage. They do not override this canonical architecture document or current host wiring evidence.

The following are superseded as current guidance:

- branch-specific Interaction/Wizard upgrade plans under `docs/archive/plans/`;
- old phase status reports under `docs/archive/status/`;
- the removed metadata-only package root under `packages/titan-zero/interaction-engine`.

## 11. Verification gates

Before declaring the universal Interaction Engine active:

1. root Composer path and package requirement are coherent;
2. provider activation uses one model and loads once;
3. Laravel boots from a clean checkout;
4. Interaction routes load once with unique names;
5. package and host tests pass;
6. migrations pass on fresh and upgrade databases;
7. WorkCore mutations route through `BusinessActionDispatcher`;
8. tenant, actor, device, correlation and causation context survive queues and offline replay;
9. Chatbot and TitanZeroChatbot cannot both activate the same TitanAI classes;
10. the embedded WorkCore runtime cannot shadow canonical WorkCore;
11. local/offline and connected command behaviour have contract parity;
12. credentials remain in Vault and outside service-worker caches.

## 12. Current disposition summary

| Item | Disposition |
|---|---|
| `packages/titanzero/interaction-engine` | Canonical universal package source |
| `packages/titan-zero/interaction-engine` | Removed metadata-only duplicate |
| WorkCore Wizards | Canonical operational wizard module |
| Chatbot TitanAI | Canonical intended five-tier runtime location |
| TitanZeroChatbot TitanAI | Exact compatibility duplicate; frozen pending focused removal |
| Embedded Chatbot WorkCore server copy | Compatibility/reference only; never authoritative |
| Interaction Engine host activation | Confirmed wiring gap; not yet verified active |
| Five-tier operational mutations | Must terminate at governed WorkCore actions |
