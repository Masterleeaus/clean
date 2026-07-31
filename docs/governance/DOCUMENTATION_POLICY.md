# Titan Zero Documentation Policy

## Authority order

When documents disagree, use this order:

1. Current tested source and accepted architecture tests.
2. Current documents listed in `docs/README.md`.
3. Accepted architecture decisions and security/tenancy rules.
4. Current audits and provenance records.
5. Reference-library doctrine and designs.
6. Historical branch plans and status reports.

## Dispositions

Every reviewed document receives one disposition: `canonical`, `merge-into`, `historical`, `superseded-delete`, or `reference-only`.

## Deletion rule

Delete a document only when it is an exact duplicate, contains no unique evidence, or its unique content has been merged into a named canonical document. Git history remains the final recovery record.

## Agent coordination rule

Agents preserve their old branches, but port only unique, verified deltas onto one fresh integration base built from current `main`. Documentation follows the same rule: preserve history, promote only current verified guidance, and never combine every old plan into one unreviewed document.
