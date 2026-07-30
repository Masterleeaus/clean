# Titan Studio — Growth Surface

**Node type:** Growth · Content · Lead Generation  
**Primary users:** Owner, Marketing Manager

---

## Purpose

Marketing and brand automation. Titan Studio removes the need for external marketing tools by generating and publishing content natively.

---

## Interface Type

Full-screen application  
Desktop-first

---

## Core Responsibilities

- Landing pages (service-specific, vertical-specific)
- Campaign builder (email, SMS, social)
- AI content generation (ads, web copy, emails)
- Social publishing (schedule and post)
- Lead funnel management (capture → qualify → convert)
- Review request automation

---

## AI Integration (Titan Zero)

In Titan Studio context, Titan Zero generates:
- Ad copy (Google Ads, Meta Ads, LinkedIn)
- Email campaigns (re-engagement, seasonal, promotional)
- Web page copy (service descriptions, landing pages)
- Social media posts
- Campaign plans and calendar suggestions
- Review response drafts

---

## Vertical Adaptation Layer

| Vertical | Titan Studio adaptation |
|---|---|
| Airbnb | Listing optimisation copy, host testimonial campaigns |
| Commercial | Tender response builder, capability statement generator |
| Construction | Builder relationship campaigns, defect liability content |
| Bond | Seasonal move-out campaign (lease-end targeting) |
| NDIS | Participant referral content, support coordinator outreach |
| Residential | Suburb-targeted campaigns, referral reward programs |

---

## Lead Funnel Flow

```
Ad / landing page → Lead captured
→ TitanHello auto-responds (Titan Zero intent detection)
→ Quote generated (BookingModule + TitanDocs)
→ Quote sent via TitanReach
→ Approved → converted to booking
→ Post-job → ClientFeedback → review request
→ Review → Titan Studio re-publishes as social proof
```

---

## Node Relationships

```
Titan Studio
├── reads: ClientFeedback (reviews for social proof)
├── reads: BookingModule (booking data for campaign targeting)
├── writes: TitanReach (campaign dispatch via SMS/email/social)
├── writes: TitanHello (lead intake triggers)
├── integrates: TitanIntegrations (Mailchimp, Meta Ads, Google Ads)
└── AI: Titan Zero (content generation, campaign planning)
```
