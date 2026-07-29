# Titan Train source packs

This directory stores immutable donor archives used for source reconciliation.

## Primary pack

`Titan-Train-LMS-Donor-Core-Pack-v1.1.0.zip`

Expected SHA-256:

```text
77bb614bccd90f8049efb3ef2f9285dd835e67fafd1505b48f8082aac69153d6
```

## Rules

- Archives are reference inputs and must never be loaded by the Laravel runtime.
- Extract archives into an isolated work directory outside `app/`, `packages/` and `public/`.
- Verify the SHA-256 checksum before extraction.
- Do not commit extracted dependency trees, caches, generated bundles or nested archives.
- Record every selected or rejected donor package in the source-disposition ledger.
- Only reconciled, reviewed code may be moved into the active application tree.
