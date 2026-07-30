# Pass 2 — Architecture and Authority Consolidation

## Scope

This pass compared current repository behaviour with overlapping architecture and doctrine documents. It focused on ownership, tenancy, trust, AI authority and operational action execution.

## Current source inspected

- `AGENTS.md`
- `docs/architecture/TITAN_ZERO_AUTHORITY_MAP.md`
- `docs/plans/CURRENT_UPGRADE_PLAN.md`
- `config/app.php`
- `config/titan-zero.php`
- `composer.json`
- `app/Providers/TitanZeroServiceProvider.php`
- `app/Support/TitanZero/TitanZeroFeatureFlags.php`
- `app/Support/WorkCore/WorkCoreTenantResolver.php`
- `app/Domains/WorkCore/Config/workcore.php`
- `app/Domains/WorkCore/WorkCoreServiceProvider.php`
- `app/Domains/WorkCore/System/Actions/BusinessActionDispatcher.php`
- `app/Extensions/Chatbot/System/TitanAI/Architecture/AuthorityBoundaryRegistry.php`
- `packages/titanzero/interaction-engine/composer.json`
- `packages/titanzero/interaction-engine/src/Providers/InteractionServiceProvider.php`
- `tests/Feature/HostBootTest.php`

## Reference documents reviewed

- `collection-1/02-Platform-Architecture/02-reference-architecture.md`
- `collection-1/02-Platform-Architecture/24-security-identity-device-trust-and-tenant-boundary.md`
- `collection-1/02-Platform-Architecture/Titan_Permission_and_Tenant_Fence_Model.md`
- Pass 1 architecture and upgrade records already archived under `docs/archive/`

## Confirmed current behaviour

1. The MagicAI host is the active Laravel 10 application shell.
2. The host owns authentication and platform user/company/membership lifecycle.
3. WorkCore is enabled by default and registered by `TitanZeroServiceProvider`.
4. WorkCore supplies scoped tenant and operation contexts.
5. WorkCore resolves a company candidate from header, session, active-company attribute or a single active membership, then verifies active membership.
6. WorkCore's `BusinessActionDispatcher` checks context, entitlement, permission, confirmation and idempotency before transactional execution, audit and domain-event recording.
7. Chatbot activation is independently gated and disabled by default.
8. Interaction Engine source, provider and tests exist.

## Confirmed wiring gap

The Interaction Engine is not yet proven active in the host:

- the root `composer.json` does not register or require `packages/titanzero/interaction-engine`;
- `interaction_engine_enabled` exists in configuration and the feature-flag object;
- `TitanZeroFeatureFlags::coreProviderClassNames()` does not add the Interaction Engine provider.

Documents must therefore describe the Interaction Engine as **source-present and intended**, not as an active connected runtime.

## Conflicts resolved

### Host versus WorkCore tenancy

Older material sometimes describes Worksuite/WorkCore as owning tenancy, authentication and company records. Current source separates this more precisely:

- the host owns identity and membership lifecycle;
- WorkCore owns operational tenant resolution, scoping, permissions and mutation enforcement after consuming host context.

### Permission versus confidence

Some AI-oriented documents combine authority, confidence and approval. The canonical model now separates:

- entitlement;
- actor permission;
- device/channel trust;
- AI confidence;
- domain policy;
- explicit confirmation;
- idempotency and audit.

Confidence cannot grant authority.

### Conceptual Worksuite/Filament paths versus current repository

Reference material proposes `app/Platform/*` and a Worksuite/Filament split. These are useful design concepts but are not current repository authority. Current implementation remains MagicAI at the host root, WorkCore at `app/Domains/WorkCore`, Chatbot at `app/Extensions/Chatbot`, and the Interaction Engine package under `packages/titanzero/interaction-engine` pending connected registration.

## Canonical documents promoted or rewritten

- `AGENTS.md`
- `docs/architecture/TITAN_ZERO_AUTHORITY_MAP.md`
- `docs/architecture/TENANCY_TRUST_AND_ACTION_EXECUTION.md`
- `docs/plans/CURRENT_UPGRADE_PLAN.md`

## Disposition of source documents

| Document group | Disposition | Reason |
|---|---|---|
| Current source-backed architecture files above | `canonical` | Aligned with current paths and verified code |
| Extracted architecture Markdown | `reference-only` | Contains useful design material but also proposed or legacy structure |
| PDFs and DOCX doctrine/blueprints | `reference-only` | Require semantic extraction and comparison before promotion |
| Archived branch plans/status reports | `historical` | Preserve provenance; no longer current instructions |

## Deletion decision

No non-identical architecture document was deleted in this pass. Unique information has not yet been completely extracted from every PDF, DOCX and overlapping Markdown document. Deleting them now would be premature.

## Next architecture clusters

1. Interaction Engine, Wizard and five-tier intelligence.
2. PWA, nodes, offline storage and synchronisation.
3. Modules, extension platform and provider lifecycle.
4. Communications, channels and consent.
5. Workflows, automation, approvals and recovery.
6. Data model, statuses, migrations and record ownership.

Each cluster will produce a canonical Markdown specification and a source-disposition table before any non-identical source is removed.
