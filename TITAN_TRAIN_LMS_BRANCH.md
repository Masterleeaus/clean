# Titan Train LMS Working Branch

This branch is the isolated integration workspace for Titan Train.

## Current phase

**Pass 1 complete — source reconciliation and baseline**

Pass 1 outputs are committed under `docs/architecture/` and `docs/merge/`. No donor provider, route, listener, queue, schedule or migration has been activated.

**Next:** Pass 2 — port the minimal online Titan Train domain into the MagicAI + WorkCore host and expose authenticated APIs for the existing Chatbot PWA.

## Branch rules

1. `main` remains the reviewed integration branch.
2. `agent/titan-train-lms` remains isolated until its pass gates are met.
3. The donor ZIP under `source-packs/` is reference material, not executable application code.
4. Donor packages are extracted outside the active Laravel runtime.
5. Do not copy entire donor modules into `app/`.
6. Select one authority for every table, route, provider, model and permission.
7. Keep learning records under `app/Domains/TitanTrain`.
8. Route operational writes through WorkCore services.
9. Do not commit `.env`, credentials, `vendor/`, `node_modules`, caches, logs or generated bundles.
10. Use focused commits for reconciled capabilities.

## Target layout

```text
app/Domains/TitanTrain/
app/Runtime/InteractionEngine/
app/Extensions/TitanTrainAuthoring/
app/Extensions/TitanTrainMedia/
app/Extensions/TitanTrainVoice/
resources/schemas/titan-train-interactions/
resources/views/train/
docs/architecture/
docs/merge/
docs/operations/
source-packs/
```

## Pass 1 outputs

```text
docs/architecture/TITAN_TRAIN_AUTHORITY_MAP.md
docs/architecture/TITAN_TRAIN_SOURCE_DISPOSITION.csv
docs/merge/CLASS_COLLISIONS.csv
docs/merge/ROUTE_COLLISIONS.csv
docs/merge/TABLE_COLLISIONS.csv
docs/merge/PROVIDER_COLLISIONS.csv
docs/merge/CONFIG_COLLISIONS.csv
docs/merge/PASS1_SCAN_SUMMARY.json
docs/merge/PASS1_BASELINE_REPORT.md
docs/merge/PASS1_PHP_LINT.txt
docs/merge/PASS1_SHA256SUMS.txt
PASS1_STATUS.md
```
