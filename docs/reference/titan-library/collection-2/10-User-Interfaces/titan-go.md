# Titan Go — Field Worker App

**Node type:** Execution · Evidence · Compliance  
**Primary users:** Cleaner, Technician, Field Worker

---

## Purpose

The operational interface for field workers. Runs on their phone. Works offline. Every cleaning job runs through Titan Go.

---

## Interface Type

Progressive Web App (PWA)  
Mobile-first · Offline capable  
Syncs when connectivity returns

---

## Core Responsibilities

- Daily job list (sorted by start time)
- Job card (client address, access instructions, clean type)
- Checklist execution (room-by-room ticks)
- Photo capture (before/after, per room)
- Time tracking (GPS check-in/check-out)
- Notes and observations
- Issue reporting from the field

---

## Worker Workflow

```
1. Open Titan Go → today's jobs appear
2. Tap job → view job card (address, access code, requirements)
3. Navigate → tap address → Google Maps / Apple Maps
4. Arrive → scan site QR or GPS auto-check-in
5. Review checklist → tick each room/area as completed
6. Capture photos → before/after per area
7. Mark complete → triggers completion signal chain
8. Report issues → damage found, access denied, client present
```

---

## Offline Behaviour

- Full job card available offline (synced at job dispatch time)
- Checklists, photos, and notes saved locally (IndexedDB)
- Sync queue flushes automatically when connectivity returns
- Uses service worker + background sync (TitanPWA)

---

## Safety Layer

- PPE confirmation before job start (vertical-dependent)
- Site hazard acknowledgement
- SWMS display and sign-off (construction, biohazard verticals)
- Compliance sign-off on high-risk sites

---

## AI Integration (Titan Zero)

In Titan Go context, Titan Zero provides:
- Task guidance ("how to remove this type of stain")
- On-site instructions (chemical dilution ratios)
- Issue escalation suggestions
- Training prompts between jobs

---

## Vertical Adaptation Layer

| Vertical | Titan Go adaptation |
|---|---|
| Airbnb | Staging photos checklist, linen tracking, guest-ready sign-off |
| Construction | Zone verification sequence, progressive handover photos |
| Commercial | Lock-up checklist, building security sign-off |
| Solar | Weather validation gate, panel condition photo sequence |
| Biohazard | PPE don/doff log, containment zone check, regulatory photos |
| Bond | Room-by-room condition photo sequence, entry/exit comparison |
| NDIS | Participant consent confirmation, support log entry |

---

## Node Relationships

```
Titan Go
├── reads: BookingModule (today's assignments)
├── reads: ManagedPremises (access instructions, site notes)
├── reads: SupplyChain (COSHH data for chemicals on site)
├── writes: BookingModule (job completion signal)
├── writes: CleanEquipment (attendance log via GPS)
├── writes: CleanQuality (checklist results, photos)
├── writes: TitanDocs (proof-of-service photo package)
├── triggers: TitanPWA (background sync queue)
└── AI: Titan Zero (on-site guidance)
```
