# Meetup + WorkCore Canonical Integration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [x]`) syntax for tracking.

**Goal:** Convert the supplied Meetup Laravel application into the Titan Zero host and integrate a repaired first-party WorkCore runtime plus SDK-governed Titan Maps Intelligence.

**Architecture:** Meetup owns identity, tenancy, messaging, shared infrastructure and extension discovery. WorkCore is installed under `app/Domains/WorkCore` and consumes host contracts. Optional extensions live under `app/Extensions` and can access WorkCore only through approved gateways.

**Tech Stack:** PHP 8.2+, Laravel 12, Eloquent, Sanctum, Pest/PHPUnit where dependencies are available, Vite/Tailwind, SQLite and PostgreSQL-compatible migrations.

## Global Constraints

- Never copy donor `IntegratedSources` into runtime autoload.
- Never represent WorkCore as an optional extension.
- Request-supplied company IDs are never authority.
- Secrets must use encrypted host Vault storage.
- Incomplete modules must fail closed or remain disabled.
- No operational extension may write WorkCore tables directly.
- Keep the original Meetup conversation and realtime ownership intact.
- Do not claim Laravel boot, migration or Pest success unless those commands execute successfully.

---

### Task 1: Baseline and executable integration guards

**Files:**
- Create: `tools/titan_verify.php`
- Create: `tests/Architecture/TitanIntegrationStructureTest.php`
- Modify: `.gitignore`

**Interfaces:**
- Consumes: current Meetup repository layout.
- Produces: a dependency-free verifier that later tasks extend.

- [x] **Step 1: Write a verifier that initially fails**

The verifier must check for the future paths:

```php
$required = [
    'app/Titan/Tenancy/ActiveCompanyContext.php',
    'app/Titan/Extensions/ExtensionRegistry.php',
    'app/Domains/WorkCore/WorkCoreServiceProvider.php',
    'app/Extensions/TitanMapsIntelligence/extension.json',
];
```

Run: `php tools/titan_verify.php`
Expected: non-zero exit with missing-path failures.

- [x] **Step 2: Add PHP lint and prohibited donor checks**

The script must recursively lint runtime PHP and reject runtime files beneath `IntegratedSources`, `__MACOSX`, or filenames ending in `.pdb`, `.dll`, or `.exe`.

- [x] **Step 3: Commit the red verification harness**

```bash
git add tools tests .gitignore
git commit -m "test: add Titan integration verification harness"
```

---

### Task 2: Host-owned companies, memberships and active context

**Files:**
- Create: `app/Titan/Tenancy/ActiveCompanyContext.php`
- Create: `app/Titan/Tenancy/CompanyContextResolver.php`
- Create: `app/Titan/Tenancy/EnsureActiveCompany.php`
- Create: `app/Titan/Permissions/PermissionResolver.php`
- Create: `app/Models/Company.php`
- Create: `app/Models/CompanyMembership.php`
- Create: `app/Models/CompanyRole.php`
- Create: `app/Models/CompanyPermission.php`
- Create: `database/migrations/2026_07_25_000100_create_titan_company_foundation.php`
- Modify: `app/Models/User.php`
- Modify: `app/Http/Controllers/AuthController.php`
- Modify: `bootstrap/app.php`

**Interfaces:**
- Produces: `ActiveCompanyContext::companyId(): int`, `CompanyContextResolver::resolve(Request): ActiveCompanyContext`, and `PermissionResolver::allows(int $userId, int $companyId, string $permission): bool`.

- [x] **Step 1: Add failing structure and security assertions**

Verify the context resolver does not read `company_id` from request input and requires an active membership.

- [x] **Step 2: Implement company foundation migration**

Create `titan_companies`, `titan_company_roles`, `titan_company_memberships`, `titan_role_permissions`, `titan_member_permissions`, and add nullable indexed `active_company_id` to users.

- [x] **Step 3: Implement models and relationships**

`User` gains `companies()`, `companyMemberships()`, and `activeCompany()`.

- [x] **Step 4: Implement resolver and middleware**

