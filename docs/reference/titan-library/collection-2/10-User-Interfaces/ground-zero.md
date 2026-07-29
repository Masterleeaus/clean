# Ground Zero — Dispatch Control Panel

**Node type:** Execution · Dispatch · Scheduling  
**Primary users:** Scheduler, Dispatcher, Operations Manager

---

## Purpose

Real-time operations surface. Coordinates field activity across crews, sites, and schedules as it happens.

---

## Interface Type

Single Page Application  
Desktop · Tablet optimised  
Live updates via WebSocket broadcasting

---

## Core Responsibilities

- Live dispatch board (all crews, all sites, real-time)
- Crew allocation and reassignment
- Route visualisation (map view)
- Visit status tracking (en route, on site, complete, missed)
- Exception handling (no-shows, late arrivals, conflicts)

---

## Interactive Controls

- Drag-and-drop scheduling (move jobs between crews/times)
- Crew reassignment (swap cleaners mid-day)
- Priority escalation (push urgent jobs to top of queue)
- Urgent job injection (add unplanned jobs)
- Visit cancellation and rebooking

---

## Operational Signals Displayed

| Signal | Source |
|---|---|
| Late arrival | CleanEquipment (GPS), TitanGo |
| No-show | BookingModule (check-in timeout) |
| Job overrun | TitanGo (time tracking) |
| Site conflict | ManagedPremises (access issue reported) |
| Equipment fault | CleanEquipment (field report) |
| Quality flag | CleanQuality (immediate fail) |

---

## AI Integration (Titan Zero)

In Ground Zero context, Titan Zero suggests:
- Auto-reassignment when a cleaner is absent
- Capacity rebalancing when crews run over/under
- Conflict resolution (two jobs booked at same site)
- Travel time optimisation (reorder stops by proximity)

---

## Vertical Adaptation Layer

| Vertical | Ground Zero adaptation |
|---|---|
| Construction | Zone-based scheduling (different zones per day) |
| Commercial | Recurring rotation management by building floor |
| Airbnb | Turnover time windows with hard checkout deadlines |
| Biohazard | Compliance gating — job cannot start without PPE sign-off |
| Bond | Back-to-back sequencing for same-day inspections |

---

## Node Relationships

```
Ground Zero
├── reads: BookingModule (today's schedule)
├── reads: CleanEquipment (GPS, attendance)
├── reads: TitanGo (live job status)
├── reads: ManagedPremises (site access status)
├── writes: BookingModule (reassignment, rescheduling)
└── AI: Titan Zero (dispatch optimisation)
```
