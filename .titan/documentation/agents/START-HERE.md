# Titan Agent OS — Worker Agent Start Here

This is the mandatory Agent OS handbook for ChatGPT and other implementation agents.

## Before changing anything

1. Read the root `README.md` and `AGENTS.md`.
2. Read `/docs/README.md` and every canonical document relevant to the task.
3. Read `/.titan/README.md` and the current status file.
4. Confirm the repository, branch, base SHA and task scope.
5. Compare the request with current source, tests, routes, providers, migrations, registries and runtime wiring.
6. Work only from an approved isolated branch based on `integration/current-main-reconciliation`.
7. Preserve old branches as evidence; port only unique, verified deltas.

## Agent authority

Worker agents implement approved plans. They do not redefine bounded-context authority, weaken governance or invent production status. When a plan is unsafe, contradictory, blocked or architecturally incomplete, stop and escalate to the control plane.

## Non-negotiable boundaries

- MagicAI host owns authentication and platform membership lifecycle.
- WorkCore alone owns operational business records and mutations.
- Titan Zero owns planning and orchestration.
- Interaction Engine owns clarification, evidence, approvals and governed command preparation.
- Chatbot/PWA owns conversation and device/offline experience, not server operational truth.
- Titan Vault owns credentials and protected configuration.
- No direct operational model writes from AI, PWA, extension or integration layers.

## Required work record

Every task must record:

- task and agent identity;
- branch and source baseline;
- files changed;
- assumptions and evidence;
- validators and tests run;
- tests not run;
- remaining risks;
- decisions and escalations;
- documentation updated;
- reusable lesson or failure pattern.

## Documentation duties

Update `/docs` when the change affects long-form project architecture, governance, plans, audits, provenance, setup or historical truth. Update `/.titan/documentation` when the change affects agent onboarding, current status, decisions, reviews, lessons, generated views or Chronicle context.

Do not create a duplicate manual authority in both trees. Link or derive one view from the canonical source.

## Completion rule

A task is not complete merely because files changed. It is complete only when the intended behaviour is reachable, relevant checks have run, governance boundaries remain intact, documentation is updated and unverified claims are clearly labelled.
