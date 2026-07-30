# PWA, Offline Runtime and Chatbot Extension Architecture

**Status:** Canonical documentation boundary for the current reconciliation programme.

**Source baseline:** `integration/current-main-reconciliation` at `fa607d769a4f72ba287801b027cc42dcf56aa549`, with inventory generated on `agent/documentation-reconciliation`.

This document defines the current canonical Chatbot/PWA extension, the compatibility status of its duplicate tree, and the required safety model for local storage, encryption, offline mutation and synchronisation.

## 1. Physical extension inventory

| Extension | Path | Files | Current disposition |
|---|---|---:|---|
| Primary Chatbot | `app/Extensions/Chatbot` | 1,548 | Canonical intended extension |
| Secondary Chatbot copy | `app/Extensions/TitanZeroChatbot` | 1,542 | Frozen compatibility/reference copy |

The generated full-tree comparison found:

- 1,542 common relative files;
- 1,541 byte-identical common files;
- one divergent common file: `System/ChatbotServiceProvider.php`;
- six primary-only Titan Train files;
- no secondary-only files.

The primary-only files are:

- `resources/assets/css/titan-train.css`
- `resources/assets/js/titan-train-workspace.js`
- `resources/pwa/chatbot-pwa/apps/titan-train.js`
- `resources/titan-apps/TemplateSchemas/titan-train.json`
- `resources/titan-apps/TitanSuiteTemplates/titan-train/config.json`
- `tests/js/titan-train-native-workspace.test.js`

Detailed evidence is stored in:

- `docs/inventory/PWA_OFFLINE_RUNTIME_INVENTORY.md`
- `docs/inventory/PWA_OFFLINE_RUNTIME_INVENTORY.json`

## 2. Canonical extension decision

`app/Extensions/Chatbot` is the canonical intended extension because:

1. host feature flags point to `App\Extensions\Chatbot\System\ChatbotServiceProvider`;
2. external source contains extensive references to the primary namespace and no secondary namespace references;
3. the primary service provider consumes `TitanZeroFeatureFlags`;
4. the primary conditionally registers WorkCore-related providers only when WorkCore is enabled;
5. the secondary provider registers WorkCore-related providers unconditionally;
6. the primary contains the six newer Titan Train files;
7. the primary path is used by build, audit, test and application configuration.

The secondary extension must not be activated in parallel. Its PHP files declare the primary `App\Extensions\Chatbot` namespace, so activating both directories risks duplicate class, provider, route, migration, asset and event registration.

## 3. Compatibility and removal rule

`app/Extensions/TitanZeroChatbot` remains frozen until a focused source reconciliation proves that:

- no extension registry discovers it independently;
- no installer, marketplace record or deployment process depends on its directory name;
- no published asset path points to it;
- no migration loader activates it;
- no external archive or updater expects it;
- all provenance required from it is preserved;
- a clean checkout, install, upgrade and rollback pass after removal.

No new feature work may be added only to the secondary copy. Agents must change the canonical primary extension and must not manually maintain two identical runtime trees.

## 4. Provider difference

The primary provider:

- resolves `TitanZeroFeatureFlags`;
- registers TitanAI providers through a feature-aware method;
- conditionally registers WorkCore AI and WorkCore app integration only when WorkCore is enabled;
- protects the canonical WorkCore namespace from the embedded compatibility runtime unless an explicit legacy flag is enabled and canonical WorkCore is absent.

The secondary provider:

- does not consume `TitanZeroFeatureFlags`;
- registers WorkCore AI, embedded WorkCore runtime integration and WorkCore app integration unconditionally.

Therefore the secondary provider is superseded as current bootstrap guidance. Its unconditional behaviour must not be reintroduced when the duplicate tree is eventually removed.

## 5. PWA component model

The canonical PWA consists of:

```text
Chatbot Blade/application surface
        ↓
PWA registration and install runtime
        ↓
Service worker + static offline shell
        ↓
IndexedDB device database
        ↓
Device vault and local repositories
        ↓
Outbox, conflict store and sync inbox
        ↓
Chatbot sync engine
        ↓
Authenticated Chatbot API
        ↓
Canonical WorkCore actions for operational mutation
```

