# Titan Zero Multi-Step Upgrade Plan

## Purpose

This document is the root execution plan for upgrading the `clean` repository into the canonical Titan Zero Business Operating System workspace.

The target architecture is:

```text
MagicAI Host
  -> Titan Zero orchestration
  -> Five-Tier AI workforce
  -> Interaction Engine
  -> WorkCore application services
  -> WorkCore domains
  -> local/offline and cloud persistence
```

WorkCore remains the only authoritative writer of operational business records. Titan Zero provides conversation, intent recognition, planning, delegation and generative UI. The Interaction Engine owns structured workflows, validation sequences, approvals and resumable execution.

## Working Branch

`chatgpt/titan-zero-upgrade-workspace`

All upgrade work should be committed to this branch or to child branches created from it.

## Core Source Inputs

The workspace must account for these supplied systems:

- MagicAI v10.91 + WorkCore merged Laravel base
- Titan Zero Chatbot PWA Pass 12
- Titan Zero Extension SDK v2
- Base App System Extensions
- AI System Extensions
- Marketing and Creative Extensions
- Titan BOS modules
- AI-powered no-code mobile app builder
- MobileKit mobile UI kit
- Website-to-app builder
- WorkCore architecture and extension specifications

Do not merge donor applications wholesale. Extract reusable code, patterns, components and build infrastructure into Titan-owned modules.

---

# Phase 0 — Workspace and Safety Baseline

## Step 0.1 — Repository inventory

- Record framework and dependency versions.
- Identify Laravel, PHP, JavaScript, TypeScript, PWA, native-wrapper and build-system roots.
- List all modules, domains, service providers, routes, migrations and frontend entry points.
- Produce checksums and file-count baselines.
- Detect nested archives, generated assets, vendor folders and duplicate source trees.

## Step 0.2 — Development safety

- Keep `main` untouched.
- Add or confirm `.editorconfig`, `.gitattributes`, `.gitignore` and environment templates.
- Prevent secrets, API keys, local databases, user uploads and build artefacts from being committed.
- Add architecture decision records under `docs/adr/`.
- Add repeatable audit scripts under `tools/audit/`.

## Exit criteria

- Repository boots or its current boot failures are documented.
- Baseline tests and build commands are known.
- No supplied source remains unclassified.

---

# Phase 1 — Canonical Architecture

## Step 1.1 — Enforce ownership boundaries

- MagicAI owns authentication, tenancy, billing, host administration and extension lifecycle.
- Titan Zero owns context, reasoning, planning, delegation and UI orchestration.
- Interaction Engine owns forms, wizards, approvals, checklists and workflow state.
- WorkCore owns all operational entities, validation, permissions, transactions, audit and domain events.
- PWA owns device presentation, local storage, offline execution and synchronisation UX.

## Step 1.2 — Remove architectural bypasses

Find and replace:

- AI classes writing directly to Eloquent models or SQL.
- PWA routes writing directly to operational tables.
- Controllers containing domain rules.
- Duplicate customer, job, invoice, scheduling, tenancy or permission systems.
- Temporary `integration`, `imported`, `legacy-copy`, `source`, `staging` or parallel-domain folders.

## Step 1.3 — Add architecture tests

Tests must fail when:

- Titan or agents import persistence models directly.
- Cross-domain writes bypass application services.
- tenant context is absent.
- an operational write omits audit or event emission.

## Exit criteria

A single dependency direction is enforced by tests.

---

# Phase 2 — WorkCore Completion

## Step 2.1 — Domain audit

Audit and normalise:

- Foundation
- CRM
- Premises
- Workforce
- Scheduling
- Operations
- Inventory
- Finance
- Documents
- Compliance
- Knowledge

For every domain verify entities, value objects, repositories, policies, commands, queries, events, migrations, factories, seeders, APIs and tests.

## Step 2.2 — Canonical command/query API

Standardise command envelopes with:

- command ID
- client-generated UUID
- tenant/company/branch/workspace IDs
- user, agent and device IDs
- correlation and causation IDs
- expected record version
- idempotency key
- payload schema version

Standardise query envelopes, pagination, filtering and permission-aware projections.

## Step 2.3 — Events, audit and outbox

Every significant action must produce:

- immutable domain event
- audit record
- synchronisation change entry
- analytics projection input
- notification opportunity
- AI memory input where allowed

Use an outbox pattern and idempotent consumers.

## Exit criteria

A complete job lifecycle is traceable from command to transaction, audit, event and response.

---

# Phase 3 — Interaction Engine

## Step 3.1 — Install as a first-class domain

Wire the Interaction Engine into the Laravel host through canonical service providers, routes, configuration, migrations, policies, queues and tests.

## Step 3.2 — Universal workflow schemas

Create versioned workflows for:

