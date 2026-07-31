# Titan Train LMS — Pass 1 Baseline Report

## Scope

Pass 1 performs source reconciliation only. No donor provider, route, listener, schedule or migration is enabled by this pass.

## Inputs

- Repository branch: `agent/titan-train-lms`
- Host baseline: `MagicAI-v10.91-WORKCORE-MERGED.zip`
- Donor source pack: `source-packs/Titan-Train-LMS-Donor-Core-Pack-v1.1.0.zip`
- Donor SHA-256: `77bb614bccd90f8049efb3ef2f9285dd835e67fafd1505b48f8082aac69153d6`

## Scan results

| Metric | Result |
|---|---:|
| Host files | 6,902 |
| Host PHP files | 4,676 |
| Donor files | 8,128 |
| Donor PHP files | 6,696 |
| Disposition entries | 53 |
| Repeated table-creation targets | 554 |
| Repeated explicit route names | 606 |
| Repeated fully-qualified PHP symbols | 1277 |
| Repeated service-provider symbols | 29 |
| Repeated config namespaces or environment keys | 595 |

## Critical decisions

1. Titan Train becomes permanent first-party code under `app/Domains/TitanTrain`.
2. The online Chatbot PWA is the only learner client required for the current scope.
3. Offline databases, device queues, sync cursors and conflict stores are excluded.
4. Interaction Engine is deferred until the canonical Train action/query APIs exist.
5. QualityControl is the general quality authority; CleanQuality contributes cleaning-specific rubrics only.
6. WorkCore Documents is the binary/document authority; TitanDocs, TitanVault and JobAssist become adapters.
7. WorkCore remains the authority for worker, premises, job and dispatch records.
8. Optional authoring, media and voice features remain disabled-by-default extensions.

## Collision handling policy

- A donor migration is never copied until its table target is compared with host and Titan Train schema.
- A duplicate route name is renamed or removed before provider registration.
- Duplicate PHP symbols are resolved by selecting one canonical implementation; no classmap precedence hacks.
- Donor service providers remain disabled until their contracts, permissions and migrations are reconciled.
- Direct `env()` access is moved into host configuration before active integration.

## Verification status

- Static source extraction: passed.
- PHP syntax baseline: 5,252 host/canonical/runtime PHP files checked; 0 parse errors.
- Collision ledgers: generated.
- Package disposition ledger: generated.
- Host checkpoints: WorkCore provider, action registry, company-context services and Chatbot provider were identified. The repository branch also contains `app/Extensions/TitanZeroChatbot/extension.json`; the older local host archive predates that path.
- Runtime Laravel boot: blocked because the supplied source excludes `vendor/autoload.php`.
- Database migrations: not executed in Pass 1.
- Donor runtime activation: intentionally not performed.

## Pass 2 entry gate

Pass 2 may begin when these files are reviewed and committed:

- `docs/architecture/TITAN_TRAIN_AUTHORITY_MAP.md`
- `docs/architecture/TITAN_TRAIN_SOURCE_DISPOSITION.csv`
- `docs/merge/TABLE_COLLISIONS.csv`
- `docs/merge/ROUTE_COLLISIONS.csv`
- `docs/merge/CLASS_COLLISIONS.csv`
- `docs/merge/PROVIDER_COLLISIONS.csv`
- `docs/merge/CONFIG_COLLISIONS.csv`
- `docs/merge/PASS1_SCAN_SUMMARY.json`

Pass 2 will port the minimal online Titan Train core into the host and add the authenticated API boundary used by the Chatbot PWA.
