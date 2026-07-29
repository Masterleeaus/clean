# Chat-First Job to Payment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [x]`) syntax for tracking.

**Goal:** Build a governed chat command that completes a WorkCore job, issues its invoice, creates a Titan Pay QR collection session, sends it through preferred customer channels, and follows overdue balances.

**Architecture:** Add a deterministic operational intent/router in front of free-form AI chat, a WorkCore completion gateway, an orchestration workflow, Titan Pay QR generation, customer channel preference routing and concrete transport contracts. Preserve Titan Money as financial authority and Titan Pay as payment authority.

**Tech Stack:** PHP 8.2, Laravel 12, Pest 3, Eloquent, queued events, Endroid QR Code 5.1 SVG writer.

## Global Constraints

- Default payment order is PayID, bank transfer, cash, PayPal.
- Stripe is not shown in the default customer flow.
- QR codes point to the Titan Pay collection page.
- Payment completion remains webhook/reconciliation controlled.
- Ambiguous or unauthorised job completion fails closed.
- All actions are company-scoped, idempotent and audited.

---

### Task 1: Operational intent and WorkCore gateway
- [x] Write failing tests for job-completion intent, ambiguity and authorisation.
- [x] Add intent DTO/parser, gateway contract and runtime WorkCore adapter.
- [x] Verify tests and PHP syntax.

### Task 2: Complete-job-and-invoice workflow
- [x] Write failing tests for idempotent completion, billable event ingestion, invoice issue and collection reuse.
- [x] Add workflow result DTO and orchestration service.
- [x] Verify tests and invariants.

### Task 3: QR generation and payment method order
- [x] Write failing tests for method order and QR payload.
- [x] Add Endroid 5.1 dependency, SVG QR service, QR route and collection-session relation.
- [x] Render QR on payment page and chat response.
- [x] Verify tests and syntax.

### Task 4: Chat integration
- [x] Write failing controller/service tests for operational tool execution before LLM fallback.
- [x] Add Titan Zero operational router to AI chat and persist structured result metadata.
- [x] Return invoice, payment URL, QR URL and delivery status.
- [x] Verify tests and tenancy.

### Task 5: Customer channel routing and receipts
- [x] Write failing tests for channel preference and unavailable-provider behaviour.
- [x] Add customer communication preferences, transport contracts, Channels/SMS/email/voice adapters and delivery receipts.
- [x] Change handoff semantics so unconsumed events are not marked delivered.
- [x] Verify tests and retry behaviour.

### Task 6: Receivables follow-up integration
- [x] Write failing tests for preferred-channel fallback, active-session reuse and quiet hours.
- [x] Update follow-up queueing and dispatcher.
- [x] Verify forward-only and stop-state behaviour.

### Task 7: Verification and packaging
- [x] Run PHP lint, regression scripts, drift scans, migration checks and archive reconstruction.
- [x] Build full and v0.4-to-v0.5 delta ZIPs, checksums and verification report.
