> [!IMPORTANT]
> **Historical record — not current implementation guidance.** This document is retained for provenance because it describes an earlier branch, source version, import, or completed upgrade pass. Use `docs/README.md` and `docs/plans/CURRENT_UPGRADE_PLAN.md` for current guidance.

# Titan Zero Chatbot PWA — Multi-Pass Upgrade Plan

**Branch:** `agent/titan-zero-pwa-upgrade`  
**Repository:** `Masterleeaus/clean`  
**Status:** Prepared implementation branch  
**Primary goal:** Deliver one comprehensive Titan Zero PWA containing the existing fourteen apps, five-tier AI system, Interaction Engine, WorkCore integration, generative UI, offline runtime, and secure MagicAI desktop handoff.

## 1. Product decision

The existing Titan Zero chatbot runtime is the PWA. It is not replaced by AppForge, MobileKit, the website-to-app builder, a second app shell, or a separate native builder.

The MVP has two coordinated surfaces:

- **Titan Zero PWA:** mobile/tablet operations, fourteen role-aware apps, persistent chat, five-tier AI, Interaction Engine workflows, local-first operation, generative UI, push, device management, and sync.
- **MagicAI desktop:** full-screen administration, advanced WorkCore screens, large tables, billing, subscriptions, provider settings, AI configuration, builder controls, reports, and bulk operations.

## 2. Canonical authority boundaries

| System | Owns | Must not own |
|---|---|---|
| MagicAI | Laravel host, authentication, users, subscriptions, entitlements, desktop UI, extension lifecycle | WorkCore operational truth or PWA local state |
| WorkCore | companies, memberships, permissions, customers, properties, jobs, schedules, quotes, invoices, payments, workforce, forms, evidence, audit, events | chatbot reasoning or visual composition |
| Interaction Engine | interaction definitions, sessions, validation, branching, authority decisions, confidence, evidence, abstention, resumable/offline interaction state | direct WorkCore model writes |
| Titan Zero five-tier AI | orchestration, managers, assistants, specialists, agents, governed tools, explanations | permission bypass or operational database authority |
| Chatbot PWA | fourteen apps, mobile shell, local projections, drafts, offline journal, sync, device vault, generative UI rendering | duplicate server-side WorkCore domains |

All operational mutations must pass through the canonical WorkCore action boundary, normally `BusinessActionDispatcher` or a newer governed equivalent.

## 3. Source authority

1. `MagicAI-v10.91-WorkCore-InteractionEngine-FULL-MERGED.zip` — backend authority and local Interaction Engine package.
2. `Titan-Zero-Chatbot-PWA-PASS12-HOST-BOUNDARY-FIXED(1).zip` — canonical PWA extension source.
3. `Titan-Mobile-Donor-Code-VERIFIED.zip` — AppForge and MobileKit donor reference only.
4. `online-app-builder-from-website.zip` — selected Vue/Flutter shell reference only.
5. `TitanZero-Extension-SDK-v2.0.0(1).zip` — extension-development tooling and architecture contracts.

Donor authentication, billing, Supabase, storage authority, service workers, manifests, backend models, webhooks, and direct database writes are prohibited unless explicitly reimplemented behind Titan contracts.

---

# Pass 0 — Branch preparation and source reconciliation

## Outcome

Create a clean, reviewable branch containing the full MagicAI–WorkCore base, local Interaction Engine package, canonical PWA extension, quarantined donor sources, architecture documents, verification scripts, and CI.

## Work

- Verify source archive hashes, file counts, and extraction integrity.
- Overlay only backend files that are new or changed from the existing MagicAI–WorkCore repository base.
- Install the PWA under `app/Extensions/Chatbot` without creating a second shell.
- Keep donor code under `tools/donor-sources`; no donor code is runtime-active.
- Add root plan, branch preparation notes, provenance, authority map, agent instructions, and CI.
- Confirm no `.env`, runtime cache, credential, private key, `vendor`, or `node_modules` content enters Git.

## Acceptance gate

- `artisan`, `composer.json`, WorkCore provider, Interaction Engine package, and PWA `extension.json` are present.
- The branch is ahead of `main` and isolated.
- PHP syntax, JSON parsing, shell syntax, and archive provenance checks pass.

---

# Pass 1 — Host boot and dependency stabilisation

## Objective

Make the merged Laravel host installable and bootable from a clean clone.

## Primary areas

- `composer.json`, `composer.lock`
- `config/app.php`
- `bootstrap/providers.php` where present
- `app/Domains/WorkCore/WorkCoreServiceProvider.php`
- `packages/titanzero/interaction-engine/src/Providers/InteractionServiceProvider.php`
- `app/Extensions/Chatbot/System/ChatbotServiceProvider.php`

## Work

