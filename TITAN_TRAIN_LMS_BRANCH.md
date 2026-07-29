# Titan Train LMS Working Branch

This branch is the isolated integration workspace for Titan Train.

## Branch purpose

- Merge Titan Train into the current MagicAI + WorkCore host.
- Keep the learner experience accessible through the Titan Zero Chatbot PWA.
- Integrate the Interaction Engine as the guided lesson and assessment runtime.
- Reconcile donor code before enabling any provider, route or migration.

## Current phase

**Pass 1 — source reconciliation and baseline**

The first development work on this branch must produce:

```text
docs/architecture/TITAN_TRAIN_AUTHORITY_MAP.md
docs/architecture/TITAN_TRAIN_SOURCE_DISPOSITION.csv
docs/merge/CLASS_COLLISIONS.csv
docs/merge/ROUTE_COLLISIONS.csv
docs/merge/TABLE_COLLISIONS.csv
docs/merge/PROVIDER_COLLISIONS.csv
docs/merge/CONFIG_COLLISIONS.csv
docs/merge/PASS1_BASELINE_REPORT.md
```

## Source rules

1. `main` remains the reviewed integration branch.
2. `agent/titan-train-lms` remains isolated until its pass gate is met.
3. The donor ZIP under `source-packs/` is reference material, not executable application code.
4. Donor packages must be extracted outside the active Laravel runtime.
5. Do not copy entire donor modules into `app/`.
6. Select one authority for every table, route, provider, model and permission.
7. Keep all learning records under `app/Domains/TitanTrain`.
8. Route operational writes through WorkCore services.
9. Do not add `.env`, credentials, `vendor/`, `node_modules`, caches, logs or generated bundles.
10. Use one focused commit for each reconciled capability.

## Initial target layout

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

## First implementation checkpoint

Before porting code, verify the current host can identify:

- active company and branch;
- current user and company membership;
- WorkCore action registry;
- private document storage;
- Chatbot extension provider and PWA app registry;
- current route, migration and permission namespaces.

No donor provider should be enabled during this checkpoint.