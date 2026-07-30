# Extension Platform, Manifest and Lifecycle Architecture

**Status:** Canonical extension-platform boundary for the current reconciliation programme.

**Source baseline:** `integration/current-main-reconciliation` at `fa607d769a4f72ba287801b027cc42dcf56aa549`.

This document defines what makes an extension discovered, installed, qualified, enabled and active. Files being present under `app/Extensions` are not enough.

## 1. Current physical inventory

The source-backed Pass 5 inventory found:

- **95** extension directories under `app/Extensions`;
- **112** static marketplace provider mappings;
- **2** filesystem extension directories not mapped by Marketplace;
- **19** marketplace mappings whose provider source is absent;
- **810** PHP symbols declared in more than one extension directory;
- **93** migration filenames shared across more than one extension directory;
- **88 of 95** extension directories with no detected test file;
- a recognised `extension.json` in every extension directory;
- **0** malformed `extension.json` files after BOM-aware decoding.

Detailed evidence is stored in:

- `docs/inventory/EXTENSION_PLATFORM_INVENTORY.md`
- `docs/inventory/EXTENSION_PLATFORM_INVENTORY.json`
- `docs/inventory/EXTENSION_PLATFORM_GAPS.md`

## 2. Current registry drift

### Filesystem extensions not mapped by Marketplace

- `Introduction`
- `TitanZeroChatbot`

`TitanZeroChatbot` is already classified as a frozen compatibility copy of the canonical `Chatbot` extension and must not be added to the marketplace map.

`Introduction` requires a separate product decision: either qualify and map it deliberately, classify it as built-in/dormant, or archive/remove it after dependency tracing. Its presence on disk does not make it active.

### Stale marketplace mappings

The static marketplace map still references 19 provider classes whose expected source files are absent:

- `photo-studio`
- `onboarding`
- `affilate`
- `ideogram`
- `speechify-tts`
- `chatbot-telegram`
- `chatbot-whatsapp`
- `chatbot-messenger`
- `chatbot-instagram`
- `footer-menu`
- `demo-extension`
- `ai-chat-pro-image-chat`
- `ai-chat-pro-memory`
- `ai-agent-outlook`
- `ai-chat-pro-gmail`
- `ai-chat-pro-outlook`
- `ai-chat-pro-notion`
- `ai-chat-pro-google-drive`
- `ai-chat-pro-google-calendar`

These mappings are registry drift. They must not be treated as installed capabilities. A focused implementation pass must either restore verified source or remove the stale map entries and related database/menu/package references.

## 3. Current activation defect

`MarketplaceServiceProvider` guards discovery behind `extension_discovery_enabled`, but when that flag is enabled it loops through the complete static provider map and registers every loadable provider.

This collapses several distinct states into one switch:

```text
source present
→ provider class loadable
→ automatically registered
```

That is unsafe. The required model is:

```text
source present
→ manifest valid
→ registry identity unique
→ installed record present
→ version compatible
→ dependencies satisfied
→ tenant/package entitled
→ administrator enabled
→ health checks pass
→ provider registered exactly once
```

The global discovery flag may enable the extension platform, but it must not activate every extension.

## 4. Canonical extension states

Every extension must have one explicit lifecycle state:

| State | Meaning | Provider may load? |
|---|---|---:|
| `source_present` | Files exist, but no qualification decision exists | No |
| `discovered` | Manifest parsed and identity recorded | No |
| `installed` | Verified package committed/staged and install record written | Not automatically |
| `qualified` | Compatibility, dependencies, permissions, migrations and health checks pass | No |
| `enabled` | Administrator/tenant has enabled the qualified extension | Yes, subject to entitlement |
| `disabled` | Installed but intentionally inactive | No |
| `dormant` | Retained for possible future use without current qualification | No |
| `compatibility_only` | Kept only for migration or caller compatibility | No direct activation |
| `quarantined` | Failed security, integrity, health or conflict checks | No |
| `superseded` | Replaced by canonical host/domain functionality | No |
| `uninstalling` | Reversible removal is in progress | Restricted lifecycle only |
| `removed` | Runtime files and active registrations are absent; provenance retained | No |

Files on disk never imply `enabled`.

## 5. Extension ownership boundary

Extensions may own optional capabilities, presentation surfaces and provider adapters.

Extensions must not replace or become an alternative authority for:

- host authentication;
- users, companies or memberships;
- tenant resolution;
- global permission authority;
- WorkCore operational records or mutation policy;
- messaging/conversation authority owned by the host/Chatbot architecture;
- Titan Vault credentials;
- MagicAI subscription billing;
- canonical operational finance ledgers.

