# Titan Hello — Receptionist & Omni Gateway

**Node type:** Intake · Communication · Lead Capture  
**Primary users:** All inbound channels (no human required by default)

---

## Purpose

Manages all inbound communication across every channel. Titan Hello is the front door of the business — it never misses a call, message, or lead.

> Zero missed calls. Zero unanswered messages.

---

## Interface Type

Chat + Voice gateway  
No dedicated screen required — operates as an always-on background system  
Management UI in Titan Pro (call log, message inbox, campaign history)

---

## Channels Supported

| Channel | Capability |
|---|---|
| SMS | Inbound/outbound, two-way conversation |
| WhatsApp | Message, media, voice note, template messages |
| Facebook Messenger | Lead capture, booking intake |
| Email | Inbound routing, auto-response, template sending |
| Voice calls | Inbound answering, IVR, outbound campaigns |
| Telegram | Message routing |
| Web chat widget | Embedded on business website |

---

## Core Responsibilities

- Lead qualification (is this a real lead? what service?)
- Booking intake (capture date, service, address from conversation)
- Status responses ("your cleaner is on the way")
- Customer routing (escalate to human when needed)
- Missed call callback queue
- Campaign delivery (outbound SMS/call campaigns)

---

## Conversation Flow

```
Inbound arrives (any channel)
→ Titan Zero: intent detection
→ Route: booking / status / support / escalate
→ Booking: extract details → create in BookingModule
→ Status: query BookingModule → respond with live status
→ Support: create ClientFeedback record → assign
→ Escalate: notify human via TitanTalk
```

---

## AI Integration (Titan Zero)

Titan Zero handles Titan Hello conversations:
- Natural language understanding across all channels
- Booking detail extraction ("next Tuesday, 3-bedroom unit in Surry Hills")
- Quote generation from conversation context
- Objection handling ("our team is fully insured and police-checked")
- Escalation detection (angry/upset → route to human immediately)

---

## Vertical Adaptation Layer

| Vertical | Titan Hello adaptation |
|---|---|
| Bond | Pre-inspection checklist intake, agent contact capture |
| Commercial | After-hours emergency line routing, site manager contact |
| Airbnb | Host turnover request intake, guest complaint routing |
| Biohazard | Emergency callout intake, regulatory body notification |
| NDIS | Participant/coordinator intake, funding check prompts |

---

## Node Relationships

```
Titan Hello
├── channels: TitanReach (SMS, WhatsApp, Telegram, Email)
├── voice: TitanHello module (Twilio/voice routing)
├── writes: BookingModule (new bookings from conversation)
├── writes: ClientFeedback (complaints, issues from conversation)
├── notifies: TitanTalk (escalation to human)
├── triggers: Titan Studio (lead captured → enter nurture sequence)
└── AI: Titan Zero (full conversation handling)
```