The PWA may own local projections, drafts, queued intentions, encrypted local material and conflict state. It does not own canonical operational server truth.

## 6. Service-worker policy

The canonical service worker is:

`app/Extensions/Chatbot/resources/pwa/chatbot-sw.js`

Current source demonstrates the following intended safeguards:

- authenticated API and sensitive paths are excluded from fetch caching;
- navigation responses remain network-only, with only the static offline page as fallback;
- cache writes reject `no-store`, cookies, private-response headers, authenticated-user headers and opaque responses;
- cache generations are versioned and bounded;
- background sync does not directly read IndexedDB or API credentials;
- background and periodic sync only wake controlled clients, which own tenant context and authentication;
- one previous static shell is retained for rollback.

Required verification before release:

1. confirm every precached asset is public and contains no tenant/user data;
2. confirm no generated API response can be misclassified as a static asset;
3. verify cache cleanup does not remove data required for rollback;
4. test multiple signed-in users on one browser profile;
5. validate notification URLs against an allowlist before navigation;
6. confirm cache names and service-worker scope cannot collide with another extension;
7. test missing precache assets and partial deployment behaviour.

## 7. IndexedDB model

The canonical device database is:

- name: `titan-chatbot-device`;
- current version: `5`;
- implementation: `resources/pwa/chatbot-pwa/device-db.js`.

Current stores include:

- conversations;
- messages;
- drafts;
- participants;
- customer summaries;
- attachments;
- reviews;
- support requests;
- canned responses;
- knowledge articles;
- outbox;
- metadata;
- conflicts;
- generative UI state;
- sync records;
- sync inbox.

The database includes legacy-record migration logic and adds missing stores/indexes during `onupgradeneeded`.

Required rules:

- every tenant-owned local record must carry `tenant_id` or inherit an unambiguous tenant partition;
- actor and device context must be retained for queued mutations;
- schema upgrades must be monotonic and tested from every supported prior version;
- failed migrations must not clear or silently recreate the database;
- unsynchronised records, conflicts and attachments must never be deleted automatically;
- local reset must be an explicit user/admin action with a clear data-loss warning;
- local storage quotas and attachment eviction must preserve unsynchronised evidence.

## 8. Device vault

The canonical browser vault uses:

- PBKDF2 with SHA-256;
- 250,000 iterations;
- a per-device random salt;
- AES-256-GCM;
- a random 96-bit IV per encrypted value;
- a memory-only unlocked session key.

This is a sound browser cryptography foundation, but release verification must still cover:

- passphrase strength and rate limiting;
- lock behaviour on inactivity, logout, tenant switch and device revocation;
- recovery and reset UX;
- protection against storing raw provider keys outside encrypted envelopes;
- cross-tab vault state;
- key rotation and encrypted-record version migration;
- browser/device support failure behaviour.

The vault `reset()` method clears all device stores. It must remain explicit and must never be triggered automatically during normal upgrade, logout, sync failure or conflict recovery.

## 9. Outbox and conflict model

The canonical generic outbox provides:

- client-generated operation UUIDs;
- idempotency keys;
- ordered retries;
- exponential backoff with jitter;
- a maximum-attempt transition to `needs-review`;
- explicit retry and cancellation;
- `409`/`412` conflict capture;
- attachment replay;
- browser online and Background Sync triggers.

Important audit requirement:

The generic outbox currently persists operation headers and bodies directly into IndexedDB. The vault exists, but the outbox implementation shown does not itself encrypt those fields. Before production release, every caller must be classified and one of these rules enforced:

1. sensitive values and authorization credentials are never inserted into queued headers/bodies; or
2. sensitive queued payloads are encrypted before persistence and decrypted only inside the authenticated client execution path.

Do not store bearer tokens, provider secrets, raw payment credentials or unrestricted sensitive API responses in the outbox.

Cancellation must not be used as automatic cleanup. A user-visible audit trail is required for cancelled operational intentions.

## 10. Sync protocol

The canonical Chatbot sync engine uses:

- `/api/v2/chatbot/devices/register`;
- `/api/v2/chatbot/sync/status`;
- `/api/v2/chatbot/sync/bootstrap`;
- `/api/v2/chatbot/sync/push`;
- `/api/v2/chatbot/sync/pull`;
- `/api/v2/chatbot/sync/acknowledge`.

