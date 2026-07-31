# Finance, ZeroPay and Trust Accounting Delta

## Purpose

This delta adds commercial finance, provider-neutral payment reconciliation and segregated trust accounting to the canonical WorkCore runtime without transferring accounting authority to payment providers or donor modules.

## Canonical authority

### WorkCore Finance

WorkCore Finance owns:

- quotes and quote lifecycle
- invoices and receivable balances
- credit notes and credit allocations
- expenses and approval state
- chart-of-account records
- accounting periods
- balanced, immutable journal entries

Amounts are stored as integer minor units. The default currency is Australian dollars (`AUD`).

### ZeroPay

ZeroPay owns payment orchestration only:

- payment sessions and attempts
- provider and bank observations
- candidate invoice matches
- reviewed reconciliation
- provider usage and safe failure state

ZeroPay never updates invoice tables directly. Reconciliation requests a governed receivable allocation through `FinanceRepositoryContract`.

### WorkCore Trust Accounting

Trust accounting owns separately permissioned client-money records:

- trust accounts and matters
- receipts and matter allocations
- disbursement requests
- independent approvals
- released disbursements
- append-only trust ledger corrections
- reconciliation records

Trust accounts cannot be operating accounts. A requester cannot approve their own disbursement. Releases require the configured approval count and sufficient matter funds.

## Integrated records

The migration `2026_07_26_040000_create_tz_finance_payment_trust_tables.php` creates 28 company-scoped tables across commercial finance, payment orchestration and trust accounting.

All money columns use integer minor units. Immutable allocations, journals and trust-ledger entries use linked reversal records rather than destructive updates.

## Governed actions

### Finance

- create and transition quote
- create, issue and transition invoice
- create credit note and allocate credit
- record and approve expense
- allocate receivable
- create account
- create and close accounting period
- post balanced journal

### ZeroPay

- create payment session
- record payment attempt
- record provider or bank observation
- propose candidate matches
- reconcile observation to invoice

### Trust accounting

- create trust account and matter
- record and allocate receipt
- request, approve and release disbursement
- reverse trust-ledger entry

All writes pass through WorkCore confirmation, permission, entitlement, idempotency, audit and domain-event handling.

## Accepted donor concepts

- provider-neutral payment sessions
- PayID, bank-transfer, cash and provider-card observation concepts
- safe event deduplication and matching
- invoice and receivable lifecycle concepts
- append-only client-money ledgers
- dual-control disbursement approval

## Rejected duplicate authorities

The following donor concepts were not imported as runtime authorities:

- donor company, user, role or tenant models
- direct payment-provider HTTP clients
- plaintext or module-local payment credentials
- provider-owned invoice state
- payment capture that bypasses WorkCore
- arbitrary AI journal posting
- mutable trust balances
- standalone finance routes, dashboards or audit stores
- donor-local property, customer or accommodation identities

## Deliberate boundary

Live payment execution is not claimed in this source checkpoint. Provider credentials remain Titan Vault references, and provider adapters require separate licensed integrations and connected-environment testing.

Jurisdiction-specific Australian trust-account, rent, bond, owner-statement and statutory reporting rules remain a regulated implementation layer that must be validated independently before production use.
