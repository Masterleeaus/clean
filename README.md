# Titan Zero — Clean Repository

This repository is the authoritative integration workspace for Titan Zero.

## Intended architecture

- **MagicAI** — Laravel host, authentication, users, subscriptions, desktop UI and extension lifecycle.
- **WorkCore** — authoritative operational business domains, tenancy, permissions, governed actions, audit and events.
- **Interaction Engine** — user, user-plus-AI and delegated-AI workflows, authority, confidence, offline interaction state and resumable execution.
- **Titan Zero Chatbot PWA** — fourteen integrated apps, five-tier AI system, generative UI, local device runtime, offline sync and mobile/tablet interface.

## Repository workflow

- `main` remains the reviewed integration branch.
- Large application imports and upgrade passes are prepared on isolated branches.
- Pull requests are used to review changes before merging.
- Secrets, generated dependencies, runtime caches and local environment files must never be committed.

## Current source authority

The initial backend import is based on the verified `MagicAI-v10.91-WorkCore-InteractionEngine-FULL-MERGED.zip` application base.
