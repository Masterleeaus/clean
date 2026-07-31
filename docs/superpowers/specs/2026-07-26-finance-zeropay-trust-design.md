# Finance, ZeroPay and Trust Accounting Design

## Objective

Extend the verified v0.3.0 Titan Zero + Meetup + WorkCore source with a canonical finance boundary covering quotes, invoices, credit notes, expenses, receivables, payment-session orchestration, payment reconciliation and separately permissioned trust accounting.

## Authority map

- **WorkCore Finance** owns quotes, invoices, invoice lines, credit notes, expenses, receivable allocations, accounting periods and journal records.
- **ZeroPay** owns payment sessions, provider/bank observations, payment attempts, matching and reconciliation workflow. It never owns invoice balances or general-ledger truth.
- **Trust Accounting** owns append-only client-money receipts, trust allocations, disbursement requests, approvals, reconciliations and correction entries. It is isolated from ordinary operating cash.
- **Titan Zero** may propose or execute registered tools only through existing permission, confirmation, idempotency, audit and domain-event boundaries.
- **Meetup** presents operations and approval surfaces; it does not own finance records.
- **Titan Vault** stores payment-provider and banking credentials. No gateway secrets enter finance tables or configuration files.

## Domain invariants

1. Money is stored as integer minor units with ISO 4217 currency codes. Default currency is AUD.
2. Invoice totals are derived from immutable line snapshots, discounts and taxes; clients cannot submit authoritative totals.
3. Issued invoices cannot have existing line snapshots edited. Corrections use credit notes, voids or replacement invoices.
4. Payment observations do not reduce an invoice balance until an authorised reconciliation allocation is posted.
5. Provider webhooks are idempotent and stored with payload hashes; raw secrets and unsafe provider errors are never persisted.
6. Trust money cannot be posted to an operating bank account, used for ordinary expenses or transferred without dual-control policy where configured.
7. Trust ledger corrections are reversals plus replacement entries, never destructive updates.
8. General-ledger journals are balanced: total debits equal total credits in one currency and accounting period.
9. Accommodation folio charges remain operational until explicitly converted to an invoice; conversion records lineage.
10. All records are company-scoped. Client-supplied company identifiers are ignored or rejected.

## Components

### Finance module

- Quote and quote-line lifecycle: draft, issued, accepted, rejected, expired, converted.
- Invoice and invoice-line lifecycle: draft, issued, viewed, overdue, partially paid, paid, void, written off.
- Credit notes and credit allocations.
- Expenses and expense approval states.
- Receivable allocations and customer statements.
- Accounting periods, chart accounts, journal entries and immutable journal lines.

### ZeroPay module

- Payment methods: cash, PayID, bank transfer and card-provider handoff.
- Payment sessions referencing invoices or other payable records.
- Provider attempts and webhook observations.
- Bank-deposit observations and deterministic matching candidates.
- Reconciliation allocation that invokes WorkCore Finance rather than changing invoice totals directly.
- No forced provider dependency; adapters are registered behind contracts and fail closed.

### Trust accounting module

- Trust accounts separate from operating accounts.
- Trust matters linked to property, agreement, customer or other WorkCore record.
- Receipts, allocations and disbursement requests.
- Approval and release workflow.
- Trust reconciliation statements and exception records.
- Audit-ready append-only ledger.

## Governed API and AI surface

Registered writes require explicit capabilities and permissions. High-risk operations such as invoice issue, credit allocation, write-off, payment reconciliation, trust receipt reversal and trust disbursement require confirmation. Reads use the existing WorkCore read executor and bounded pagination.

## Donor treatment

Concepts may be informed by EInvoice, Quotes, QuoteEngine, ZeroPayModule and TitanTrust donor archives. Their duplicate routes, tenancy, authentication, provider credentials, direct database writes, global scopes, dashboards and accounting authorities are not imported. Implementation is rebuilt in canonical WorkCore namespaces.

## Testing and release gates

- Dependency-free unit tests for money arithmetic, state transitions, journal balance, matching and trust constraints.
- Structural tests for migrations, company scope, append-only records and provider registration.
- Runtime registration tests for actions, reads, capabilities and finance boundaries.
- Existing Assurance, Maps, AI, namespace and JavaScript gates must remain green.
- Final ZIP is extracted elsewhere and independently retested before release.

## Explicit exclusions

- Live payment-provider credentials or network calls.
- Australian state-specific trust-account regulatory certification.
- Tax advice, BAS preparation or payroll.
- Direct bank settlement execution.
- Production eInvoicing network registration.
