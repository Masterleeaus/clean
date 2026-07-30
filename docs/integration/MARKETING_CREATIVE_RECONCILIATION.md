# Marketing and Creative Extension Reconciliation

## Authority boundaries

Titan Creative and Marketing is a first-party Titan subsystem. It owns brand kits, audiences, templates, creative projects, private creative assets, generation records, campaigns, content calendars, publication workflows, SEO briefs, newsletters, automations and source-attributed analytics observations.

Meetup continues to own conversations, collaboration and notifications. Titan Zero continues to own intent and orchestration. Titan Intelligence continues to own agents, tools, connectors, model/provider routing and Titan Vault references. WorkCore continues to own customers, leads, contacts, jobs, properties, quotes, invoices and all operational business records.

## Integrated concepts

The 30 valid donor packages were reduced to shared native concepts:

- Brand kits and reusable audience definitions
- Versioned content templates
- Creative projects, private assets and revisions
- Image, presentation, product-shot, photoshoot, fashion, video, dubbing and music generation job types
- Campaigns, objectives, content items and calendars
- Approval-gated publication workflows
- Publication channels backed by Titan Intelligence connector references
- Marketing automation definitions and append-only run records
- SEO briefs with source references
- Newsletter drafts
- Source-attributed analytics observations

No donor database, controller, route, service provider or user-facing settings authority was installed.

## Optional adapters

Provider and channel implementations remain disabled definitions until they receive dedicated credentials, scopes, terms review, rate limits, tests and failure handling. These include OpenAI image generation, Fal AI, Freepik, Clipdrop, Novita, Together, Gamma, Pebblely, Midjourney/PiAPI, Google Lyria, ElevenLabs, Heygen, FFmpeg, Serper, WordPress, Mailchimp, Meta, LinkedIn, X, TikTok, Telegram and WhatsApp.

## Rejected duplicate authorities

The merge rejected donor ownership of:

- Users, authentication and company tenancy
- Conversations, inboxes and chat
- CRM contacts, customers, products and audiences derived by copying CRM data
- Credits, subscriptions, free items, invoices and payments
- Provider credentials and global settings
- OAuth tokens and refresh commands
- Public file storage and unsigned media downloads
- Provider webhooks and direct publishing routes
- Global dashboards, menus and editor injection

## Quarantined

The original ZIP packages remain outside the application tree and outside Composer autoload. Large binary examples, preview media, demo seeders, direct provider clients and provider-specific controllers were not copied into runtime.

## Provider activation boundary

A provider-backed generation or publication may run only after:

1. Titan Intelligence contains an active company-scoped provider or connector record.
2. Credentials are held by Titan Vault and referenced indirectly.
3. The adapter is implemented as an explicit provider-neutral contract.
4. Input, output, MIME, URL and storage restrictions are validated.
5. Rate limits, retries, safe errors, usage metering and audit events are tested.
6. Relevant provider terms and data-retention restrictions are recorded.

v0.6 stores durable workflow state only. It does not make live creative-provider, social-network, newsletter or publishing calls.
