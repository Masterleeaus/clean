# Titan Zero Repository Stabilisation and Upgrade Plan

## Purpose

This plan converts the recovered cumulative source tree into a secure, testable and maintainable Titan Zero application without replacing the existing MagicAI host, WorkCore domain or Titan Zero Chatbot extension.

The governing rule is simple: preserve real functionality, establish one authority for each responsibility, and remove duplicate or unreachable implementations only after references and runtime wiring have been verified.

## Target authority model

- **MagicAI host:** authentication, users, companies, subscriptions, provider configuration and application shell.
- **WorkCore:** authoritative operational business records, commands, policies, events, audit trails and sync contracts.
- **Titan AI:** reasoning, orchestration, confidence, governance and model selection.
- **Titan Zero Chatbot:** presentation, PWA/device runtime, local storage, offline interaction and generative UI.
- **Interaction Engine:** governed execution pipeline between actors, AI, WorkCore and device runtimes.

## Delivery phases

### Phase 0 — Repository safety and recovery controls

Status: in progress.

1. Work only from isolated repair branches.
2. Keep the repository private because it contains licensed source.
3. Remove tracked runtime secrets and destructive import/sanitiser workflows.
4. Rotate any keys exposed in historical commits.
5. Add repeatable CI checks for Composer, PHP syntax, Laravel boot, routes, tests and frontend builds.
6. Record each repair pass in commits and pull requests rather than force-pushing `main`.

Exit criteria:

- No live `.env`, private key or embedded transport key is tracked in the branch tree.
- No workflow can rewrite `main` or create orphan history.
- Basic repository validation runs automatically.

### Phase 1 — Boot and dependency baseline

1. Validate `composer.json`, path repositories and lockfiles.
2. Confirm all local packages referenced by Composer exist.
3. Run PSR-4/autoload validation and PHP syntax checks.
4. Boot Laravel with a generated test environment.
5. verify configuration caching, route discovery and service-provider registration.
6. Catalogue missing classes, interfaces and package dependencies.

Exit criteria:

- `composer install` succeeds from a clean checkout.
- Laravel boots without container-resolution failures.
- No duplicate class or PSR-4 errors remain.

### Phase 2 — Canonical WorkCore boundary

1. Treat `app/Domains/WorkCore` as the canonical server-side WorkCore implementation.
2. Inventory the PHP WorkCore copy embedded under the chatbot runtime.
3. Compare duplicate files and preserve any unique capabilities.
4. Move unique server functionality into the canonical domain.
5. Retain only device-side contracts and TypeScript/JavaScript runtime code in the chatbot extension.
6. Add architecture tests preventing a second Laravel WorkCore domain from being introduced.

Exit criteria:

- One server-side WorkCore domain exists.
- Chatbot code consumes WorkCore contracts/APIs rather than carrying a second PHP backend.

### Phase 3 — Tenant and identity consolidation

1. Select one tenant-context contract.
2. Separate request-scoped context from serialisable queue snapshots.
3. Standardise company/user/device identifiers and column types.
4. Consolidate tenant resolvers and company-scoping traits.
5. Verify queue restoration, scheduled jobs, AI tool calls and offline sync all restore the same tenant context.
6. Add cross-tenant isolation tests.

Exit criteria:

- One canonical tenant context and one host adapter are used everywhere.
- Cross-company access tests fail closed.

### Phase 4 — Provider, adapter and module wiring

1. Audit every service-container binding.
2. Replace production null adapters with explicit MagicAI adapters.
3. Verify menu, notification, storage, payment, messaging, calendar and geocoding contracts.
4. Classify every WorkCore module as enabled, optional, dormant or legacy.
5. Disable unused verticals by manifest/config rather than deleting code prematurely.
6. Ensure retained modules register providers, migrations, routes, permissions and UI entries exactly once.

Exit criteria:

- No required interface resolves to a null implementation in integrated production mode.
- Every enabled module is reachable and authorised.

### Phase 5 — Route and API consolidation

1. Generate and analyse the complete route table.
2. Remove duplicate names and method/URI collisions.
3. Standardise `/api/v1/workcore` prefixes.
4. Apply consistent authentication, tenant, capability and API-exception middleware.
5. Keep controllers thin and move business decisions into WorkCore application actions.
6. Version device sync and AI tool endpoints explicitly.

Exit criteria:

- No duplicate route names or method/URI pairs.
- All operational APIs enforce authentication and tenant context.

### Phase 6 — Database and migration repair

1. Build a complete table and migration ownership map.
2. Detect duplicate table creation and altered historical migrations.
3. Validate foreign-key types and indexes.
4. Reconcile MagicAI users/companies with WorkCore membership tables.
5. Mark Rewind/legacy models as canonical, compatibility-only or obsolete.
6. Test fresh install and upgrade from the latest supported deployed schema.

Exit criteria:

- Fresh migration and upgrade migration both succeed.
- No table has competing model or migration ownership without an explicit compatibility layer.

### Phase 7 — AI and Interaction Engine authority

1. Separate Titan AI orchestration from WorkCore business authority.
2. Consolidate agent, tool, memory, usage and governance registries.
3. Make WorkCore actions the only authoritative mutation path.
4. Route chatbot, offline and hosted AI execution through the Interaction Engine.
5. Enforce confidence, confirmation, idempotency, authorisation and audit policies.
6. Add contract tests for local/offline and cloud execution parity.

Exit criteria:

- One tool/action catalogue is authoritative.
- AI cannot bypass WorkCore policy, confirmation or audit controls.

### Phase 8 — PWA and offline runtime alignment

1. Confirm IndexedDB schemas match canonical WorkCore DTOs.
2. Standardise UUID, tenant, user and device fields.
3. Validate vault encryption, outbox retries, conflict handling and background sync.
4. Prevent secrets and sensitive responses from entering service-worker caches.
5. Add schema/version migration handling for local databases.
6. Test offline create/update/delete and later reconciliation.

Exit criteria:

- Offline operations reconcile without loss, duplication or cross-tenant leakage.

### Phase 9 — UI, menu and product-surface wiring

1. Wire WorkCore modules into the MagicAI application shell.
2. Keep the persistent chatbot input as the primary assistant surface.
3. Use the menu for operational domains rather than duplicate assistant links.
4. Add settings through the header gear and secondary navigation where appropriate.
5. Verify role-specific dashboards and mobile/tablet/desktop layouts.
6. Remove dead links and unreachable screens.

Exit criteria:

- Every enabled feature has a reachable authorised UI path.
- No placeholder menu or disconnected dashboard remains.

### Phase 10 — Security, quality and release hardening

1. Add secret scanning, dependency auditing and static analysis.
2. Test file uploads, SSRF, path traversal, XSS, CSRF, SQL injection and mass assignment controls.
3. Review queue, scheduler, webhook and provider failure behaviour.
4. Add observability for actions, sync, AI runs and tenant violations.
5. Produce deployment, rollback and disaster-recovery documentation.
6. Generate a verified release archive and checksums.

Exit criteria:

- CI is green.
- Security review has no unresolved critical/high findings.
- Release can be installed from a clean environment and rolled back safely.

## Working method

Each pass should:

1. State its scope.
2. Identify confirmed defects separately from probable defects and architecture risks.
3. Modify the smallest coherent set of files.
4. Add or update tests for changed behaviour.
5. Run relevant validation.
6. Record remaining risks and the next pass.

## Current branch

Initial stabilisation work begins on:

`agent/repository-stabilisation-pass1`
