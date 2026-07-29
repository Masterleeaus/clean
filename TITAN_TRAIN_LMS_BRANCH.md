# Titan Train LMS Working Branch

This branch is the isolated integration workspace for Titan Train.

## Current phase

**Pass 3 complete — native Chatbot PWA learner workspace**

Titan Train is now the fifteenth native app in the Chatbot extension. The learner can open Learn, Practice, Skills and Me, use the persistent Titan chat bar, complete lessons through governed online APIs, start assessments and enter managed training channels.

**Next:** Pass 4 — integrate the Interaction Engine as the guided lesson and assessment runtime.

## Branch rules

1. `agent/import-all-extensions` is the current full-source integration base.
2. `agent/titan-train-lms` remains isolated until its pass gates are met.
3. The donor ZIP under `source-packs/` is reference material, not executable application code.
4. Donor packages are extracted outside the active Laravel runtime.
5. Do not copy entire donor modules into `app/`.
6. Select one authority for every table, route, provider, model and permission.
7. Keep learning records under `app/Domains/TitanTrain`.
8. Route operational writes through WorkCore services.
9. Keep Titan Train online-only until an explicit later architecture decision changes that scope.
10. Do not commit `.env`, credentials, `vendor/`, `node_modules`, caches, logs or generated bundles.
11. Use focused commits for reconciled capabilities.

## Target layout

```text
app/Domains/TitanTrain/
app/Runtime/InteractionEngine/
app/Extensions/TitanTrainAuthoring/
app/Extensions/TitanTrainMedia/
app/Extensions/TitanTrainVoice/
app/Extensions/Chatbot/resources/titan-apps/
app/Extensions/Chatbot/resources/pwa/chatbot-pwa/apps/
docs/architecture/
docs/merge/
docs/operations/
source-packs/
```

## Completed passes

- Pass 1 — source reconciliation and authority mapping.
- Pass 2 — online Titan Train domain, database, API and initial PWA bridge.
- Pass 3 — native Chatbot PWA app, learner workspace and channel handoff.
