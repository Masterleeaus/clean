# Duplicate Systems Report

## Scope

Chatbot-related extension code under `app/Extensions`.

## Confirmed Duplicate System

### `Chatbot` and `TitanZeroChatbot`

The repository contains two large parallel extension trees:

- `app/Extensions/Chatbot`
- `app/Extensions/TitanZeroChatbot`

### Evidence

- Both expose an `extension.json` named `Chatbot` with version `6.9.0-unified-ai-shell` and identical content/blob identity.
- Both contain `System/ChatbotServiceProvider.php` declaring the `App\Extensions\Chatbot\System` namespace.
- Both contain identical `System/Titan/TitanRegistry.php` files declaring the same namespace and classes.
- Both contain parallel models, controllers, migrations, views, PWA assets, Titan AI modules, WorkCore app mappings, tests and inventories.
- Search results show duplicated channel providers, agent providers, builder files and operational shell files.

### Proven Drift

The trees are not safely identical anymore.

The provider under `app/Extensions/Chatbot`:

- resolves `TitanZeroFeatureFlags`;
- conditionally registers WorkCore integration;
- conditionally registers WorkCore AI providers.

The provider under `app/Extensions/TitanZeroChatbot`:

- lacks the feature-flag resolver;
- registers WorkCore integration unconditionally;
- registers WorkCore-related providers unconditionally when classes exist.

This proves that fixes and architecture controls can land in one tree without reaching the other.

## Risks

### Namespace collision

Both trees declare the same `App\Extensions\Chatbot` namespaces. Whichever path is discovered first may determine which class version is loaded.

### Duplicate provider boot

If marketplace or custom discovery registers both providers, routes, policies, observers, broadcast channels and assets can be registered twice.

### Duplicate migrations

Both trees contain parallel migration files. Loading both can cause table-exists failures, repeated schema mutation or inconsistent migration history.

### Duplicate route names and paths

Both providers define the same route groups and names. Runtime boot may fail or silently override routes.

### Fix divergence

The primary tree's feature-flag improvements already demonstrate divergence. Future security or architectural changes could be applied to only one copy.

### Packaging ambiguity

Build, archive and deployment scripts may package the wrong tree or include both, especially because both manifests identify as the same extension.

## Adjacent Overlap Requiring Review

The extension also embeds or parallels capabilities represented by adjacent extensions, including:

- ChatbotAgent;
- ChatbotBooking;
- ChatbotCustomerTag;
- ChatbotEcommerce;
- ChatbotVoice;
- ChatbotVoiceCall;
- channel-specific runtime providers;
- AI agent/tool chatbot capabilities.

These are not automatically defects. Some may be intentional modular extensions while others may be historical imports now embedded into the unified chatbot. Each must be classified as:

1. canonical external extension;
2. canonical embedded module;
3. compatibility adapter;
4. obsolete duplicate.

Do not delete these components solely from name similarity.

## Required Resolution Sequence

1. Inspect extension discovery and registration paths.
2. Identify which tree is used by production packaging and marketplace registration.
3. Compare all files or repository trees, not selected samples only.
4. Declare the authoritative tree in architecture documentation.
5. Disable provider, route and migration discovery from the non-authoritative tree.
6. Move any unique code into the authoritative tree through reviewed deltas.
7. Preserve upgrade aliases only where installation metadata requires them.
8. Add CI checks for duplicate extension names, duplicate namespaces, route names and migration identifiers.
9. Remove the redundant tree only after the compatibility and deployment tests pass.

## Recommended Authority

Based on this static audit, `app/Extensions/Chatbot` is the stronger candidate because its service provider includes the newer feature-flagged WorkCore boundary. This is a recommendation, not a deletion approval; production discovery and packaging must still be verified.

## Status

Confirmed architectural defect. Resolution pending.
