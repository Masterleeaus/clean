# Pass 1 — Host Boot and Dependency Stabilisation

## Scope

This pass makes the MagicAI–WorkCore–Interaction Engine host reproducibly installable and proves that the authoritative providers and Interaction Engine routes boot once.

## Changes

- The merged host uses one explicit Interaction Engine provider-registration path.
- Laravel package auto-discovery is disabled for the local Interaction Engine package to avoid duplicate registration.
- A static host contract verifies the local path repository, package requirement, lock entry, provider registration, and migration-table uniqueness.
- Laravel test scaffolding and `HostBootTest` verify WorkCore and Interaction Engine providers, route-name uniqueness, offline defaults, cloud-AI opt-in, and default-deny authority.
- GitHub Actions refreshes the local package lock when necessary, then performs a clean Composer install and Laravel boot checks.
- Host runtime and extension requirements are documented.

## Explicit exclusions

- No PWA visual changes.
- No WorkCore domain-model changes.
- No Interaction Engine workflow changes.
- No migration is deleted or renamed because the cumulative package currently has no duplicate table creator.

## Gate

The pass is complete only when the branch contains the generated Interaction Engine lock entry and CI proves:

1. `composer install` succeeds from the lock file.
2. `php artisan about` boots.
3. Interaction routes are discoverable.
4. `HostBootTest` passes.
5. Static provider and migration contracts pass.
