# Titan Zero Interaction Engine — Onboarding Upgrade Plan

## Branch purpose

This branch integrates reusable onboarding and guided-interaction capabilities into the **Titan Zero Interaction Engine** without creating a second onboarding engine, onboarding runtime, or parallel wizard platform.

The Interaction Engine remains the single orchestration and interaction authority. WorkCore remains the only authority permitted to mutate operational business records.

## Architectural rules

1. All recovered onboarding features become reusable Interaction Engine capabilities.
2. Wizard execution remains a subsystem of the Interaction Engine.
3. No donor-specific `OnboardingPro` runtime, service container, route namespace, database authority, or lifecycle may survive the merge.
4. Cleaning-specific language belongs in interaction definitions, not shared platform services.
5. Every operational write must pass through governed WorkCore commands.
6. Existing namespaces and compatibility bridges must remain functional until callers are migrated and tested.
7. Offline-created records must retain tenant, user, device, version and synchronisation metadata.
8. No feature is considered complete until it is reachable through routes, service bindings, policies, UI entry points and tests.

## Phase 1 — Establish the cumulative baseline

- Extract and inventory the reconciled Interaction Engine archive.
- Verify archive checksum and ZIP integrity.
- Compare source, migrations, configuration, routes, tests and compiled artefacts against the repository baseline.
- Preserve the restored compatibility bridges.
- Remove temporary, generated or donor-only files that do not belong in source control.
- Document every accepted, superseded and rejected file.

### Exit criteria

- A deterministic source inventory exists.
- PHP and JSON syntax validation passes.
- No duplicate runtime authority is present.
- The cumulative Interaction Engine boots in isolation.

## Phase 2 — Generic presentation capabilities

Implement reusable Interaction Engine presentation services:

- `InteractionIntroduction`
- `InteractionWelcomeScreen`
- `InteractionPresentationProfile`
- `InteractionAnnouncement`
- `InteractionBanner`
- `InteractionNotice`
- `InteractionHelpPanel`
- `InteractionGuidedTour`
- `InteractionProgressPrompt`
- `InteractionResumePrompt`
- `InteractionCompletionScreen`
- `InteractionSectionSummary`

Add contracts, DTOs, policies, repositories, migrations, service-provider bindings and tests.

### Exit criteria

- Any interaction definition can enable presentation capabilities declaratively.
- No capability contains cleaning- or onboarding-specific assumptions.
- Presentation state survives pause, resume and offline restart.

## Phase 3 — Engagement and participant state

Implement shared engagement services:

- `InteractionSurvey`
- `InteractionFeedbackForm`
- `InteractionAcknowledgement`
- `InteractionParticipantState`
- `InteractionDismissalState`
- `InteractionFirstRunState`
- `InteractionUserPreference`
- progress and completion notifications

Create shared storage for profiles, participants, acknowledgements, announcements, surveys, responses and preferences.

### Exit criteria

- State is tenant-, user- and device-aware.
- Acknowledgements are auditable and immutable where required.
- Survey and feedback responses support offline capture and later synchronisation.

## Phase 4 — Owner onboarding interaction

Create the first production interaction definition for cleaning-business owners:

- business identity
- company and branch setup
- service catalogue
- operating area
- pricing foundations
- working hours
- payment preferences
- notification channels
- privacy and BYO AI/API settings
- first customer, property and job readiness

Materialise approved answers through governed WorkCore commands only.

### Exit criteria

- The owner can stop and resume safely.
- Validation and approval gates are enforced.
- Repeated submissions are idempotent.
- WorkCore records are created transactionally and audited.

## Phase 5 — Staff onboarding, invitations and training

Add interaction definitions for:

- employee and contractor invitations
- identity and contact capture
- role and permission assignment
- availability
- skills, licences and certifications
- policies and acknowledgements
- induction and training modules
- device registration
- final approval and activation

### Exit criteria

- Invitations are tokenised, expiring and revocable.
- Staff cannot grant themselves elevated permissions.
- Mandatory acknowledgements and training are enforced before activation.

