# Titan Intelligence Runtime v0.5.0 Design

## Purpose

Reconcile the Base App System Extensions and AI System Extensions archives into the Titan Zero + Meetup + WorkCore application without importing duplicate MagicAI authorities. The pass converts useful donor concepts into native first-party Titan subsystems and leaves marketing/creative generators for the next pass.

## Authority map

- Meetup owns authentication, users, companies, memberships, conversations, messages, realtime collaboration and user-facing chat.
- Titan Zero owns AI orchestration, model routing, workspace context, memory, skills, agent runtime, connector governance and voice-session coordination.
- WorkCore owns every operational business record and remains accessible only through registered actions and read models.
- Titan Vault owns provider secrets, OAuth tokens and API credentials.
- Titan Audit records governed AI, connector, agent and sharing actions.
- External providers are transport/data processors only. They do not own conversations, memory, business records or accounting.

## Chosen approach

Build a native `App\Titan\Intelligence` runtime rather than installing donor extensions unchanged. Donor code is used as a capability and field catalogue. The runtime uses the existing active-company context, permission resolver, Vault, audit recorder and capability registry.

Rejected alternatives:

1. Installing AIChatPro, AI Agent and Chatbot packages unchanged: rejected because they duplicate conversations, users, connectors, plans, billing, routes and tool execution.
2. Keeping every donor as an optional extension: rejected because the core workspace, memory, skills and agent runtime are first-party platform authorities shared by all interfaces.
3. Copying donor tables and models under new namespaces: rejected because it preserves schema drift and duplicate lifecycle ownership.

## Native subsystem boundaries

### 1. Titan Workspace

Company-scoped folders, canvases, share grants and temporary conversation policy. Existing Meetup conversations remain canonical. Workspace records reference conversations rather than cloning them.

### 2. Titan Memory

Structured memory records with subject type/id, scope, memory type, source, confidence, sensitivity, retention, expiry and status. Memory never stores hidden chain-of-thought. Deletion is a governed status transition with audit history.

### 3. Titan Skills

Reusable skills with immutable versions, input/output schemas, instructions, provenance and assignments to users or agents. Skills never execute arbitrary PHP or shell code.

### 4. Titan Agent Runtime

Company-scoped agent profiles, allowed tools, knowledge-source references, channel bindings, workflow definitions and execution runs. Tool execution continues through the existing `ToolRouter`; agent records cannot bypass capability registration, permissions or confirmation.

### 5. Titan Connector Gateway

Provider-neutral connector definitions and company connections. Connections store only Vault references, scopes, status, health, cursors and sync metadata. Provider-specific Gmail, Outlook, Google Drive, Google Calendar, Notion, Slack, WhatsApp, Telegram, Messenger and Instagram adapters remain disabled until implemented and configured.

### 6. Titan Provider Routing

Provider definitions, model catalogue, routing policies, model-council runs and response records. Model selection is policy-driven, company-scoped and cost/latency/capability aware. Secrets remain in Vault. No direct provider HTTP client is introduced in this pass.

### 7. Titan Voice Runtime

Voice profiles, voice sessions, transcripts and call events. It coordinates STT/TTS/telephony providers but does not embed Twilio, ElevenLabs, Azure or OpenAI credentials in records.

### 8. Announcements and onboarding

Company announcements and member onboarding progress are host-experience capabilities. They do not introduce duplicate menus, billing, plans or global CMS authority.

## Data rules

- Every runtime record carries `company_id`.
- Client payloads cannot override company authority.
- Public UUIDs are used outside the database boundary.
- Provider secrets are represented by Vault references only.
- Connector payloads and provider responses are minimised and redacted.
- Temporary conversations do not enter long-term memory unless explicitly promoted.
- Agent and skill execution records are append-only except for lifecycle status fields.
- Share grants use hashed tokens, expiry and revocation.
- Model-council responses are observations; the selected response is a recorded decision, not an overwrite.

## Capability surface

The service provider registers governed Titan capabilities for workspace, memory, skills, agents, connectors, provider routing, voice, announcements and onboarding. Each capability declares a permission and a concrete handler. High-impact actions require explicit confirmation in their handler even though the generic capability registry lacks WorkCore's confirmation metadata.

## Donor classification

### Integrated conceptually in v0.5

- AIChatPro core, folders, canvas, memory, skills, share, temporary chat and deep research session concepts
- AI Agent profiles, tools, workflows, runs, channels, knowledge sources and memories
- Chatbot channel and voice-session concepts
- Multi-model, Model Council, OpenRouter, Azure OpenAI, Perplexity and OpenAI Realtime provider concepts
- Cloudflare R2 credential-reference pattern
- Announcements and onboarding progress

### Optional adapters retained as disabled definitions

- Gmail, Outlook, Google Drive, Google Calendar, Notion
- Slack, WhatsApp, Telegram, Messenger, Instagram
- Twilio, ElevenLabs, Azure TTS, OpenAI Realtime
- HubSpot, Mailchimp, Xero, WordPress and Cloudflare R2

### Deferred to marketing/creative pass

- UGC Creator, UGC Factory, AI Captions, AI Avatar, AI Persona, influencer avatar, writer templates, smart image and marketing/social tools

### Rejected or quarantined

- Duplicate users, conversations, messages, subscriptions, checkout, discounts and billing
- Direct external webhooks without signature verification
- Direct provider calls from controllers
- Plaintext API keys or OAuth tokens
- Duplicate chatbot carts, bookings and customer authorities
- Generic arbitrary-code agent actions
- Donor migrations targeting MagicAI tables

## Error and security handling

- Unknown providers, models, connectors, skills, agents and tools fail closed.
- Every handler resolves the active company from the server context.
- Permission denial returns stable safe errors.
- Connector and provider failure details are sanitised before user display.
- Share tokens are never persisted in plaintext.
- Memory marked sensitive cannot be exposed through ordinary conversation context.
- Agent tools must exist in the capability/action/read registries before assignment.

## Testing

- Pure PHP policy tests for memory retention, routing, share-token hashing, workflow state and voice state.
- Structural tests for migrations, company scope, Vault references, permissions, provider registration and donor quarantine.
- Runtime tests for registered capabilities and no direct WorkCore writes.
- Namespace scan and full existing standalone regression suite.
- Final ZIP extraction and independent retest.

## Release output

`Titan-Zero-Meetup-WorkCore-Integrated-v0.5.0.zip` with build report and SHA-256 checksum.