- Normalise provider registration so WorkCore, Interaction Engine, and chatbot load once.
- Validate Composer path repositories and package discovery.
- Remove duplicate or future-dated migration collisions without discarding schema.
- Add a minimal host boot test and route/provider contract tests.
- Document required PHP extensions, queue, cache, database, and Node versions.

## Gate

`composer validate`, dependency installation, `php artisan about`, route listing, and a clean test environment boot succeed.

---

# Pass 2 — Canonical PWA extension installation

## Objective

Install the 1,542-file PWA as the single mobile runtime inside MagicAI.

## Work

- Register the extension through the existing MagicAI extension lifecycle.
- Publish/resolve PWA assets through Vite without duplicating host packages.
- Confirm all PWA routes are authenticated and company-scoped where required.
- Ensure the existing builder continues to configure and preview the same PWA.
- Remove temporary source/integration folder assumptions from runtime paths.

## Gate

The PWA opens from MagicAI, the builder preview renders it, and no parallel PWA shell exists.

---

# Pass 3 — Mobile shell and accessible design system

## Objective

Upgrade the current visual shell using selected MobileKit patterns while preserving Titan Zero’s friendly rounded design.

## Work

- Add namespaced design tokens, larger text, contrast, touch targets, reduced motion, dark/system mode, and RTL foundations.
- Upgrade header, persistent chat bar, cards, action sheets, dialogs, toasts, forms, and bottom navigation.
- Keep settings behind the gear icon and secondary links behind the hamburger/app launcher.
- Prevent donor Bootstrap styles and scripts from leaking into MagicAI or PWA runtime.

## Gate

Phone, tablet, and desktop-PWA layouts pass responsive and accessibility tests.

---

# Pass 4 — Fourteen-app registry and role-aware navigation

## Objective

Expose the existing fourteen apps through one canonical registry and role-aware navigation.

## Work

- Reconcile stale 10-app, 13-app, and 14-app lists.
- Resolve availability from company, role, permissions, entitlements, and device capability.
- Support worker, customer, resident, provider, manager, property, and administrator navigation presets.
- Preserve active app, record, and interaction context across navigation.
- Add authorised deep links from chat, push, and MagicAI desktop.

## Gate

All fourteen manifests resolve and unauthorised apps fail closed.

---

# Pass 5 — Interaction Engine runtime bridge

## Objective

Make Interaction Engine the execution spine beneath the PWA and all fourteen apps.

## Execution modes

1. **User only:** deterministic forms, checklists, inspections, training, evidence, and guided procedures.
2. **User plus AI:** prefill, explanation, recommendation, clarification, summarisation, and approval.
3. **Delegated AI:** bounded autonomous work subject to permission, delegation, risk, reversibility, confidence, and abstention.

## Work

- Add PWA interaction renderer and session adapter.
- Cache definitions and sessions locally for deterministic offline continuation.
- Surface authority, confidence, evidence, explanation, abstention, and approval states in generative UI.
- Ensure authority always overrides confidence.
- Route final operational commands through WorkCore.

## Gate

Representative workflows run online, offline, user-only, collaborative, and delegated modes with audit evidence.

---

# Pass 6 — Five-tier AI convergence

## Objective

Unify the public chatbot, fourteen apps, Interaction Engine, and five-tier AI behind one governed execution pipeline.

## Work

- Keep Titan Zero as Tier 0 orchestrator.
- Verify Tier 1 managers, Tier 2 assistants/specialists, Tier 3 action agents, and Tier 4 tools are discoverable and routable.
- Remove bypass paths and duplicate chatbot execution services.
- Pass active app, company, record, interaction, device, and offline context into routing.
- Produce action receipts and user-readable explanations.

## Gate

The same intent produces the same governed plan whether started from chat, an app screen, voice, or an Interaction Engine workflow.

---

# Pass 7 — WorkCore read and action wiring

## Objective

Wire every visible PWA action to authoritative WorkCore reads and governed mutations.

## Work

- Complete bootstrap, read-model, search, and record-detail APIs.
- Map app actions to `BusinessActionDispatcher` handlers.
- Implement company context, permission, entitlement, confirmation, idempotency, optimistic version, and audit validation.
- Remove direct Eloquent model-write fallbacks from chatbot and agent code.
- Complete finance mappings through Titan Money/ZeroPay boundaries where applicable.

## Gate

No visible action is a placeholder, silent no-op, or direct operational model write.

---

# Pass 8 — Offline WorkCore and conflict-centre consolidation

## Objective

Consolidate local persistence behind one device database and one sync protocol.

## Work

- Reconcile chatbot stores, WorkCore cache, drafts, attachments, outboxes, conflict records, and Interaction Engine sessions.
- Implement bootstrap, incremental pull, batched push, acknowledgements, tombstones, attachment/resumable upload, job packs, and knowledge packs.
- Use client UUIDs, device sequences, idempotency keys, optimistic versions, and explicit conflict records.
- Never delete unsynchronised data automatically.
- Revalidate every queued action on the server.

## Gate

