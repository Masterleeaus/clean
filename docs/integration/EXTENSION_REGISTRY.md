# Extension Registry

## Purpose

The Titan extension registry discovers optional integrations without allowing them to replace host or WorkCore authority.

## Discovery

The registry reads manifests from:

`app/Extensions/*/extension.json`

A manifest is accepted only when its schema, identifier, type, provider class, host requirements and declared capabilities are valid.

## Allowed ownership

Extensions may provide channels, integrations, intelligence helpers, UI surfaces and specialised tools. They may not declare the prohibited `domain` type, duplicate a capability or own company tenancy.

WorkCore is loaded through `bootstrap/providers.php` as a first-party domain and is intentionally absent from the extension registry. Titan Intelligence is likewise a first-party host subsystem and is not installed as an optional extension.

## Enablement

An extension provider boots only when:

- the manifest validates;
- host requirements are compatible;
- a `titan_extensions` registry record exists;
- `enabled` is true;
- compatibility is true;
- status permits execution.

Invalid or incomplete extensions remain visible in diagnostics but fail closed.

## Installed extension

### Titan Maps Intelligence 1.0.0

- Type: `integration`
- Default state: disabled
- Provider: `App\\Extensions\\TitanMapsIntelligence\\TitanMapsIntelligenceServiceProvider`
- Declared tools: 12 Titan Quattro capabilities
- Declared permissions: search, candidate review, promotion, provider management, territory analysis, usage and restricted raw-data access
- WorkCore dependencies: CRM and supply gateways

The extension becomes operational only after explicit registry enablement and a company-scoped provider credential is stored in Titan Vault.

## Adding future extensions

1. Validate ownership against the host and WorkCore boundaries.
2. Place original source under a unique extension directory.
3. Use an allowed manifest type.
4. Declare every capability, permission and host requirement.
5. Register no direct operational-table writes.
6. Add company isolation, permission and failure-path tests.
7. Enable only after compatibility verification.
