# Titan Train LMS — Multi-Pass Upgrade Plan

## Mission

Build Titan Train as the permanent learning and competency domain inside the MagicAI + WorkCore host, with the Titan Zero Chatbot PWA as the learner-facing online interface.

The LMS must feel like a guided work coach rather than a conventional course catalogue. WorkCore remains authoritative for companies, workers, premises, jobs, documents, compliance, dispatch and audit. Titan Train owns programs, lessons, assignments, progress, assessments, competencies, certificates, live training and learning evidence. The Interaction Engine controls guided execution but never becomes the learning-record authority.

## Branch

Development branch: `agent/titan-train-lms`

Base branch: `main`

Primary donor pack: `source-packs/Titan-Train-LMS-Donor-Core-Pack-v1.1.0.zip`

## Non-negotiable rules

1. Preserve the existing MagicAI + WorkCore application architecture.
2. Keep Titan Train under `app/Domains/TitanTrain` as permanent first-party code.
3. Keep WorkCore as the only authority for structured operational records.
4. Do not flatten donor routes, providers, migrations or models into the host.
5. Every learning record must be company scoped; branch scope is applied where relevant.
6. AI may draft and explain training but cannot publish, grade practical competence or issue qualifications.
7. Trainer approval is required for practical competency grants.
8. Dispatch may consume Titan Train eligibility decisions but cannot rewrite them.
9. Sensitive evidence must use private company-scoped storage.
10. No state-changing GET routes or runtime extension ZIP extraction.
11. The first integrated release is online-only but must be accessible from the Chatbot PWA.
12. Each pass requires tests, a change manifest, database patch, verification report and cumulative/delta package.

---

# Pass 1 — Source reconciliation and baseline

- Inventory the donor pack against the current repository.
- Generate class, route, table, migration, provider, permission and configuration collision reports.
- Select one canonical implementation for learning, documents, quality, onboarding, voice and evidence.
- Mark every donor package as `keep`, `extract`, `adapt`, `quarantine` or `reject`.
- Confirm the current MagicAI, WorkCore and Chatbot runtime baseline.

**Gate:** no donor provider enabled; every collision has a chosen authority.

# Pass 2 — Titan Train core port

- Add the permanent `app/Domains/TitanTrain` bounded domain.
- Port programs, modules, lessons, assignments, progress, assessments, competencies, certificates, live sessions and evidence.
- Preserve the cleaner-foundation blueprint: 9 modules, 26 lessons, 2 assessments, 5 competencies and one certificate.
- Add WorkCore company, workforce, documents and audit adapters.
- Add governed action/query registries and immutable domain events.

**Gate:** cleaner foundation installs idempotently and all records are tenant scoped.

# Pass 3 — Online Chatbot PWA access

- Register Titan Train as a first-class Chatbot PWA app.
- Provide Learn, Practice, Skills and Me views.
- Add authenticated online APIs for bootstrap, assignments, lessons, assessments and readiness.
- Register governed chatbot actions such as `titan_train.assignments.list`, `titan_train.lesson.complete` and `titan_train.readiness.get`.
- Add managed learner, cohort, live-session and eligibility channels.

**Gate:** a cleaner can complete the foundation journey from the PWA while online.

# Pass 4 — Interaction Engine integration

- Install the canonical Interaction Engine runtime under `app/Runtime/InteractionEngine`.
- Define versioned lesson, induction, assessment and practical-observation schemas.
- Link runtime sessions to Titan Train assignments and attempts.
- Route all mutations through Titan Train actions.
- Add resumable checkpoints and deterministic no-AI fallback.

**Gate:** Interaction Engine owns session flow; Titan Train owns outcomes.

# Pass 5 — Trainer Studio and AI authoring

- Adapt File Chat, Deep Research, Canvas, Folders and Content Manager behind a `TitanTrainAuthoring` adapter.
- Store sources through WorkCore Documents.
- Add curriculum drafts, citations, review, approval and immutable publication versions.
- Add question banks, learning outcomes, difficulty and rubrics.
- Block prompt injection and untrusted-document tool execution.

**Gate:** AI output remains draft until an authorised trainer publishes it.

# Pass 6 — Practical assessment and evidence

- Add trainer observation flows, rubrics and sign-off.
- Add private photo, video, audio, signature and document evidence.
- Link evidence to workers, assignments, properties and jobs without duplicating file authority.
- Add reassessment and remedial-training workflows.

**Gate:** practical competence cannot be granted by AI or quiz completion alone.

# Pass 7 — Workforce, compliance and dispatch

- Auto-assign cleaner training from worker onboarding.
- Link licences and external credentials through WorkCore Compliance.
- Add property- and equipment-specific inductions.
- Enforce competency requirements before dispatch.
- Preserve hard blocks, advisory warnings and authorised overrides.
- Add expiry processing and recurring refresher cycles.

**Gate:** dispatch cannot bypass a hard Titan Train competency requirement.

# Pass 8 — Media, voice and accessibility

- Add governed presentation, image, annotation, video, caption, narration and voice adapters.
- Store originals and derivatives through WorkCore Documents.
- Require captions, transcripts and accessible alternatives.
- Add multilingual lesson variants linked to the canonical source version.

**Gate:** no learning material exists only inside a provider extension.

# Pass 9 — LMS standards and analytics

- Add gradebooks, transcripts, CPD hours, RPL, moderation and appeals.
- Add quarantined SCORM import and controlled conversion.
- Add xAPI statements and a tenant-scoped learning-record store.
- Add question analytics and training-effectiveness reporting tied to quality, rework, incidents and complaints.

**Gate:** analytics and recommendations cannot directly alter grades, competencies or eligibility.

# Pass 10 — Runtime and deployment release candidate

- Install exact Composer and frontend dependencies in a deployment-capable environment.
- Run clean and upgrade migrations against supported databases.
- Run PHP, JavaScript, PWA, browser, tenancy, upload, security and permission tests.
- Verify queues, schedules, broadcasts, notifications and private files.
- Produce backup, rollback, disaster-recovery and deployment runbooks.
- Build the final cumulative application and incremental upgrade package.

**Gate:** no unresolved critical/high defects and the cleaner journey passes invitation through job eligibility.

---

## Required release artefacts per pass

```text
Titan-Train-Suite-PASS{N}-CUMULATIVE.zip
Titan-Train-Suite-PASS{N}-DELTA.zip
database_titan_train_pass{N}.sql
titan_train_pass{N}_incremental.sql
PASS{N}_VERIFICATION.md
PASS{N}_APP_DIRECTORY.txt
PASS{N}_SHA256SUMS.txt
PASS{N}_CHANGE_MANIFEST.json
```

## Completion definition

Titan Train is complete when a cleaner can receive an invitation, enter a managed training channel, complete guided lessons, ask Titan Zero for approved help, submit practical evidence, pass knowledge and supervised practical assessments, receive competencies and a certificate, complete property-specific induction, and be accepted or blocked by dispatch using immutable eligibility evidence.