Resolution priority is authenticated user's validated `active_company_id`, then a validated session company ID. A header may request a switch only after membership verification; body input is ignored.

- [x] **Step 5: Create a default personal company on registration**

Registration must wrap user, company and owner membership creation in one transaction.

- [x] **Step 6: Run verifier and commit**

```bash
php tools/titan_verify.php --section=tenancy
git add app database bootstrap tests tools
git commit -m "feat: add host-owned company tenancy"
```

---

### Task 3: Host Vault, audit, capabilities and extension registry

**Files:**
- Create: `app/Titan/Vault/Vault.php`
- Create: `app/Titan/Vault/DatabaseVault.php`
- Create: `app/Titan/Audit/AuditRecorder.php`
- Create: `app/Titan/Audit/DatabaseAuditRecorder.php`
- Create: `app/Titan/Capabilities/CapabilityRegistry.php`
- Create: `app/Titan/Extensions/ExtensionDefinition.php`
- Create: `app/Titan/Extensions/ExtensionRegistry.php`
- Create: `app/Titan/Extensions/ExtensionManager.php`
- Create: `app/Providers/TitanServiceProvider.php`
- Create: `config/titan.php`
- Create: `database/migrations/2026_07_25_000110_create_titan_platform_tables.php`
- Modify: `bootstrap/providers.php`

**Interfaces:**
- Produces: scoped encrypted secret resolution, immutable audit recording, capability registration, manifest discovery and enabled-provider boot.

- [x] **Step 1: Add failing manifest and duplicate-capability tests**

Reject prohibited type `domain`, duplicate IDs, duplicate capability IDs, absent provider classes and invalid host requirements.

- [x] **Step 2: Implement Vault tables and encrypted resolver**

Store only encrypted values with company, optional user, provider and key identity. Never expose ciphertext or decrypted data through serialization.

- [x] **Step 3: Implement audit and capability registries**

Audit records include company, user, agent, conversation, action, entity, before/after, correlation and causation IDs.

- [x] **Step 4: Implement extension discovery**

Discover `app/Extensions/*/extension.json`, validate allowed types and load only extensions enabled in `titan_extensions`.

- [x] **Step 5: Register Titan provider and commit**

```bash
php tools/titan_verify.php --section=platform
git add app config database bootstrap tests tools
git commit -m "feat: add Titan host services and extension registry"
```

---

### Task 4: Repair and install canonical WorkCore runtime

**Files:**
- Create: `app/Domains/WorkCore/**`
- Create: `config/workcore.php`
- Create: `config/trade_compliance.php`
- Create: `resources/workcore/verticals/*.json`
- Create: `database/migrations/workcore/*.php`
- Create: `app/Titan/WorkCore/MeetupTenantResolver.php`
- Create: `app/Titan/WorkCore/MeetupPermissionResolver.php`
- Create: `app/Titan/WorkCore/MeetupStorageAdapter.php`
- Create: `app/Titan/WorkCore/MeetupNotificationAdapter.php`
- Create: `app/Titan/WorkCore/MeetupEntitlementResolver.php`
- Modify: `bootstrap/providers.php`

**Interfaces:**
- Consumes: host tenancy, permission, audit, storage and capability services.
- Produces: a bootable first-party WorkCore provider and stable action/read registries.

- [x] **Step 1: Add a namespace/import resolution test that fails against raw WorkCore**

The test must require all runtime WorkCore classes to use `App\\Domains\\WorkCore` and report unresolved imports.

- [x] **Step 2: Copy only canonical runtime sources**

Copy `System`, selected configs, root migrations and uppercase vertical manifests. Exclude `IntegratedSources`, embedded `Integrated/TitanRewind`, root legacy provider and extension manifest.

- [x] **Step 3: Rebase namespace and paths**

Replace `App\\Extensions\\WorkCore` with `App\\Domains\\WorkCore`; replace MagicAI defaults with Meetup adapters; normalise vertical path to `resources/workcore/verticals`.

- [x] **Step 4: Establish one provider authority**

Install `App\\Domains\\WorkCore\\WorkCoreServiceProvider` that registers only providers whose classes and required contracts exist. Missing modules appear in diagnostics and remain disabled.

