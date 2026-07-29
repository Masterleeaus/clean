# Titan Zero Clean Integration Workspace

Private integration repository for the MagicAI host, WorkCore operational domain, Titan Zero Chatbot/PWA, Interaction and Wizard runtimes, five-tier intelligence, extensions and supporting services.

## Current working rule

`main` is the source baseline. Agents preserve old branches as evidence, but port only unique, verified deltas onto a fresh integration branch built from current `main`. Old branches are not merged wholesale.

## Architecture boundaries

- **MagicAI host:** authentication, users, tenancy, billing and application shell.
- **WorkCore:** sole authority for operational business records and mutations.
- **Titan Zero:** intent, orchestration, governance and delegation.
- **Interaction Engine:** governed interaction and wizard execution.
- **Chatbot/PWA:** user experience, device storage, offline state and synchronisation.
- **Titan Vault:** credentials and protected configuration.

## Documentation

Begin with [`docs/README.md`](docs/README.md). Historical plans and source-specific reports are retained under `docs/archive/`; extracted doctrine and blueprints are under `docs/reference/titan-library/`.

> This repository contains licensed/private source and must remain private.