It supports:

- client device registration;
- bootstrap pagination;
- per-scope cursors;
- weak-network limits;
- push before pull;
- durable sync inbox application;
- acknowledgements;
- last-successful-sync state;
- explicit local statuses including conflict and needs-review.

Required server-side verification:

- the authenticated server resolves company and actor context independently of body/query tenant claims;
- a device ID supplied from localStorage is never trusted as authorization proof;
- device registration is tenant- and actor-bound and revocable;
- push operations map only to registered, permitted and idempotent actions;
- pull cursors cannot cross tenant or role boundaries;
- acknowledgements cannot acknowledge another device or tenant's changes;
- server responses preserve correlation and causation IDs;
- WorkCore remains the mutation authority for operational records;
- deleted-locally and deleted-remotely states have explicit retention and conflict policy.

## 11. Offline operational authority

The device may prepare and queue operational intentions, but server reconciliation must terminate at:

```text
Authenticated host context
→ device and sync validation
→ Interaction Engine clarification/approval when applicable
→ registered WorkCore action
→ entitlement
→ permission
→ confirmation
→ idempotency
→ transactional handler
→ audit, events and outbox
```

Direct device sync into operational tables is prohibited.

## 12. Current risks and classifications

| Finding | Classification | Required action |
|---|---|---|
| Two nearly identical extension trees | Confirmed duplication | Keep only primary active; remove secondary in focused source PR after dependency trace |
| Secondary provider registers WorkCore/TitanAI unconditionally | Confirmed superseded bootstrap behaviour | Never activate secondary provider or copy this behaviour back |
| Six Titan Train files exist only in primary | Confirmed unique delta | Preserve primary files and test coverage |
| 93 migrations exist in each tree | High duplicate-activation risk | Prove only one extension migration root can load |
| 40 provider-like files exist in each tree | High duplicate-provider risk | Enforce one extension provider graph |
| Service worker avoids authenticated/API caching | Positive source evidence | Verify deployed behaviour and multi-user isolation |
| Outbox stores headers/body directly | Security review requirement | Prove payloads contain no secrets or encrypt sensitive queued content |
| Device ID stored in localStorage | Identity-risk input | Treat as identifier only; require server-bound device trust |
| IndexedDB includes migration and conflict stores | Positive source evidence | Test supported-version upgrades and no-loss recovery |

## 13. Documentation disposition

The many pass reports, merge manifests and file inventories inside both extension roots are source-local provenance, not current global architecture guidance. They may remain with the extension until a dedicated extension-documentation pass merges unique information into `docs/` and archives or removes superseded copies.

No extension-local report may override this canonical document, the global authority map or current source evidence.

## 14. Verification gates before secondary removal

1. extension registry lists only canonical Chatbot as enabled;
2. PHP classmap/autoload has no dependency on the secondary path;
3. all external primary/secondary path references are classified;
4. route names and URIs remain unchanged after removal;
5. provider count remains exactly one;
6. migrations load exactly once;
7. published assets and service worker are unchanged;
8. IndexedDB schema and cache version are unchanged or migrated;
9. PWA install/update/offline tests pass;
10. outbox replay and conflict recovery pass;
11. tenant/device isolation tests pass;
12. Titan Train files and tests remain present;
13. release rollback is documented and tested.

## 15. Current disposition summary

| Item | Disposition |
|---|---|
| `app/Extensions/Chatbot` | Canonical intended Chatbot/PWA extension |
| `app/Extensions/TitanZeroChatbot` | Frozen near-exact compatibility copy; do not activate |
| Primary `ChatbotServiceProvider` | Canonical provider candidate with feature-aware registration |
| Secondary `ChatbotServiceProvider` | Superseded bootstrap behaviour; reference only |
| `titan-chatbot-device` IndexedDB | Canonical device database candidate |
| `chatbot-sw.js` | Canonical service worker candidate |
| `TitanDeviceVault` | Canonical local cryptography foundation candidate |
| `TitanOutbox` and Chatbot sync engine | Canonical offline queue/sync candidates subject to payload and server-boundary audit |
| Operational server truth | WorkCore only |
