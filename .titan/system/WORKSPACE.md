# Titan Zero Development Workspace

This branch is the isolated working area for the Titan Zero whole-system upgrade.

## Branch

`chatgpt/titan-zero-upgrade-workspace`

## Root plan

See [`TITAN_ZERO_UPGRADE_PLAN.md`](./TITAN_ZERO_UPGRADE_PLAN.md).

## Workspace goals

- Preserve `main` as the stable baseline.
- Keep architecture decisions explicit.
- Track donor-source provenance.
- Separate audits, extracted references and implementation code.
- Avoid parallel domain systems.
- Keep WorkCore as the only operational authority.

## Recommended repository structure

```text
docs/
  adr/
  architecture/
  audits/
  source-inventory/
  upgrade-passes/

workspaces/
  donor-analysis/
  extension-audit/
  pwa-audit/
  workcore-audit/
  interaction-engine-audit/

reference-sources/
  README.md

scripts/
  audit/
  verify/
  workspace/

artifacts/
  manifests/
  reports/
  checksums/
```

`reference-sources/` is for manifests and provenance only. Large third-party archives should not be committed blindly. Upload only files needed for implementation, with licences and origin recorded.

## First implementation sequence

1. Baseline repository inventory.
2. Identify current boot and test commands.
3. Map WorkCore domains and bypasses.
4. Map PWA runtime, local storage and sync.
5. Map Interaction Engine registration and reachability.
6. Classify all extensions.
7. Extract reusable mobile-builder patterns.
8. Implement architecture tests before feature changes.

## Pass documentation

Each pass should create a file under `docs/upgrade-passes/` containing:

- objective
- files inspected
- confirmed defects
- probable defects
- architectural risks
- changes made
- tests run
- test output
- unresolved items
- next pass

## Source admission rules

Before importing donor code, record:

- archive name
- original path
- licence
- framework and version
- dependencies
- selected files
- intended Titan destination
- required refactoring
- security concerns
- tests required

Do not import complete donor applications as nested products.

## Verification commands

Populate `scripts/verify/` with repository-specific commands after the baseline scan. The intended final verification set includes:

- PHP syntax and static analysis
- Composer validation
- Laravel boot and route checks
- migration checks
- architecture tests
- unit and integration tests
- frontend lint/type checks
- PWA build
- service-worker validation
- offline tests
- extension health checks
- end-to-end tests

## Change discipline

- One bounded change per commit where practical.
- No direct commits to `main`.
- No secrets or local databases.
- No automatic deletion of unsynchronised data.
- No AI or UI writes that bypass WorkCore.
- No extension marked complete without a health check and reachable UI/API path.
