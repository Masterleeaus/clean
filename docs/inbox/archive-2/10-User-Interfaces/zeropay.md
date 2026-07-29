# ZeroPay — Financial Engine

**Node type:** Finance · Billing · Revenue  
**Primary users:** Owner, Accounts, Manager

---

## Purpose

Invoicing and payments without transaction lock-in. ZeroPay is the financial surface of Titan BOS.

> Zero surprise bills. Zero gateway dependency.

---

## Interface Type

Progressive Web App  
Desktop · Mobile supported

---

## Core Responsibilities

- Invoice generation and dispatch
- QR code payment links
- Expense tracking
- Contract profitability analysis
- Payment reconciliation
- Cashflow reporting
- BAS / tax reporting (Australian compliance)

---

## Payment Philosophy

ZeroPay supports BYO payment providers. No platform transaction fees.

**Supported payment methods:**
- PayID (Australian instant bank transfer)
- Stripe
- Square
- Direct bank transfer
- BPAY
- Cash/cheque (logged manually)

---

## Financial Workflow

```
Booking completed
→ EInvoice module generates invoice (PEPPOL/UBL or standard)
→ Accountings module posts to accounts receivable
→ ZeroPay surface displays invoice with payment link
→ Client pays via Zero Fuss or payment link
→ ZeroPay records payment
→ Accountings reconciles
→ Xero/MYOB synced (via TitanIntegrations)
```

---

## AI Integration (Titan Zero)

In ZeroPay context, Titan Zero provides:
- Cashflow forecasting and warnings
- Late payment reminder drafts
- Margin analysis per client/contract
- Overdue invoice escalation suggestions
- BAS preparation summaries

---

## Vertical Adaptation Layer

| Vertical | ZeroPay adaptation |
|---|---|
| Commercial | Contract-based invoicing, progress claims, SLA credits |
| Construction | Progress payment schedule, retention amounts |
| NDIS | Funding code billing, NDIS portal integration |
| Bond | Deposit + completion billing model |
| Airbnb | Per-turnover billing to host account |

---

## Node Relationships

```
ZeroPay
├── reads: Accountings (invoices, payments, expenses)
├── reads: EInvoice (e-invoice status and compliance)
├── reads: BookingModule (completed jobs for invoicing)
├── writes: Accountings (payment records, reconciliation)
├── syncs: TitanIntegrations (Xero, MYOB, Stripe, Square)
├── delivers: TitanReach (payment links, reminders via SMS/email)
└── AI: Titan Zero (cashflow analysis, late payment handling)
```
