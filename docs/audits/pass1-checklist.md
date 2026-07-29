# Pass 1 Checklist

- [x] Required source roots identified.
- [x] 104 extension directories reconciled with the import manifest.
- [x] Baseline audit implemented.
- [x] Source baseline test written red-first and passing locally.
- [x] Composer path repositories verified.
- [x] Missing npm file dependency detected red-first.
- [x] Stale unused `rt-client` dependency removed from `package.json`.
- [x] Idempotent lockfile repair utility added.
- [x] Standalone dependency test passing locally.
- [x] Composer strict validation passing in GitHub Actions.
- [x] PHPUnit architecture tests passing in GitHub Actions.
- [x] npm clean install passing in GitHub Actions.

## Acceptance evidence

GitHub Actions run `30468140105` completed successfully. It verified committed lockfiles, strict Composer metadata, PHP dependency installation, the source baseline audit, both architecture tests and a clean npm installation.
