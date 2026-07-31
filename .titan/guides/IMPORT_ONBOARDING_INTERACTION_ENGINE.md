# Onboarding Interaction Engine import runbook

The verified source archive is imported by the repository workflow:

`.github/workflows/bootstrap-onboarding-interaction-engine.yml`

## Trigger

Open an issue with the exact title:

`BOOTSTRAP ONBOARDING INTERACTION ENGINE`

## Import controls

The workflow:

1. Checks out the `onboarding` branch.
2. Downloads the MiniUp-hosted archive.
3. Verifies SHA-256 and ZIP integrity.
4. Confirms the archive contains exactly 455 files.
5. Rejects vendored dependencies, `.env` files and private-key material.
6. Preserves `UPGRADE_PLAN.md`, `artifacts/README.md` and repository workflows.
7. Imports the real source tree.
8. Runs PHP syntax and JSON parsing checks.
9. Commits the imported source as one auditable commit.
10. Closes the trigger issue when successful.