Operational mutations exposed by an extension must terminate at registered WorkCore actions.

## 6. Manifest model

### Current legacy manifest

Most current `extension.json` files contain only:

- `name`;
- `type`;
- `version`;
- `description`;
- support metadata.

Many are UTF-8 BOM encoded. BOM-aware parsing is required for compatibility, but new or rewritten manifests should use normal UTF-8 without BOM.

The legacy schema is insufficient for safe activation.

### Required canonical manifest

A qualified extension manifest must define or resolve:

```json
{
  "schema_version": 2,
  "id": "stable.extension.id",
  "slug": "extension-slug",
  "name": "Human Name",
  "type": "extension",
  "version": "1.2.3",
  "provider": "App\\Extensions\\Example\\System\\ExampleServiceProvider",
  "description": "Purpose",
  "compatibility": {
    "app": ">=10.91 <11.0",
    "php": "^8.2",
    "laravel": "^10.0"
  },
  "dependencies": {
    "required": [],
    "optional": [],
    "conflicts": []
  },
  "capabilities": [],
  "permissions": [],
  "routes": [],
  "migrations": [],
  "assets": [],
  "menus": [],
  "events": [],
  "scheduled_jobs": [],
  "queues": [],
  "secrets": [],
  "tenant_scope": "company",
  "health_checks": [],
  "install": {
    "reversible": true,
    "requires_confirmation": true
  },
  "uninstall": {
    "data_policy": "retain",
    "requires_export": false
  },
  "integrity": {
    "archive_sha256": null,
    "signature": null,
    "signing_key_id": null
  }
}
```

The manifest is declarative evidence, not permission to execute arbitrary installation instructions.

## 7. Unique identity and collision rules

The following values must be unique across active extensions:

- extension ID;
- slug;
- registration key;
- provider class;
- route name and method/URI pair;
- migration ownership/table creation;
- permission key;
- capability key;
- menu contribution ID;
- scheduled task ID;
- event listener identity where duplicate execution would be unsafe;
- published asset destination.

Duplicate detection must run before provider registration or migration execution.

The current 810 duplicated PHP symbols are dominated by the copied `Chatbot`/`TitanZeroChatbot` trees, but other cross-extension symbol collisions also exist and require individual classification.

The current 93 duplicated migration filenames are dominated by the same Chatbot copy, but filenames alone are not enough: table names, class names, checksums and execution history must also be compared.

## 8. Qualification gates

Before an extension can become `qualified`, verify:

1. manifest schema and encoding;
2. unique identity and provider class;
3. source integrity and provenance;
4. application, PHP and framework compatibility;
5. required dependencies and conflicts;
6. provider class loadability;
7. route and middleware safety;
8. migration ownership, reversibility and database support;
9. tenant scoping;
10. permissions and capability registration;
11. secret declarations and Vault integration;
12. menu and UI reachability;
13. queue, scheduler, event and webhook behaviour;
14. install, upgrade, disable, uninstall and rollback tests;
15. extension-specific tests and health checks;
16. absence of direct operational model writes outside WorkCore;
17. package/tenant entitlement;
18. no prohibited authority replacement.

An extension with no tests may remain source-present or dormant, but it must not be labelled production-ready solely because its provider boots.

## 9. Current installation lifecycle risks

### State-changing GET routes

Marketplace install and uninstall are exposed through authenticated `GET` routes.

Installation and removal are state-changing operations and must use authorised `POST`/`DELETE`-style requests with CSRF protection, explicit permission checks, confirmation and audit.

### Unverified remote archive

The current installer:

- downloads remote ZIP bytes;
- writes them into extension storage;
- calls `ZipArchive::extractTo()` directly;
- does not show per-entry traversal/symlink/destination validation;
- does not show archive signature or public-key verification;
- creates extension directories with mode `0777`;
- clears caches;
- runs forced migrations;
- force-publishes assets;
- records installed state after those operations.

The legacy installer path also directly extracts ZIP content, can execute SQL from package files, copies controllers/routes/stubs into application paths and creates directories using `0777`.

These paths are not suitable as the final Titan Zero extension lifecycle without hardening.

## 10. Required secure installation flow