- [x] **Step 5: Repair model base and host dependencies**

Replace `App\\Models\\BaseModel` assumptions with a WorkCore `DomainModel` base. Remove direct dependencies on MagicAI menu models and platform-specific classes.

- [x] **Step 6: Vet migrations**

Use the core property/field-service migration sequence, shorten identifiers over 64 characters, remove duplicate timestamps, avoid duplicate host company tables, and preserve SQLite/PostgreSQL compatibility.

- [x] **Step 7: Run static WorkCore guards and commit**

```bash
php tools/titan_verify.php --section=workcore
git add app/Domains app/Titan/WorkCore config resources/workcore database/migrations/workcore bootstrap tests tools
git commit -m "feat: integrate repaired WorkCore domain"
```

---

### Task 5: WorkCore host API and operational summary

**Files:**
- Create: `app/Http/Controllers/Titan/CompanyContextController.php`
- Create: `app/Http/Controllers/Titan/WorkCoreSummaryController.php`
- Create: `app/Http/Controllers/Titan/WorkCoreActionController.php`
- Create: `app/Http/Controllers/Titan/CapabilityController.php`
- Create: `routes/titan.php`
- Modify: `bootstrap/app.php`

**Interfaces:**
- Produces: authenticated, company-scoped endpoints for context, capabilities, operational summaries and governed actions.

- [x] **Step 1: Add failing route security assertions**

Every Titan route must use authentication and active-company middleware. Mutation requests must not accept authoritative company IDs.

- [x] **Step 2: Implement context switching**

Switching validates membership, updates session and user active company, records audit and emits a context event.

- [x] **Step 3: Implement summary reads**

Return counts and status summaries for customers, premises, work orders, appointments, workers, assets and extensions without exposing another company.

- [x] **Step 4: Implement governed action endpoint**

Dispatch only registered WorkCore actions and return normalised errors with correlation IDs.

- [x] **Step 5: Commit**

```bash
php tools/titan_verify.php --section=routes
git add app/Http routes bootstrap tests tools
git commit -m "feat: expose governed Titan and WorkCore APIs"
```

---

### Task 6: Install and adapt Titan Maps Intelligence

**Files:**
- Create: `app/Extensions/TitanMapsIntelligence/**`
- Create: `app/Titan/Maps/MapsCredentialResolver.php`
- Create: `app/Titan/Maps/MapsCompanyContext.php`
- Create: `app/Titan/Maps/MapsPermissionGateway.php`
- Create: `app/Titan/Maps/MapsAuditGateway.php`
- Create: `app/Titan/Maps/MapsWorkCoreGateway.php`
- Create: `database/migrations/extensions/titan_maps/*.php`
- Modify: `config/titan.php`

**Interfaces:**
- Consumes: host Vault, active company, permissions, audit, private storage and WorkCore action gateway.
- Produces: an optional SDK-compatible maps integration with fail-closed promotion.

- [x] **Step 1: Add failing adapter and manifest tests**

Verify the extension cannot use request body company IDs, plaintext secrets or direct WorkCore table writes.

- [x] **Step 2: Copy extension without donor artefacts**

Install source, config, resources and routes under the approved extension root.

- [x] **Step 3: Bind host adapters**

Resolve Google/provider credentials from Vault and promote candidates only through WorkCore actions/services.

- [x] **Step 4: Register extension disabled by default**

Seed the extension registry with compatibility status and require explicit enablement plus provider configuration.

- [x] **Step 5: Run SDK and local guards and commit**

```bash
php tools/titan_verify.php --section=maps
php tools/titan_verify.php --section=extensions
git add app/Extensions app/Titan/Maps database/migrations/extensions config tests tools
git commit -m "feat: integrate Titan Maps Intelligence"
```

---

### Task 7: Titan Zero conversation context and safe AI configuration

**Files:**
- Create: `app/Titan/AI/TitanZeroOrchestrator.php`
- Create: `app/Titan/AI/ConversationContextBuilder.php`
- Create: `app/Titan/AI/ToolRouter.php`
- Modify: `app/Services/AIAssistantService.php`
- Modify: `app/Http/Controllers/ChatController.php`
- Modify: `config/services.php`

