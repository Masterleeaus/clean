# Titan Agent OS Registry

The Registry is the searchable catalogue of Agent OS objects. It references versioned Kernel definitions and generated system evidence; it does not execute actions.

## Registry families

- capabilities
- actions
- workflows
- validators
- policies
- roles
- providers
- agents
- contracts
- graphs
- documentation

## Rules

1. Every object has a globally unique ID, kind, schema version, semantic version, lifecycle status, owner, verification evidence, dependencies and usage references.
2. A source file or document does not become active merely because it exists on disk.
3. Duplicate IDs, providers, actions, capability keys or contract identities fail validation.
4. Derived and generated entries identify source commit and freshness.
5. Deprecated, superseded, rejected and quarantined objects remain queryable but cannot be selected for new execution.
6. Runtime success metrics may update performance fields but cannot silently change authority, permissions or lifecycle status.
7. Registries are indexes, not competing implementations. Canonical definitions remain in their owning Kernel or provider path.

## Planned files

- `capabilities.json`
- `actions.json`
- `workflows.json`
- `validators.json`
- `policies.json`
- `roles.json`
- `providers.json`
- `agents.json`
- `contracts.json`
- `graphs.json`
- `documentation.yaml`

Only `documentation.yaml` is seeded in the bootstrap pass. Other registries remain planned until their source definitions and validators are introduced.
