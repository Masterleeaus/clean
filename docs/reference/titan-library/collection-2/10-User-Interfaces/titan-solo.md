# Titan Solo — Single Operator Mode

**Node type:** Micro-operations · Personal Workflow  
**Primary users:** Owner-operator, Solo cleaner, Freelancer

---

## Purpose

Simplifies Titan BOS for businesses run by one person. One screen runs the entire business.

> A sole trader should not need an enterprise dashboard. Titan Solo is Titan BOS without the complexity.

---

## Interface Type

Full-screen application with PWA support  
Mobile + Desktop  
Simplified layout — essential actions only

---

## Core Responsibilities

- Today's schedule (what's next, what's done)
- Client messaging (quick replies, booking confirmations)
- Job tracking (start, complete, note issues)
- Invoicing (one-tap invoice from completed job)
- Task reminders (follow-ups, re-quotes, renewals)
- Basic reporting (revenue this week/month)

---

## Design Principle

One person runs the entire business from one screen. No unnecessary complexity. Every action should be completable in under three taps.

**What Titan Solo hides:**
- Multi-crew dispatch (Ground Zero)
- Staff management (Payroll, HR)
- Complex reporting (Titan Pro analytics)
- Campaign management (Titan Studio)
- Multi-site admin

**What Titan Solo surfaces:**
- Next job
- Unread messages
- Unpaid invoices
- Quotes awaiting approval
- Today's earnings

---

## Upgrade Path

Titan Solo grows into Titan Pro automatically. When a solo operator hires their first employee, the system guides them to unlock crew management without migrating data or changing platforms.

---

## AI Integration (Titan Zero)

In Titan Solo context, Titan Zero provides:
- Next-task suggestions (what to do between jobs)
- Quote drafting from a client message
- Follow-up reminder generation
- Schedule gap filling suggestions
- Simple cashflow summary ("you have $1,400 outstanding")

---

## Vertical Adaptation Layer

Titan Solo supports all 12 verticals — the overlay simply adjusts the checklist and artefact type shown. A solo bond cleaner sees bond checklists; a solo pool cleaner sees water chemistry logs.

---

## Node Relationships

```
Titan Solo
├── reads: BookingModule (today's jobs)
├── reads: Accountings (outstanding invoices)
├── reads: TitanReach (unread messages)
├── writes: BookingModule (job start/complete)
├── writes: Accountings (invoice creation)
├── comms: TitanHello (client message handling)
└── AI: Titan Zero (simplified, one-person context)
```
