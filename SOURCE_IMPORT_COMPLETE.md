# Titan Zero v0.7.0 Source Import

- Source release: `Titan-Zero-Meetup-WorkCore-Integrated-v0.7.0`
- MiniUp transfer: <https://titan-zero-v070-source-transfer.miniup.app/Titan-Zero-source-v0.7.0.zip>
- SHA-256: `4a64ad4b2d0b141aeb3dd91fe19c618c0caeb2fedcea7820ced8694ea62bf6ed`
- Imported archive files: `1128`
- Target branch: `agent/v070-upgrade-base`
- Bootstrap run: <https://github.com/Masterleeaus/clean/actions/runs/30465837142>

The archive's `.github/workflows/` directory was intentionally not imported: the default `GITHUB_TOKEN` cannot create or update workflow files without an explicit `workflows` permission, and this automation does not request that scope. Files present in the source but not imported: titan-verify.yml. Review and add them manually via a human-authored PR if they are wanted.
