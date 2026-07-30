# Titan Creative and Marketing v0.6 Design

## Objective

Consolidate the 30 valid packages in `Marketing & Creative Extensions.zip` into one first-party Titan Creative and Marketing subsystem without importing duplicate application authorities or enabling unverified external providers.

## Authority boundaries

- Meetup owns conversations, channels, notifications and the human collaboration surface.
- Titan Zero owns intent, planning, tool selection and conversational orchestration.
- Titan Intelligence owns agents, skills, provider routing, model catalogue, connectors, voice and Vault references.
- Titan Creative and Marketing owns brand kits, creative projects, content assets, generation jobs, campaigns, calendars, publications, SEO briefs, newsletters, automations and analytics observations.
- WorkCore owns customers, leads, contacts, properties, jobs, quotes, invoices and operational business records.
- Titan Vault owns credentials. The creative/marketing subsystem stores only credential references.

## Architecture

Create `App\Titan\Creative` with focused domain policies, a repository contract, a database repository, action handlers and a service provider registering governed capabilities. Generation and publishing are durable records only; provider adapters remain disabled until separately implemented and verified. Marketing records may reference WorkCore public IDs but never duplicate CRM entities or write WorkCore tables.

## Data model

Twenty-one company-scoped tables:

1. `titan_brand_kits`
2. `titan_brand_assets`
3. `titan_audiences`
4. `titan_content_templates`
5. `titan_creative_projects`
6. `titan_creative_assets`
7. `titan_creative_revisions`
8. `titan_generation_jobs`
9. `titan_generation_outputs`
10. `titan_campaigns`
11. `titan_campaign_objectives`
12. `titan_content_items`
13. `titan_content_calendar_entries`
14. `titan_publication_channels`
15. `titan_publications`
16. `titan_marketing_automations`
17. `titan_marketing_automation_runs`
18. `titan_seo_briefs`
19. `titan_newsletters`
20. `titan_analytics_observations`
21. `titan_content_approvals`

## Domain rules

- Content cannot publish before approval when approval is required.
- Provider-backed generation and publication must reference an enabled configured provider/connector; no direct provider clients are introduced.
- Generation job state transitions are deterministic and terminal failures cannot return to processing.
- Publishing state transitions are deterministic and published records are immutable except through correction/retraction events.
- Campaign dates must be ordered.
- Analytics observations are append-only and source-attributed.
- Creative assets use private storage references rather than public paths.
- Marketing references to WorkCore use `entity_type` plus `entity_public_id`; no foreign ownership is copied.

## Governed capabilities

Register capabilities for creating brand kits, audiences, templates, projects, assets, generation jobs, campaign plans, content items, calendar entries, publication channels, publication requests, automations, SEO briefs, newsletters, approvals, analytics observations and a bounded summary.

## User surface

Add bounded counts to Titan Zero context and Titan Operations. Do not expose prompts, generated content, private storage references, provider payloads, access tokens or analytics raw payloads in the summary.

## Donor decisions

Every valid donor package receives a machine-readable classification: `integrated_concept`, `optional_adapter`, `deferred_provider`, `rejected_duplicate_authority`, or `quarantined`. No donor directory enters Composer autoload.

## Verification

Use standalone PHP contracts for domain rules, migration schema, repository/capability registration, host surface and donor decisions. Run existing verifier, namespace scan, PHP syntax validation, JavaScript syntax validation, ZIP integrity and clean-extraction retests. Framework-dependent Laravel tests remain a connected-environment requirement because `vendor/` is absent.