Offline create/update/evidence/interaction scenarios survive reload, reconnect, retry, conflict, and device revocation.

---

# Pass 9 — Generative UI and stable operational screens

## Objective

Use generative UI for context and exceptions while keeping critical worker flows predictable.

## Work

- Validate all generated specs against the trusted component catalogue.
- Bind components to authorised WorkCore read/action contracts.
- Add approval, confidence, evidence, conflict, and recovery components.
- Keep start-job, safety, evidence, checklist, and completion controls stable.
- Add screen-level offline and stale-data indicators.

## Gate

Untrusted component types, properties, URLs, and actions fail closed.

---

# Pass 10 — Builder and live-preview expansion

## Objective

Extend the existing MagicAI five-stage chatbot builder into the Titan Zero PWA Experience Builder.

## Builder stages

1. **Configure:** company, role preset, default app, enabled apps, offline policy.
2. **Customise:** brand, theme, text scale, navigation, home layout, icon, splash, accessibility.
3. **Train:** knowledge, five-tier AI teams, tools, Interaction Engine workflows, authority and confidence settings.
4. **Deploy and access:** PWA URL, QR install, website embed, worker/customer links, desktop handoff.
5. **Channels:** PWA push, in-app, email, SMS, WhatsApp, Telegram, Messenger, Instagram, staff inbox.

## Work

- Reuse the existing live device preview.
- Add role/device/app/network-state controls.
- Render the real PWA configuration, not a disconnected mock.
- Adapt selected AppForge preview, colour, navigation, asset, QR, install, and shortcut patterns.

## Gate

Changes in the builder update the same stored configuration consumed by the live PWA.

---

# Pass 11 — Install, push, device, and channel experience

## Objective

Complete installability and device operations without replacing the canonical service worker.

## Work

- Add iOS/Android/desktop install education and QR handoff.
- Add safe update discovery, activation, rollback messaging, and version status.
- Complete push token registration, preferences, deep links, delivery history, retry, and revocation.
- Keep credentials, authenticated responses, and private evidence out of service-worker caches.
- Activate only verified channel providers and signed tenant-scoped webhooks.

## Gate

Install, update, push, logout cleanup, device revoke, and offline launch scenarios pass.

---

# Pass 12 — MagicAI desktop handoff and shared context

## Objective

Let PWA users move into the full-screen MagicAI workspace without losing context or weakening security.

## Work

- Add authorised desktop deep links for current company, app, record, and interaction.
- Use the existing host session/OAuth flow; never place session tokens in URLs.
- Return to the PWA with a safe context pointer where useful.
- Mark complex, bulk, configuration, and high-risk operations as desktop-only where appropriate.

## Gate

Context handoff works across phone, tablet, and desktop without cross-company leakage.

---

# Pass 13 — Security, performance, and tenancy hardening

## Objective

Remove production blockers before release-candidate testing.

## Work

- Scan for secrets, unsafe `eval`, arbitrary class/action resolution, path traversal, weak uploads, unsigned webhooks, direct model writes, unscoped queries, and logs containing private prompts or tokens.
- Add tenant-isolation, permission, authority, confidence, replay, idempotency, and offline tamper tests.
- Lazy-load app modules and reduce initial PWA payload.
- Audit service worker caches and local data retention.
- Add rate limits and abuse controls for online and replayed offline commands.

## Gate

No known critical security finding remains and performance budgets are met on representative mobile hardware.

---

# Pass 14 — MVP release candidate

## Objective

Produce the first deployable MVP release of the comprehensive Titan Zero PWA and MagicAI desktop backend.

## Work

- Run clean installation, migration, seed, route, queue, browser, PWA, offline, WorkCore, Interaction Engine, five-tier AI, tenancy, and security tests.
- Validate all fourteen apps for worker, customer, manager, property, resident, and provider roles.
- Produce deployment guide, upgrade guide, rollback guide, architecture tree, source manifest, database artefacts, checksums, and release notes.
- Package a cumulative application archive and a reviewed delta.

## Gate

A clean environment can install, boot, authenticate, open the PWA, complete representative online/offline workflows, and hand off to MagicAI desktop.

---

## Pass deliverables

Every implementation pass must provide:

- code changes on an isolated branch;
- tests written before production changes;
- pass report and change manifest;
- updated architecture/source documentation;
- database migration and SQL changes when applicable;
- exact tests passed, failed, and not run;
- cumulative and delta archives only when requested;
- CRC, fresh-extraction, real-file, zero-byte, and SHA-256 verification for any archive.

## Definition of MVP success

The MVP is successful when a company can configure the Titan Zero experience in MagicAI, install one PWA, see the apps permitted for its role, work online or offline, collaborate with Titan Zero and its five-tier AI, complete Interaction Engine workflows, execute governed WorkCore actions, resolve conflicts, receive notifications, and move to the full MagicAI desktop workspace for advanced operations.
