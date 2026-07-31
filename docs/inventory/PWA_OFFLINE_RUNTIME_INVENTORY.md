# PWA, Offline and Chatbot Extension Runtime Inventory

Source commit: `e4771ea7efb5b7867bd14247ae9b384bfc028511`

This inventory distinguishes source presence, duplication and bootstrap evidence from verified runtime activation.

## Extension roots

| Extension | Path | Files | Bytes | Providers | Routes | Migrations | Tests |
|---|---|---:|---:|---:|---:|---:|---:|
| Primary | `app/Extensions/Chatbot` | 1548 | 5873858 | 40 | 7 | 93 | 54 |
| Secondary | `app/Extensions/TitanZeroChatbot` | 1542 | 5838608 | 40 | 7 | 93 | 53 |

## Full-tree comparison

- Common relative files: **1542**
- Byte-identical common files: **1541**
- Divergent common files: **1**
- Primary-only files: **6**
- Secondary-only files: **0**

## Offline subsystem candidates

| Category | Primary files | Secondary files |
|---|---:|---:|
| `service_worker` | 22 | 22 |
| `manifest` | 87 | 86 |
| `indexeddb` | 43 | 42 |
| `vault_crypto` | 61 | 61 |
| `outbox_queue` | 58 | 57 |
| `sync_conflict` | 230 | 225 |
| `offline_cache` | 177 | 174 |
| `device_identity` | 143 | 142 |
| `background_push` | 76 | 74 |

## External activation references

- `primary_namespace`: **84** references
- `secondary_namespace`: **0** references
- `primary_path`: **20** references
- `secondary_path`: **5** references
- `primary_provider`: **3** references
- `secondary_provider`: **0** references

## Service-worker registration candidates

- Files containing registration/runtime references: **10**

## Findings

- **partial duplicate extension:** The extension trees share 1542 files; 1541 are identical and 1 differ.
- **canonical activation evidence:** Repository bootstrap and source references favour App\Extensions\Chatbot over App\Extensions\TitanZeroChatbot.
- **offline subsystem evidence:** service_worker candidates: primary 22, secondary 22; file-level paths are recorded in the JSON inventory.
- **offline subsystem evidence:** indexeddb candidates: primary 43, secondary 42; file-level paths are recorded in the JSON inventory.
- **offline subsystem evidence:** vault_crypto candidates: primary 61, secondary 61; file-level paths are recorded in the JSON inventory.
- **offline subsystem evidence:** outbox_queue candidates: primary 58, secondary 57; file-level paths are recorded in the JSON inventory.
- **offline subsystem evidence:** sync_conflict candidates: primary 230, secondary 225; file-level paths are recorded in the JSON inventory.

## Required disposition rule

- `app/Extensions/Chatbot` is the intended canonical extension unless bootstrap evidence proves otherwise.
- `app/Extensions/TitanZeroChatbot` remains frozen compatibility/reference material until every external caller, provider, route, asset and migration dependency is traced.
- Do not activate two byte-identical provider or migration trees.
- Do not delete unsynchronised local data or change IndexedDB schemas without explicit migration and recovery behaviour.
- Service-worker caches must not contain credentials, provider secrets or sensitive API responses.
- Offline operational mutations must reconcile through canonical WorkCore actions.

Full paths, metadata, differences, references and registration snippets are stored in the JSON inventory.
