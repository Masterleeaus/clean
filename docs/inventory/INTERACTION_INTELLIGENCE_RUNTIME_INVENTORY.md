# Interaction, Wizard and Five-Tier Intelligence Runtime Inventory

Source commit: `86ffd1a3c916a961138cf0f44779480b1012ec3a`

This is an evidence inventory. It does not declare a runtime active merely because files exist.

## Runtime roots

| Key | Path | Exists | Files | Bytes | Providers | Routes | Migrations | Tests |
|---|---|---:|---:|---:|---:|---:|---:|---:|
| `interaction_package_primary` | `packages/titanzero/interaction-engine` | true | 386 | 606344 | 4 | 2 | 12 | 2 |
| `interaction_package_hyphenated` | `packages/titan-zero/interaction-engine` | true | 1 | 1270 | 0 | 0 | 0 | 0 |
| `interaction_domain` | `app/Domains/InteractionEngine` | false | 0 | 0 | 0 | 0 | 0 | 0 |
| `workcore_wizards` | `app/Domains/WorkCore/System/Modules/Wizards` | true | 33 | 143651 | 1 | 2 | 0 | 0 |
| `chatbot_ai_primary` | `app/Extensions/Chatbot/System/TitanAI` | true | 864 | 2316465 | 39 | 1 | 33 | 4 |
| `chatbot_ai_secondary` | `app/Extensions/TitanZeroChatbot/System/TitanAI` | true | 864 | 2316465 | 39 | 1 | 33 | 4 |

## Composer and package identity

### `interaction_package_primary`

- Path: `packages/titanzero/interaction-engine`
- Package name: `titanzero/interaction-engine`
- Laravel providers: `[]`

### `interaction_package_hyphenated`

- Path: `packages/titan-zero/interaction-engine`
- Package name: `titanzero/interaction-engine`
- Laravel providers: `['TitanZero\\Interaction\\Providers\\InteractionServiceProvider']`

### Root Composer activation

- Requires package: **false**
- Registers primary path: **false**
- Registers hyphenated path: **false**

## Tree comparisons

### `interaction_package_paths`

- Left: `packages/titanzero/interaction-engine`
- Right: `packages/titan-zero/interaction-engine`
- Common files: **1**
- Identical files: **0**
- Different files: **1**
- Only left: **385**
- Only right: **0**

### `chatbot_ai_paths`

- Left: `app/Extensions/Chatbot/System/TitanAI`
- Right: `app/Extensions/TitanZeroChatbot/System/TitanAI`
- Common files: **864**
- Identical files: **864**
- Different files: **0**
- Only left: **0**
- Only right: **0**

## Five-tier file counts

- `chatbot_ai_primary`: `{'tier0': 1, 'tier1': 30, 'tier2': 85, 'tier3': 69}`
- `chatbot_ai_secondary`: `{'tier0': 1, 'tier1': 30, 'tier2': 85, 'tier3': 69}`

## Findings

- **confirmed host wiring gap:** The root Composer project does not both register and require titanzero/interaction-engine, so package auto-discovery cannot be relied on from a clean install.
- **confirmed active bounded runtime candidate:** WorkCore contains its own Wizard module and provider; this runtime governs operational wizard capabilities inside WorkCore and must not be conflated with the universal Interaction Engine package.
- **duplicate runtime risk:** Both Chatbot and TitanZeroChatbot contain TitanAI trees. They share 864 relative files, with 864 identical and 0 divergent copies.

## Required disposition rule

- Keep one physical Composer package root for `titanzero/interaction-engine`.
- Treat WorkCore Wizards as an operational-domain module, not a replacement Interaction Engine.
- Treat Chatbot/PWA AI code as presentation/device orchestration or compatibility code until provider and call-path evidence proves host authority.
- Do not activate two TitanAI trees or two package roots with the same namespaces.
- Preserve unique metadata, tests and contracts before deleting a duplicate path.

Full file-level comparisons and reference locations are in the JSON inventory.
