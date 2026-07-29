# Documentation Reconciliation PR Scope

## Baseline

- Repository: `Masterleeaus/clean`
- Base branch: `main`
- Base SHA: `e565d7594e062c6705be9747bee0bd6081beb137`
- Working branch: `agent/documentation-reconciliation`

## Completed in Pass 1

- Safety-checked and extracted `docs/Archive.zip` and `docs/Archive 2.zip`.
- Catalogued the existing repository documentation and 296 extracted documents.
- Moved the extracted collections into `docs/reference/titan-library/`.
- Removed both ZIP files after extraction hashes and member inventories were committed.
- Removed six exact SHA-256 duplicate documents while preserving one copy in the most appropriate subject folder.
- Archived 29 branch-era, v10.91-specific, status, setup and import documents with historical banners.
- Promoted one current upgrade plan.
- Created a root documentation index, governance policy, reconciliation status and disposition register.
- Moved current Titan Money/Titan Pay provenance into `docs/provenance/`.

## Deliberately not completed yet

- No non-identical doctrine or architecture document was deleted.
- No PDF or DOCX was declared canonical solely from its filename.
- No runtime, route, provider, migration or application code was changed.
- No internal link rewrite has been attempted beyond the new top-level index.
- Runtime documentation duplicated inside Chatbot extension directories remains a separate review pass.

## Next review pass

1. Cluster doctrine and architecture documents by subject.
2. Compare each cluster with current code and accepted authority boundaries.
3. Promote canonical Markdown documents.
4. Merge unique content before deleting non-identical superseded documents.
5. Repair internal links and create architecture-decision records where required.
