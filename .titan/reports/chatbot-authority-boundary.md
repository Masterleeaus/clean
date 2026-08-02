# Chatbot Extension Authority Boundary

## Pass

Pass 2 — Authority and Compatibility Boundary

## Decision

`app/Extensions/Chatbot` is the only authoritative, bootable chatbot extension tree.

`app/Extensions/TitanZeroChatbot` is retained temporarily as a non-bootable compatibility snapshot. It must not register providers, routes, migrations, views, assets, policies, commands, events, AI services, WorkCore services or PWA resources.

## Enforcement

### Marketplace

`MarketplaceServiceProvider::$extensionProviders['chatbot']` continues to resolve to:

`App\Extensions\Chatbot\System\ChatbotServiceProvider`

No marketplace registration exists for the legacy snapshot.

### Composer

The root PSR-4 mapping is `App\ => app/`. Therefore the canonical namespace `App\Extensions\Chatbot\...` resolves through `app/Extensions/Chatbot/...`, not through the legacy directory.

### Canonical Descriptor

`app/Extensions/Chatbot/extension-authority.json` records the authoritative root, provider and owned extension surfaces.

### Legacy Manifest

`app/Extensions/TitanZeroChatbot/extension.json` is now marked:

- `type: legacy-disabled`
- `enabled: false`
- `bootable: false`
- `provider: null`

### Legacy Provider Guard

The provider file under the legacy tree now uses the distinct namespace:

`App\Extensions\TitanZeroChatbot\System`

Attempting to register it throws a `LogicException` directing callers to the canonical provider. It loads no extension resources.

## Compatibility Policy

The legacy files remain present only to support comparison during the remaining migration passes. No runtime fallback to the legacy tree is allowed.

Removal of the legacy snapshot is deferred until:

1. the five-application registry is complete;
2. routes and migrations are verified;
3. PWA and offline tests pass;
4. no required file exists only in the legacy tree.

## Verification Added

`tests/Feature/TitanArchitecture/ChatbotExtensionAuthorityTest.php` verifies:

- marketplace ownership;
- canonical configuration;
- disabled legacy manifest;
- legacy provider namespace isolation;
- absence of route and migration registration in the guard;
- Composer namespace resolution;
- presence of the canonical authority descriptor.

## Remaining Limitation

This pass establishes provider and discovery authority. It does not yet convert the 14 application/template definitions into the canonical five-application registry. That is Pass 3.
