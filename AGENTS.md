# AGENTS.md — Titan Zero Engineering Rules

These rules apply to every automated or human contributor working in this repository.

## Canonical ownership

- Meetup Chat owns communication and UI collaboration.
- Titan Zero owns intent, reasoning, orchestration, memory and delegation.
- WorkCore owns all structured operational records and is the only component permitted to modify operational data.
- ZeroPay observes and reconciles payments through WorkCore Finance; it does not own invoices.
- Titan Vault owns credentials and protected configuration.
- Titan Intelligence owns provider, connector, agent, skill, memory and voice runtime definitions.
- Titan Creative & Marketing owns only creative/campaign lifecycle records.

## Non-negotiable safeguards

1. Never let AI code write operational database tables directly.
2. Resolve company and actor context on the server; never trust request-body tenant identifiers.
3. Require registered capability/action/read definitions for all agent and API execution.
4. Enforce permissions, confirmation, idempotency, audit and domain events for mutations.
5. Store credentials only through Titan Vault references.
6. Keep private files in private storage and expose them only through authorised signed access.
7. Keep donor archives and donor directory trees outside runtime autoload.
8. Do not introduce duplicate users, companies, tenancy, CRM, finance, conversations, settings or storage authorities.
9. Never commit `.env`, secrets, private keys, `vendor`, `node_modules`, caches or writable runtime data.
10. Preserve unsynchronised device data and conflict history.

## Change workflow

1. Read `UPGRADE_PLAN.md` and the relevant domain/provider files before editing.
2. Identify the canonical owner and existing implementation.
3. Write a failing behavioural or structural test before production code.
4. Make the smallest coherent change.
5. Run focused tests, then regression tests for affected domains.
6. Run static namespace, route/provider and migration-order checks.
7. Run `bash bin/titan-verify-connected` before proposing a release.
8. Record donor decisions and architecture changes in the appropriate manifest/docs.

## Source-import branch

The branch `agent/v070-upgrade-base` is prepared from the verified v0.7.0 source archive. The import workflow verifies SHA-256 and ZIP integrity before extracting source. Do not bypass the workflow by uploading an unverified replacement archive.

## Required review questions

Before committing, answer:

- Which bounded domain owns this feature?
- Does this create a second authority?
- Can a request or AI prompt override company context?
- Can the operation be retried safely?
- Is the operation audited and event-producing?
- Are secrets absent from source, URLs, logs and browser bundles?
- Does offline or queued execution preserve history?
- Is the change independently testable?
