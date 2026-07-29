# Titan Zero PWA and MobileKit Upgrade Design

Date: 2026-07-30
Repository: `Masterleeaus/clean`
Status: Approved baseline design

## Objective

Upgrade the existing MagicAI + WorkCore + Titan Zero Chatbot application into the MVP cleaning and home-services platform, with a customisable desktop backend and one role-aware, offline-first PWA suite.

## Architectural boundaries

- MagicAI remains the desktop host and administration surface.
- WorkCore is the sole authority for operational records, validation, permissions, transactions, events and audit history.
- Titan Zero owns conversation, orchestration, AI workflows and builder assistance.
- The Chatbot PWA is the device-facing application for owners, office staff, field workers and customers.
- MobileKit is a UI-pattern donor only. Its full Bootstrap runtime and demo application must not become a second frontend authority.
- The no-code mobile builder is a donor for previews, theme controls, navigation configuration, feature selection, assets and manifest concepts.

## MVP profiles

- Owner / manager
- Office / dispatcher
- Field worker
- Customer

All profiles use one application shell and receive role-scoped navigation, widgets, workflows, permissions and offline packs.

## Upgrade sequence

1. Forensic repository baseline and security sanitisation.
2. Compare repository PWA against Pass 12 and merge only missing or newer code.
3. Consolidate the mobile shell.
4. Port selected MobileKit interaction patterns into Titan-owned components.
5. Add role-aware application profiles.
6. Build cleaning and home-services operational screens.
7. Connect Interaction Engine mobile forms and wizards.
8. Expose offline, sync, conflict and recovery states.
9. Expand theme, navigation and dashboard customisation.
10. Add PWA manifest, installation, notifications and secure deep links.
11. Add MagicAI desktop builder services and publishing controls.
12. Complete tenancy, security, performance and release hardening.

## Security-first baseline

The repository import history exposed an application key and a source-archive decryption key. The current working tree may no longer contain those files, but Git history retains the values. They must be treated as compromised.

Required actions before feature development:

- Rotate the Laravel application key for every deployed environment using the exposed value.
- Rotate or retire the source transport encryption key.
- Remove obsolete transport workflows and external source-part references.
- Decide whether to rewrite repository history or preserve history with documented credential rotation.
- Run secret scanning across all refs and commits.
- Confirm `.env`, generated credentials, runtime state and user data are excluded.

## Builder boundaries

The MVP builder may configure branding, navigation, dashboards, forms, wizard definitions, feature flags, role visibility, offline packs and PWA manifest settings.

It must not permit arbitrary PHP, JavaScript, SQL, raw database access, unrestricted CSS, bypassing WorkCore permissions or direct operational writes.

## Data flow

User action or conversation
→ Titan Zero intent and orchestration
→ authorised WorkCore command/query contract
→ validation and transaction
→ domain event and audit record
→ PWA local state/outbox
→ cloud synchronisation and conflict handling

## Testing gates

Every pass must include relevant unit, integration and end-to-end tests. Release gates include tenant isolation, role enforcement, offline cold start, interrupted synchronisation, stale writes, duplicate completion, deep-link authorisation, manifest rollback, accessibility and phone/tablet/desktop compatibility.

## Delivery strategy

Each pass uses an isolated branch and pull request. `main` remains the stable integration branch. UI donor code must be traceable in a donor register, and every merge must distinguish copied code, adapted patterns and newly authored Titan Zero components.