## Phase 6 — AI workforce setup interactions

Create reusable definitions for configuring:

- managers
- assistants
- specialists
- action agents
- delegated permissions
- confidence thresholds
- escalation rules
- provider and model selection
- offline behaviour
- budget and execution limits

### Exit criteria

- AI permissions never exceed the delegating user or tenant policy.
- High-risk actions require explicit approval.
- Every AI-to-WorkCore action has an idempotency key and audit trail.

## Phase 7 — WorkCore materialisation hardening

- Complete command mappings for company, membership, customer, property, service, worker, schedule, job, quote, invoice and settings records.
- Add transaction boundaries and compensating actions.
- Add deterministic replay protection.
- Add command/result correlation IDs.
- Add rollback and failure-recovery tests.

### Exit criteria

- The Interaction Engine never writes operational tables directly.
- Partial materialisation cannot leave silently inconsistent business state.
- All failures produce actionable interaction events.

## Phase 8 — Offline-first runtime and synchronisation

- Persist interaction definitions, runs, answers, approvals and presentation state locally.
- Add encrypted outbox support.
- Add retry, backoff and conflict handling.
- Preserve unsynchronised data indefinitely until resolved or explicitly discarded.
- Prevent credentials and sensitive provider responses from entering service-worker caches.

### Exit criteria

- Core onboarding works without network access.
- Reconnection synchronises deltas rather than replacing whole datasets.
- Conflicts preserve both versions and produce audit events.

## Phase 9 — API, UI and navigation wiring

- Register routes, middleware, policies and controllers.
- Wire the persistent chatbot input into interaction launch and resume actions.
- Add contextual UI surfaces rather than a separate onboarding application.
- Add generative UI cards for questions, approvals, summaries, conflicts and completion.
- Expose Interaction Engine settings through the global gear/settings surface.

### Exit criteria

- Every implemented capability is reachable.
- No orphaned controller, service, migration or UI component remains.
- Mobile, tablet and desktop flows use the same interaction definition.

## Phase 10 — Security and governance review

- Tenant-bound every query and command.
- Validate delegated authority and approval signatures.
- Add rate limits, replay protection and token rotation.
- Review mass assignment, unsafe deserialisation and file-upload paths.
- Verify sensitive fields are encrypted and excluded from logs.
- Add audit anomaly checks.

### Exit criteria

- Cross-tenant access tests pass.
- Privilege escalation tests pass.
- No direct database mutation path bypasses WorkCore.

## Phase 11 — Test matrix and release verification

Run:

- PHP syntax and static analysis
- unit tests
- contract tests
- migration tests
- service-container boot tests
- route and policy tests
- interaction lifecycle tests
- idempotency and rollback tests
- offline and synchronisation tests
- security tests
- archive and checksum verification

### Exit criteria

- All critical tests pass.
- Known limitations are documented.
- A cumulative, reproducible release archive can be generated from the repository.

## Phase 12 — Integration and release

- Produce a reviewed cumulative Interaction Engine release.
- Produce a MagicAI + WorkCore + Interaction Engine integration delta.
- Open a draft pull request into `main` with evidence, migration notes and rollback instructions.
- Merge only after the branch is internally wired and verified.

## Initial branch artefacts

The reconciled cumulative source archive is stored under:

`artifacts/TitanZero-InteractionEngine-FULL-CUMULATIVE-RECONCILED-PASS2-DELTA.zip`

Its checksum is stored beside it as:

`artifacts/TitanZero-InteractionEngine-FULL-CUMULATIVE-RECONCILED-PASS2-DELTA.zip.sha256`

## Current working status

- [x] Repository initialised
- [x] `onboarding` branch created
- [x] Upgrade plan added
- [x] Reconciled source archive selected
- [ ] Archive extracted into working source tree
- [ ] Baseline inventory committed
- [ ] Generic Interaction Engine onboarding capabilities implemented
- [ ] WorkCore materialisation completed
- [ ] Offline and security hardening completed
- [ ] Draft pull request opened
