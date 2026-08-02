# Chatbot Issues

## CHAT-001 — Chatbot identity still describes the legacy 14-app shell

### Current State

Both chatbot extension manifests use the same name, version and description. The description explicitly advertises a template-aware 14-app shell.

### Required Changes

Update the authoritative extension manifest after the five-application registry is implemented. The description should identify the chatbot as the shared conversational interface for Titan Zero, Titan Go, Titan Launch, Titan Desk and Titan Hub. The compatibility tree must not retain an independently installable identical manifest.

### Why

Extension metadata controls discovery, installation, documentation and support expectations. It currently describes an architecture that is being retired.

### Risk

Medium. Changing the extension identifier rather than only its presentation metadata could break marketplace registration or upgrade paths.

### Priority

High

### Dependencies

Authoritative extension decision, marketplace discovery and upgrade scripts.

### Estimated Work

Small

### Completion Status

Pending

---

## CHAT-002 — Current template slug is acting as application context

### Current State

The frontend resolves its shell from `titan_template` or `template_slug`. The operational workspace and client events use this template slug as the application identifier.

### Required Changes

Persist and resolve `active_application` separately from `vertical_template`. Provide a compatibility resolver that maps legacy template/app slugs to one of the five applications while retaining vertical configuration independently.

### Why

A cleaning template and Titan Go are different concepts. Treating them as one value prevents application-aware AI, permissions and workflow behaviour from remaining stable across verticals.

### Risk

High. Existing chatbot records and builder payloads may only contain the legacy template field.

### Priority

Critical

### Dependencies

Application registry, chatbot model/config persistence, builder, frontend shell and migration mapping.

### Estimated Work

Medium

### Completion Status

Pending

---

## CHAT-003 — Public chatbot controller remains a large direct execution surface

### Current State

`ChatbotApplicationController` directly coordinates conversations, customers, history, uploads, support handoff, reviews, exports and generator behaviour. No clear adapter into the canonical Interaction Engine was evidenced inside the scoped extension.

### Required Changes

Keep read-only/resource endpoints where appropriate, but route conversational workflow execution through a dedicated application/Interaction Engine service boundary. Avoid moving all existing controller logic at once; extract orchestration incrementally behind existing routes.

### Why

Application context, approvals, audit and WorkCore authority need one consistent execution path rather than controller-specific behaviour.

### Risk

High. This controller is externally consumed and should not be rewritten wholesale.

### Priority

High

### Dependencies

Interaction Engine public contract, AI runtime, conversation service, WorkCore APIs and existing API tests.

### Estimated Work

Large

### Completion Status

Pending

---

## CHAT-004 — Communication identities can be mistaken for WorkCore customers

### Current State

The extension owns `ext_chatbot_customers` containing names, email addresses, phone numbers, session IDs and channel payloads. The offline sync registry also exposes `customer` as a synchronised chatbot entity.

### Required Changes

Rename the conceptual boundary in code and documentation to communication/contact-session identity, or explicitly mark it as non-authoritative. Add a WorkCore link/promotion service that creates or associates authoritative customers through WorkCore APIs with idempotency and audit data. Do not directly merge the table into WorkCore without a migration strategy.

### Why

Titan Desk may capture intake data but must never create a second authoritative customer database.

### Risk

Critical. Existing conversations and channels depend on this table, so it cannot simply be deleted.

### Priority

Critical

### Dependencies

WorkCore CRM API, intake workflows, sync registry, conversation models and identity matching rules.

### Estimated Work

Large

### Completion Status

Pending
