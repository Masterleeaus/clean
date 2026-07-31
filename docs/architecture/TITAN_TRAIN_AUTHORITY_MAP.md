# Titan Train Authority Map

## Decision

Titan Train is a permanent first-party bounded domain inside the MagicAI + WorkCore host. It is not an installable extension. The Titan Zero Chatbot PWA is the online learner interface, and the Interaction Engine is a guided execution runtime.

## Canonical ownership

| Capability | Canonical authority | Integration rule |
|---|---|---|
| Companies, branches, memberships, workers, roles and permissions | WorkCore | Titan Train stores references only. |
| Programs, modules, lessons and curriculum versions | Titan Train | No donor course tables may remain active. |
| Assignments, progress and completion cycles | Titan Train | All mutations use Titan Train action services. |
| Assessments, attempts, grading and practical review | Titan Train | AI may explain but cannot change pass criteria or grant practical competence. |
| Competencies, certificates and refresher cycles | Titan Train | WorkCore dispatch consumes eligibility decisions without rewriting them. |
| Guided lesson/session state | Interaction Engine | Runtime checkpoints map to Titan Train assignment and attempt public IDs. |
| Conversation, channels, notifications and voice surface | Titan Zero Chatbot PWA / Titan Channels | No LMS package creates a second messaging authority. |
| Documents and evidence binaries | WorkCore Documents | Titan Train owns learning-evidence metadata and references canonical documents. |
| Sensitive credentials and provider keys | Titan Vault | Titan Train stores vault references only. |
| Premises, jobs, dispatch and quality signals | WorkCore | Adapter contracts only; no duplicate operational tables. |
| Audit, events, replay and recovery | WorkCore Audit / Titan Rewind | Every Titan Train mutation emits governed events. |
| AI curriculum authoring | Titan Train publishing workflow | Authoring extensions create drafts only; human publication required. |

## Active-source decisions

- **Port:** Titan Train v0.7.0 cleaner curriculum and learning domain into `app/Domains/TitanTrain`.
- **Integrate:** Interaction Engine under `app/Runtime/InteractionEngine`, with no direct writes to Titan Train tables.
- **Bridge:** existing Titan Zero Chatbot extension to authenticated online Titan Train APIs.
- **Extract adapters:** workforce, documents, quality, premises, jobs, dispatch, audit and voice capabilities.
- **Quarantine:** overlapping inspection/audit implementations until individual services are reconciled.
- **Reject from active merge:** mobile app builders and unrelated publishing systems; the existing Chatbot PWA is the learner client.

## Current baseline

- Host files scanned: **6,902**
- Donor files scanned: **8,128**
- Source disposition entries: **53**
- Existing Titan Train domain in supplied host: **no**
- Existing WorkCore domain in supplied host: **yes**
