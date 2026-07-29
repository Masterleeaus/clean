> [!IMPORTANT]
> **Historical record — not current implementation guidance.** This document is retained for provenance because it describes an earlier branch, source version, import, or completed upgrade pass. Use `docs/README.md` and `docs/plans/CURRENT_UPGRADE_PLAN.md` for current guidance.

# Titan Train LMS — Pass 2 Status

**Branch:** `agent/titan-train-lms`  
**Pass:** 2 of 10 — online Titan Train core  
**Status:** source integrated; runtime boot requires installed Composer dependencies

## Added

- Permanent `app/Domains/TitanTrain` bounded domain.
- Company-scoped `tt_*` schema for programs, lessons, assignments, assessments, attempts, competencies, certificates and channel links.
- Cleaner Foundation blueprint with 9 modules, 26 lessons, 2 assessments and 5 competencies.
- Idempotent company installation and recurring assignment cycles.
- Learner progress, knowledge assessment, practical sign-off, qualification and readiness services.
- WorkCore action registrations for assignments, readiness, lesson completion and cleaner assignment.
- Authenticated online API under `/api/v1/titan-train`.
- Chatbot PWA manifest, bridge client and template schema.
- API, PWA and database documentation.

## Scope boundary

This pass is server-authoritative and online-only. It does not add IndexedDB, SQLite, sync queues, device cursors, offline evidence or conflict resolution.

## Next

Pass 3 will finish the native Chatbot PWA learner workspace, channel presentation and app-registry integration against the real Chatbot extension source.