- customer creation
- property creation
- quote creation and approval
- job booking
- recurring service setup
- worker assignment and dispatch
- job start, pause and completion
- invoicing and payment
- inspections
- incidents
- stock requests
- employee onboarding
- document upload
- conflict resolution

Each workflow defines permissions, steps, conditions, validation, offline eligibility, approvals, cancellation, recovery and completion commands.

## Step 3.3 — Shared workflow renderer

The PWA must render schemas instead of maintaining separate hardcoded forms for every workflow.

## Exit criteria

A workflow can start online, continue offline when eligible, resume and commit through WorkCore.

---

# Phase 4 — Extension Platform and Five-Tier AI

## Step 4.1 — Canonical extension SDK

Normalise all extensions around one manifest contract containing:

- ID and semantic version
- category
- dependencies
- permissions
- service provider
- routes
- migrations
- settings
- install/uninstall behaviour
- health checks
- tests
- UI, workflow, AI and WorkCore contributions
- offline policy

## Step 4.2 — Extension pack classification

Classify every uploaded extension as:

- complete
- incomplete
- duplicate
- obsolete
- unsafe
- backend-only
- UI-only
- reusable library
- valid feature extension

Create install profiles for base, cleaning, field service, property management, marketing and enterprise deployments.

## Step 4.3 — Five-Tier AI wiring

Canonical hierarchy:

1. Titan Zero orchestrator
2. Managers
3. Assistants and specialists
4. Action agents
5. Skills, tools and providers

Map Uno, Duo and Trio terminology into this hierarchy without creating duplicate runtimes.

## Step 4.4 — Typed WorkCore tools

Every agent action must use registered WorkCore tools with input/output schemas, delegated permission, approval threshold, tenant scope, idempotency and audit requirements.

## Exit criteria

Every active agent is discoverable, permission-bound and unable to bypass WorkCore.

---

# Phase 5 — Shared PWA Runtime

## Step 5.1 — Offline runtime hardening

Verify and repair:

- web app manifest
- service worker
- safe cache strategy
- IndexedDB repositories
- encrypted vault
- local outbox
- retry policy
- device identity
- tenant isolation
- attachment queue
- conflict preservation
- application update lifecycle
- safe logout

Never cache credentials or sensitive API responses. Never silently delete unsynchronised records.

## Step 5.2 — Synchronisation protocol

Use delta sync with:

- client-generated UUIDs
- expected versions
- idempotent commands
- conflict snapshots
- audit events for merges
- explicit manual conflict resolution when deterministic merging is unsafe

## Step 5.3 — Local search and knowledge

Create permission-filtered local indexes for customers, properties, jobs, invoices, assets, documents, knowledge and messages.

## Exit criteria

An eligible field workflow completes without network access and safely reconciles later.

---

# Phase 6 — Titan Design System and Adaptive Shell

## Step 6.1 — Design tokens

Create one shared system for:

- colour
- typography
- spacing
- radius
- elevation
- motion
- icons
- density
- breakpoints
- accessibility
- focus and keyboard behaviour
- dark and light modes

## Step 6.2 — Component library

Build shared components for:

- global header
- command bar
- app switcher
- navigation rail
- mobile bottom navigation
- drawers and inspectors
- customer, property, job, worker, route, quote and invoice cards
- tables, lists, maps, calendars and timelines
- dynamic forms, wizards and checklists
- evidence, signature and document viewers
- AI recommendations and approvals
- sync, offline queue and conflict states
- notifications, empty, loading and error states

## Step 6.3 — Donor extraction

From MobileKit, extract and modernise useful mobile patterns such as action sheets, touch navigation, headers, tabs, search, timelines, notifications, skeletons and wizards.

From the no-code builder, extract component registry, screen schema, property inspector, preview and packaging patterns.

From the website-to-app builder, extract native build, icon, splash, remote configuration and white-label automation patterns.

Do not retain obsolete donor framework structure unless it provides unique required behaviour.

## Step 6.4 — Adaptive shell

One shell must compose differently for:

- mobile: single column, bottom navigation, large touch targets, capture-first workflows
- tablet: navigation rail, split workspace, contextual inspector and map/timeline panes
- desktop: navigation rail, central workspace, right inspector, keyboard shortcuts and resizable panels

## Exit criteria

The same entity and route render appropriately on phone, tablet and desktop.

---

# Phase 7 — Fourteen Titan Applications

All applications use the shared shell, component registry, WorkCore APIs, Interaction Engine and PWA runtime.

## Application registry

