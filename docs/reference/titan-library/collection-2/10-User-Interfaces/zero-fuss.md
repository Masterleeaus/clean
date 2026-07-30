# Zero Fuss — Customer App

**Node type:** Customer · Communication · Booking  
**Primary users:** Client, End Customer, Property Manager, Airbnb Host

---

## Purpose

The client interaction surface. Replaces portals, support inboxes, and booking forms with a single conversational PWA.

Zero Fuss = zero friction for the customer.

---

## Interface Type

Chat-first Progressive Web App  
Mobile-first · Desktop supported  
No app store — opens via link or QR code

---

## Core Responsibilities

- Book services (new booking, quote request)
- Track active jobs (real-time status, cleaner ETA)
- View and pay invoices (ZeroPay integration)
- Upload photos (before clean, damage claims)
- Approve quotes
- Receive updates (booking confirmation, reminder, completion)
- Send requests and questions

---

## Communication Model

Conversational interface powered by Titan Zero. The customer types naturally — Titan Zero extracts intent and routes to the correct action.

```
"Can I move my Thursday clean to Friday?"
→ Titan Zero detects: reschedule intent
→ Queries: Friday availability for this client
→ Responds: "Yes, 9am or 2pm available Friday. Which works?"
→ Action: updates BookingModule on confirmation
```

---

## Example Client Actions

| Client says | System does |
|---|---|
| "Book a clean" | Opens booking flow (date, type, address) |
| "Reschedule my visit" | Shows available slots, updates booking |
| "Where's my cleaner?" | Shows GPS ETA from TitanGo |
| "Approve this quote" | Signs off quote in TitanDocs |
| "Pay invoice" | Opens ZeroPay payment link |
| "Report an issue" | Creates ClientFeedback record |
| "Upload photos" | Attaches to booking/feedback record |

---

## Vertical Adaptation Layer

| Vertical | Zero Fuss adaptation |
|---|---|
| Bond | Inspection pack delivery, real estate agent access, condition report sign-off |
| Commercial | Logbook message thread, SLA status view |
| Airbnb | Turnover confirmation checklist, staging photo review |
| Construction | Progressive handover report delivery, zone sign-off |
| NDIS | Participant service agreement, session notes access |
| Carpet | Before/after photos, drying time notification |

---

## Node Relationships

```
Zero Fuss
├── reads: BookingModule (booking status, history)
├── reads: Accountings / ZeroPay (invoices, payment status)
├── reads: TitanDocs (reports, agreements, proof packs)
├── reads: TitanGo (live job tracking, ETA)
├── writes: BookingModule (new bookings, reschedules)
├── writes: ClientFeedback (complaints, ratings)
├── comms: TitanReach (send/receive via preferred channel)
└── AI: Titan Zero (conversation handling, intent routing)
```
