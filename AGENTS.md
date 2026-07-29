# Titan Zero Agent Working Agreement

This repository contains licensed MagicAI source and must remain private.

## Active branch

All work covered by `TITAN_ZERO_UPGRADE_PLAN.md` must be performed on:

```text
agent/gpt56-titan-zero-upgrade-workbench
```

Do not commit upgrade work directly to `main`.

## Canonical ownership

- MagicAI is the host SaaS, tenant shell and primary application runtime.
- WorkCore at `app/Domains/WorkCore` is authoritative for operational business data and actions.
- The canonical Interaction Engine belongs in `app/Domains/InteractionEngine` once convergence work begins.
- The Titan Zero Chatbot remains at `app/Extensions/Chatbot` and owns chatbot presentation, channels, PWA/device adapters and generative UI integration.
- Embedded chatbot WorkCore code is compatibility-only and must never shadow `App\Domains\WorkCore`.
- Extension packages remain under `app/Extensions`; do not recreate extension code under parallel roots.

## Non-negotiable rules

1. Preserve tenant, user, device and correlation identifiers through HTTP, queues, offline storage, synchronisation and domain events.
2. Never cache credentials, provider secrets or sensitive API responses in service-worker Cache Storage.
3. Never automatically delete unsynchronised device records.
4. Never activate all imported extensions at once. Use manifest validation and progressive qualification.
5. Do not introduce permanent `source`, `integration`, `merge`, `legacy-copy` or donor-code folders.
6. Do not delete apparently unused code until routes, providers, events, dynamic resolution, scheduled jobs, queues, migrations and JavaScript imports have been traced.
7. Keep compatibility shims explicit, documented and covered by tests.
8. Use failing tests before repairs. Run the smallest relevant test set after each change.
9. Keep commits scoped to one task from `TITAN_ZERO_UPGRADE_PLAN.md`.
10. Do not merge to `main` without backend, frontend, extension-health and tenant-isolation evidence.

## Required validation hierarchy

```text
PHP syntax → Composer validation → architecture tests → focused Pest tests
→ Laravel boot/route checks → npm build → Playwright browser tests
→ extension health audit → release verification
```

## Build Web Apps boundary

Build Web Apps work must extend the existing Blade, React, Alpine and Vite structure. Do not create a separate frontend application. Preserve the persistent chat bar, operational workspace, responsive mobile/tablet/desktop shell and WorkCore capability-driven navigation.

## Source baseline

The complete extracted source baseline used for this branch is commit:

```text
a76eee53af7b72b9f740adb3fa757b3f4d527bd6
```

The extension inventory is recorded in `EXTENSIONS_IMPORT_MANIFEST.json`.