**Interfaces:**
- Produces: conversation context containing company, active operational entities, permissions and available capabilities; tool execution routes through registries.

- [x] **Step 1: Add failing tests for secret leakage and direct SQL prohibition**

AI responses must not include provider bodies or secret values. Tool execution may call only registered capability handlers.

- [x] **Step 2: Move AI credentials to Vault resolution**

Remove database setting plaintext API key priority and return safe configuration errors.

- [x] **Step 3: Add WorkCore context to Titan Zero**

Build a compact context package from authorised read models and conversation state.

- [x] **Step 4: Route recognised tools through capability registry**

No AI path may directly instantiate repositories or update Eloquent operational models.

- [x] **Step 5: Commit**

```bash
php tools/titan_verify.php --section=ai
git add app/Titan/AI app/Services app/Http config tests tools
git commit -m "feat: connect Titan Zero conversation orchestration"
```

---

### Task 8: UI integration and removal of unsafe maintenance routes

**Files:**
- Create: `resources/views/titan/operations.blade.php`
- Create: `resources/js/titan/operations.js`
- Modify: `resources/views/layouts/partials/mini-sidebar.blade.php`
- Modify: `resources/views/chat.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/css/app.css`

**Interfaces:**
- Produces: company switcher, operations overview, capability status and maps candidate/progress surfaces.

- [x] **Step 1: Add failing route scan for unauthenticated maintenance actions**

Reject public cache clear, storage link or administrative Artisan routes.

- [x] **Step 2: Remove unsafe routes**

Delete `/system/catch-clear` and `/system/storage-link` HTTP actions.

- [x] **Step 3: Add company and operations surfaces**

Use server-resolved company context and authorised API responses. Do not embed secrets or company authority in hidden fields.

- [x] **Step 4: Add Maps UI component mounting points**

Render only when extension registry reports enabled and authorised.

- [x] **Step 5: Build frontend if dependencies are available and commit**

```bash
npm install
npm run build
git add resources routes tests tools
git commit -m "feat: add Titan operations interface"
```

---

### Task 9: Verification, documentation and packaging

**Files:**
- Create: `docs/integration/AUTHORITY_AND_PROVENANCE.md`
- Create: `docs/integration/WORKCORE_REPAIR_REPORT.md`
- Create: `docs/integration/MEETUP_HOST_ADAPTERS.md`
- Create: `docs/integration/EXTENSION_REGISTRY.md`
- Create: `docs/integration/REMAINING_WORK.md`
- Create: `BUILD_REPORT.md`
- Modify: `README.md`

**Interfaces:**
- Produces: reproducible evidence and final distributable ZIP.

- [x] **Step 1: Run complete verifier**

```bash
php tools/titan_verify.php
```

Expected: all dependency-free checks pass.

- [x] **Step 2: Run PHP lint**

```bash
find app bootstrap config database routes tests tools -name '*.php' -print0 | xargs -0 -n1 php -l
```

Expected: no syntax errors.

- [x] **Step 3: Attempt Laravel verification**

If Composer is available, run `composer install`, `php artisan about`, `php artisan route:list`, SQLite migrations and `php artisan test`. Record exact outcomes.

- [x] **Step 4: Attempt frontend build**

Run `npm install && npm run build` and record exact outcomes.

- [x] **Step 5: Generate source inventory and remaining-gap report**

Document included, modified, excluded and quarantined sources with reasons.

- [x] **Step 6: Create reproducible ZIP and checksum**

```bash
zip -qr Titan-Zero-Meetup-WorkCore-Integrated-v0.1.0.zip . -x '.git/*' '.env' 'vendor/*' 'node_modules/*'
sha256sum Titan-Zero-Meetup-WorkCore-Integrated-v0.1.0.zip > Titan-Zero-Meetup-WorkCore-Integrated-v0.1.0.zip.sha256
```

- [x] **Step 7: Final commit**

```bash
git add .
git commit -m "release: package Meetup WorkCore integration v0.1.0"
```
