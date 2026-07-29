# Titan Train Interaction Engine Reconciliation Plan

Baseline: `e565d7594e062c6705be9747bee0bd6081beb137`

Target branch: `reconcile/interaction-engine`

Target PR base: `integration/current-main-reconciliation`

## Purpose

Connect the existing Titan Train domain to the canonical package under `packages/titanzero/interaction-engine` without transferring ownership of assignments, progress, attempts, competencies, certificates or operational records.

## Authority split

- Titan Train owns programs, assignments, lesson completion, attempts, competencies and certificates.
- Interaction Engine owns definitions, sessions, transitions, clarification, confidence and approval state.
- WorkCore owns workers, properties, jobs, dispatch, permissions, audit and operational events.
- Titan Channels owns conversations and training-channel messages.
- Chatbot PWA renders the guided flow and remains online-only for Titan Train.

## Current-main findings

- Titan Train core and the native Chatbot learner workspace are already present.
- The canonical Interaction Engine package exists at `packages/titanzero/interaction-engine`.
- Current main uses staged provider activation through `TitanZeroServiceProvider` and feature flags.
- Old direct provider registration must not be restored.

## Reconciliation increments

### Increment 1 — Definition catalog

Add domain-local, immutable definitions for:

1. Guided lesson
2. Knowledge assessment
3. Practical observation
4. Property induction

Definitions declare authority, version, required context, steps, confirmation rules and allowed completion commands. They do not execute writes.

### Increment 2 — Contract tests

Add tests proving:

- Every definition names Titan Train as learning authority.
- Operational actions are represented only by registered WorkCore action keys.
- Practical qualification requires a trainer-confirmed step.
- Definitions contain no arbitrary PHP, JavaScript, SQL, class callbacks or raw table names.
- Titan Train remains online-only.

### Increment 3 — Canonical engine adapter

Create a small adapter that compiles the Titan Train catalog through the existing Interaction Engine definition/compiler contract. Do not create a second registry or runtime.

### Increment 4 — Session correlation

Link Interaction Engine session public IDs to Titan Train assignment, lesson, assessment attempt or property-induction context without moving learning records into engine tables.

### Increment 5 — API and PWA projection

Expose only the current interaction session and safe view model through Titan Train APIs. The PWA renders the compiled definition and submits transitions to the server.

### Increment 6 — Activation

Before editing staged providers or global registries, submit:

```text
LOCK REQUEST
File: provider/feature configuration or canonical interaction registry
Reason: activate Titan Train definition contribution
Expected changes: register one adapter/contributor behind the existing Interaction Engine feature flag
Dependent branches: reconcile/provider-gating, reconcile/product-shell
Expected release time: coordinator-controlled
```

### Increment 7 — Verification

Run where available:

- PHP syntax checks
- Package tests
- Titan Train unit and feature tests
- Laravel boot and route list
- Tenant-isolation and permission tests
- Direct operational-write scan
- Definition injection/adversarial tests
- PWA connected-flow tests

Unavailable checks must remain recorded as not run.

## Deliberately rejected

- Old branch merge or cherry-pick.
- Duplicate Interaction Engine provider.
- Direct `config/app.php` registration.
- New LMS offline database or outbox.
- Raw WorkCore model/table writes.
- Executable AI-generated PHP or JavaScript definitions.
- Donor archives or generated dependency trees.