```text
Authorised administrator request
        ↓
CSRF + permission + recent confirmation
        ↓
Fetch signed manifest and archive metadata
        ↓
Verify origin, SHA-256, signature and supported signing key
        ↓
Download to isolated staging directory
        ↓
Reject encrypted ZIPs, links, traversal, absolute paths and unsafe file types
        ↓
Validate extracted tree against manifest allowlist
        ↓
Run static checks, dependency resolution and conflict detection
        ↓
Snapshot current registry, files, schema state and published assets
        ↓
Dry-run migrations and provider health checks
        ↓
Transactional/staged install where possible
        ↓
Register extension as installed but disabled
        ↓
Run migrations, asset publication and health checks under audit
        ↓
Enable only after all gates pass
        ↓
Retain rollback package and lifecycle record
```

Never execute or copy files outside declared, approved destinations.

## 11. Upgrade policy

Upgrades require:

- old and new manifest comparison;
- semantic version and host compatibility checks;
- explicit migration plan;
- changed permission/capability disclosure;
- route/provider/menu conflict analysis;
- file deletion allowlist;
- database backup or reversible migration path;
- staged health checks;
- rollback package and instructions;
- post-upgrade audit.

An extension must not delete arbitrary base paths from package-provided instructions without validation against an owned-file registry.

## 12. Disable and uninstall policy

Disable and uninstall are different:

### Disable

- stop provider, routes, jobs, schedules and UI contributions;
- retain data and files;
- preserve lifecycle/audit records;
- permit safe re-enable.

### Uninstall

- require explicit administrator permission and confirmation;
- stop active workers/jobs first;
- export or retain tenant data according to manifest policy;
- run only validated uninstall migrations/hooks;
- remove only extension-owned files and registrations;
- retain provenance, audit and rollback evidence;
- never silently swallow lifecycle exceptions;
- never report success while database or file residue remains unknown.

The current uninstall path deletes directories and clears caches, but no database rollback was detected and exceptions may be swallowed. It requires redesign before being treated as complete removal.

## 13. Registry architecture

The static `MarketplaceServiceProvider::$extensionProviders` map may remain as a compatibility catalogue during transition, but it must not be the final activation authority.

The canonical registry should combine:

- validated filesystem manifests;
- installed extension database records;
- qualification status;
- enabled state;
- version and dependency graph;
- tenant/package entitlement;
- provider health;
- quarantine reason;
- lifecycle audit history.

Provider registration should be computed from that registry and cached only after validation.

## 14. Current extension classifications

### Canonical active candidates

Extensions with source, a mapped provider and deliberate product use may proceed to qualification. Presence in this category is not a production-readiness claim.

### Unmapped source

- `Introduction`: requires deliberate qualify/dormant/remove decision.
- `TitanZeroChatbot`: compatibility-only; must remain disabled and is a future focused-removal candidate.

### Stale registry entries

The 19 missing providers listed above must be removed from the active map unless verified source is intentionally restored.

### Test-poor source

Eighty-eight extension directories have no detected extension-local test file. They remain unqualified until adequate host/contract/health evidence exists.

## 15. Verification gates

Before enabling extension discovery in production:

1. stale provider mappings are resolved;
2. compatibility-only directories are excluded;
3. discovery registers only installed, enabled, entitled and qualified extensions;
4. manifest v2 or an equivalent normalized registry is available;
5. duplicate symbols, migrations, routes, permissions and capabilities fail closed;
6. install/uninstall routes are state-changing, authorised and audited;
7. archives are signed and safely extracted;
8. install/upgrade/uninstall have rollback behaviour;
9. extension files use least-privilege permissions;
10. migrations are dry-run and ownership checked;
11. providers boot independently and together without collisions;
12. tenant isolation and WorkCore mutation boundaries pass;
13. each enabled extension has tests or approved health/contract evidence;
14. extension discovery disabled mode still boots cleanly;
15. quarantine prevents loading failed extensions.

## 16. Current disposition summary

| Item | Disposition |
|---|---|
| `MarketplaceServiceProvider::$extensionProviders` | Compatibility catalogue with confirmed drift; not final activation authority |
| `extension_discovery_enabled` | Platform gate only; must not mean activate all providers |
| Legacy `extension.json` | BOM-compatible discovery metadata; insufficient qualification schema |
| `Introduction` | Unmapped source; decision required |
| `TitanZeroChatbot` | Unmapped compatibility copy; never activate |
| 19 missing provider mappings | Stale until source is deliberately restored |
| Remote ZIP installer | Requires signature, safe extraction, staging and rollback hardening |
| GET install/uninstall routes | Superseded lifecycle design; replace with authorised state-changing requests |
| `0777` extension directories | Replace with least-privilege permissions |
| Forced migrations/asset publication | Gate behind staged qualification and rollback |
| 88 extensions without detected tests | Not production-qualified by source presence alone |
