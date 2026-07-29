# Chat-First Job Completion to Payment Design

## Goal

Allow an authorised user to tell Titan Zero that a job is complete and have the system safely complete the operational action, create and issue the invoice, create a Titan Pay collection session and QR code, return the result in chat, deliver it through preferred customer channels, and follow up overdue balances.

## Architecture

1. `TitanOperationalToolRouter` recognises deterministic high-confidence job-completion commands before general LLM chat.
2. `WorkCoreJobCompletionGateway` is an interface. The default database implementation discovers compatible WorkCore job tables/models at runtime and fails closed when WorkCore is absent or the job is ambiguous.
3. `CompleteJobAndInvoiceWorkflow` applies company and actor context, validates authority, completes the job idempotently, ingests a Titan Money billable event, runs the invoice agent, creates/reuses a Titan Pay collection session, generates an SVG QR record, and queues delivery.
4. Titan Pay customer payment order is `payid`, `bank_transfer`, `cash`, `paypal`; Stripe remains opt-in but not in the default customer flow.
5. Customer delivery routing prefers customer app/Titan Channels, then SMS, then email, then optional voice. A route is only marked delivered after a concrete transport listener/provider returns a receipt; unavailable transports remain queued/retryable and create an internal exception.
6. Receivables follow-up reuses the active collection session and QR, honours customer preferences, quiet hours, consent, dispute/hold states, and forward-only reminder stages.

## Safety

- No chat message directly writes finance or payment tables.
- Job completion, invoice creation and collection session creation use idempotency keys tied to company, job and job version.
- Ambiguous jobs produce a clarification response and no mutation.
- Amount or variation uncertainty produces approval rather than issue.
- QR payload points to the Titan Pay collection page, never directly to PayPal.
- Browser returns never confirm payment.
- SMS/voice delivery requires explicit provider configuration and consent.
- Every action records conversation, user/agent, company, correlation and source identifiers.

## Testing

Regression tests cover intent matching, ambiguity, idempotency, authority, method ordering, QR generation, channel preference, delivery receipt state, and overdue follow-up fallback.
