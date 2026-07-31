# Onboarding branch status

## Current state

- Branch: `onboarding`
- Upgrade plan: present
- Verified archive host: present
- Import workflow: installed on `main`
- Import runbook: present
- Trigger issues: `#15` and retry `#16`
- Source import: blocked because GitHub Actions did not start either issue-event workflow

## Verified source

- Archive: `TitanZero-InteractionEngine-FULL-CUMULATIVE-RECONCILED-PASS2-DELTA.zip`
- Files: `455`
- Size: `582951` bytes
- SHA-256: `fe89e2937b72b0d0d387ffda8fc2afed08e6af85930a0c322ac59ffa18b3650f`
- MiniUp host: <https://titan-zero-onboarding-artifacts.miniup.app>

## Import controls already installed

The repository workflow `.github/workflows/bootstrap-onboarding-interaction-engine.yml` will:

1. Check out `onboarding`.
2. Download and checksum-verify the archive.
3. Confirm ZIP integrity and the exact 455-file count.
4. Reject vendored dependencies, environment files and private keys.
5. Preserve branch governance files.
6. Import the real source tree.
7. Run PHP syntax and JSON parsing checks.
8. Commit the source as one auditable commit.

## Required unblock

Enable GitHub Actions for the repository or manually run the workflow after Actions becomes available. The importer and trigger are already committed; do not create another parallel import mechanism.

After the import commit appears, begin Phase 1 inventory and architecture reconciliation.
