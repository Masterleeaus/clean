# Titan Pro — Owner Command Centre

**Node type:** Strategy · Control · Analytics  
**Primary users:** Owner, Operator, Manager, Multi-site Administrator

---

## Purpose

Central control panel providing complete operational visibility across the business. Titan Pro is the primary Filament admin surface — every backend module exposes its Filament resources here.

---

## Interface Type

Full-screen multi-page application  
Desktop-first · Tablet supported  
Powered by Filament Admin Panel

---

## Core Responsibilities

- Business overview (revenue, jobs, staff, clients)
- Financial status (invoices, payments, cashflow)
- Team activity (who's where, doing what)
- Client pipeline (quotes, bookings, renewals)
- Job performance (quality scores, complaints, re-cleans)
- Automation monitoring (workflow health, signal log)
- AI insights (Titan Zero recommendations)

---

## Primary Dashboard Sections

| Section | Source Module |
|---|---|
| Command Dashboard | All modules (aggregate) |
| Jobs Overview | BookingModule |
| Clients | BookingModule, ClientFeedback |
| Sites / Locations | ManagedPremises |
| Staff / Crews | Payroll, CleanerRecruit, Performance |
| Scheduling | BookingModule, Ground Zero |
| Quotes | BookingModule, TitanDocs |
| Invoices | Accountings, EInvoice |
| Payments | ZeroPay, Accountings |
| Compliance | CleanQuality, CleanerRecruit |
| Reports | Performance, Accountings, CleanQuality |
| Supply Chain | SupplyChain |
| Automation | TitanZero, Webhooks |

---

## Example Command Cards

- Revenue today / this week / this month
- Jobs in progress · completed · missed
- Late arrivals
- Open quotes
- Outstanding invoices
- Compliance alerts (expiring checks, overdue inspections)
- Staff on site now

---

## AI Integration (Titan Zero)

Titan Zero provides in Titan Pro context:
- Decision summaries from overnight data
- Profitability warnings per client/contract
- Staff load balancing suggestions
- Schedule optimisation recommendations
- Quote drafting assistance
- Anomaly detection alerts

---

## Vertical Adaptation Layer

Each vertical modifies Titan Pro via overlay config:

| Vertical | Titan Pro adaptation |
|---|---|
| Bond | Inspection pack status cards, real estate agent contacts |
| Commercial | SLA compliance dashboard, logbook audit trail |
| Airbnb | Turnover completion rate, host satisfaction scores |
| Construction | Zone handover progress, SWMS compliance tracking |
| Biohazard | Regulatory reporting dashboard, PPE stock alerts |
| NDIS | Participant plan status, funding code tracking |

---

## Node Relationships

```
Titan Pro
├── reads from: all backend modules
├── triggers: BookingModule (scheduling actions)
├── views: Ground Zero live dispatch data
├── manages: Payroll, CleanerRecruit, Performance
└── AI: Titan Zero (full access)
```