1. Titan Zero — owner briefing, approvals, cross-domain exceptions and app health
2. Titan Go — field work, jobs, navigation, capture, checklists and offline queue
3. Titan Hub — customer portal, bookings, quotes, invoices, payments and messages
4. Titan Dispatch — board, schedule, map, routes, availability and exceptions
5. Titan Money — quotes, invoices, payments, expenses, refunds and ZeroPay
6. Titan Teams — workforce, availability, timesheets, skills and certifications
7. Titan Analytics — metrics, reports, trends, forecasts and anomalies
8. Titan Front Desk — enquiries, reception, lead intake, booking and callbacks
9. Titan Marketing — campaigns, segments, promotions, reviews, email and SMS
10. Titan Social — social accounts, calendar, posts, approvals and inbox
11. Titan Sprout — vertical, business, workflow and application builder
12. Titan Locker — stock, equipment, suppliers, vehicle inventory and barcode capture
13. Titan Office — documents, contracts, resources, schedules and signatures
14. Titan Quality — inspections, incidents, evidence, compliance and quality scoring

## App manifest contract

Each app defines:

- ID, label and icon
- roles and permissions
- routes and navigation
- WorkCore domains and projections
- available commands and workflows
- AI capabilities
- offline records
- settings and notifications
- mobile, tablet and desktop layouts

## Delivery groups

### Group A — Operational MVP

- Titan Zero
- Titan Go
- Titan Dispatch
- Titan Money
- Titan Quality

### Group B — People and customers

- Titan Hub
- Titan Teams
- Titan Front Desk
- Titan Office

### Group C — Growth and intelligence

- Titan Analytics
- Titan Marketing
- Titan Social

### Group D — Expansion

- Titan Locker
- Titan Sprout

## Exit criteria

Every app is loaded through the registry and has live data, permissions, workflows, offline policy and responsive layouts.

---

# Phase 8 — Titan Sprout Builder

## Step 8.1 — Controlled no-code model

Sprout must generate approved schemas, not unrestricted executable code.

A screen schema contains:

- layout
- registered components
- WorkCore data sources
- Interaction Engine workflows
- permissions
- offline policy
- responsive rules
- navigation

## Step 8.2 — Builder workspaces

Provide:

- Apps
- Screens
- Components
- Data
- Workflows
- AI
- Theme
- Permissions
- Offline
- Preview
- Publish

## Step 8.3 — Vertical packaging

Support creating and versioning industry packs that extend WorkCore rather than duplicating it.

## Exit criteria

A new cleaning or field-service screen can be composed and published without core-code modification.

---

# Phase 9 — Native Packaging

## Step 9.1 — Shared runtime first

Keep business logic and UI source in the shared PWA.

## Step 9.2 — Thin native wrappers

Use Capacitor-style native bridges for:

- secure storage
- biometrics
- camera
- filesystem
- push notifications
- background tasks
- deep links
- share targets
- barcode/QR scanning
- geolocation

Use SQLite or SQLCipher only in native wrappers where justified; use IndexedDB in the browser PWA.

## Step 9.3 — Build outputs

Produce:

- Titan Go mobile app
- Titan Hub customer app
- full Titan Zero mobile container
- tablet-optimised Dispatch installation
- desktop installable PWA
- white-label build pipeline

## Exit criteria

Native packages can be built without forking WorkCore logic or duplicating app code.

---

# Phase 10 — Security, Quality and Release

## Step 10.1 — Security review

Test tenant leakage, object authorisation, agent escalation, secrets, XSS, CSRF, SQL injection, uploads, webhooks, queue poisoning, path traversal, sync replay and unsafe extension loading.

## Step 10.2 — Performance review

Measure cold/warm/offline boot, large lists, dispatch board, maps, attachment sync, low-memory phones, slow networks and queue throughput.

## Step 10.3 — Test matrix

Required layers:

- unit
- domain
- architecture
- API integration
- workflow
- extension
- PWA
- offline and conflict
- mobile, tablet and desktop viewport
- accessibility
- end-to-end

## Step 10.4 — Production package

Deliver installation, upgrade, rollback, environment, queue, scheduler, native build, extension development and architecture documentation plus checksums and release notes.

## Final acceptance workflow

```text
Create customer
 -> create property
 -> create quote
 -> approve quote
 -> book job
 -> dispatch worker
 -> complete offline checklist
 -> upload evidence
 -> synchronise
 -> generate invoice
 -> receive payment
 -> complete inspection
 -> show owner briefing
```

---

# Recommended Execution Order

1. Repository and dependency inventory
2. Architecture boundaries
3. WorkCore domain and API repair
4. Events, audit and outbox
5. Interaction Engine integration
6. Extension SDK normalisation
7. Five-Tier AI and WorkCore tools
8. PWA offline and sync runtime
9. Design system and adaptive shell
10. Operational five-app MVP
11. Remaining nine applications
12. Titan Sprout builder
13. Native packaging
14. Security, performance and release hardening

---

# Completion Rules

A feature is complete only when:

- source code exists in the canonical location
- dependencies are registered
- routes and permissions are wired
- database changes are migratable
- UI is reachable
- offline behaviour is defined
- tests pass
- audit and event behaviour is verified
- documentation is updated

Do not count placeholders, empty interfaces, copied donor apps, disabled routes or unwired services as completed functionality.
