# Base App and AI Extension Reconciliation

## Scope

This record covers every package found in `Base App System Extensions.zip` and `AI System Extensions.zip` during the Titan Zero `0.5.0` integration pass.

The donor archives were treated as capability evidence, not as runtime authority. No donor directory, migration set, route group, user model, billing model, conversation authority or plaintext credential pattern was copied into autoload.

## Authority retained

- Meetup remains authoritative for users, authentication, conversations, messages, channels, realtime delivery and the user interface.
- Titan Zero remains authoritative for reasoning, orchestration, memory, skills, agents, provider selection and governed tool delegation.
- WorkCore remains authoritative for operational business records and is accessed only through registered actions and reads.
- Titan Vault remains authoritative for provider, connector and voice credentials.
- Titan Finance and ZeroPay remain authoritative for invoices, payments and reconciliation.

## Native runtime built

The useful cross-package concepts were consolidated into `App\Titan\Intelligence` rather than installing overlapping donor modules.

### Workspace

- folders
- canvases
- temporary and durable working surfaces
- hash-backed share links
- entity highlighting and highlight-to-ask concepts
- document-context references

### Memory and skills

- company-scoped memory records
- retention policy
- skill definitions and versions
- skill-to-agent assignments
- no unbounded cross-company recall

### Agent runtime

- agent definitions
- registered tool assignments
- knowledge-source references
- channels and workflows
- auditable run states
- no direct arbitrary tool or WorkCore execution

### Connectors and provider routing

- configured connector definitions
- Vault-backed connection records
- provider and model catalogue
- deterministic routing policies
- model-council run records
- adapters disabled until explicitly configured

### Voice

- voice profiles
- session lifecycle
- transcript and event records
- provider-neutral credential references

### Host experience

- announcements
- onboarding flows, steps and progress
- bounded intelligence summary in Titan Operations and conversation context

## Package decisions

The canonical machine-readable decision for all 75 packages is stored in `config/titan_intelligence.php` under `donor_classification`.

### Integrated as native Titan concepts

- `ai-chat-pro`
- `ai-chat-pro-folders`
- `ai-chat-pro-memory`
- `ai-chat-pro-skills`
- `canvas`
- `chat-share`
- `chat-pro-temp-chat`
- `ai-chat-pro-deep-research`
- `ai-chat-pro-entity-highlight`
- `ai-chat-pro-highlight-to-ask`
- `ai-chat-pro-file-chat`
- `focus-mode`
- `ai-agent`
- `ai-agent-tool-chatbot`
- `chatbot`
- `chatbot-agent`
- `webchat`
- `chatbot-voice`
- `chatbot-voice-call`
- `phone-call-agent`
- `multi-model`
- `model-council`
- `announcement`
- `onboarding-pro`
- `introductions`

These names identify source concepts only. Runtime code uses Titan namespaces, host tenancy and governed registries.

### Optional adapters, disabled by default

Connector concepts:

- Gmail and Outlook
- Google Drive and Google Calendar
- Notion
- Slack
- WhatsApp, Telegram, Messenger and Instagram
- HubSpot
- Mailchimp
- Xero
- WordPress
- Cloudflare R2

Provider and voice concepts:

- Azure OpenAI
- OpenRouter
- Perplexity
- OpenAI Realtime
- Azure TTS
- ElevenLabs
- voice isolation

Each requires a separately implemented and tested adapter, explicit company configuration, Titan Vault credentials, provider scopes, rate limits, error sanitisation and terms review. No live external call is enabled by this checkpoint.

### Deferred to the Marketing and Creative pass

- AI writer templates
- plagiarism/editor assistance
- AI persona
- UGC creator and UGC factory
- AI captions
- AI avatar and influencer avatar
- smart-image tooling
- chatbot review and reputation concepts
- marketing-bot and social-media agent tools

These are not operational authorities and are intentionally deferred to the next reconciliation pass.

### Rejected or quarantined

- Cryptomus and donor checkout flows: unapproved or duplicate payment authority.
- Discount Manager: duplicate finance authority.
- Chatbot booking: duplicate WorkCore scheduling authority.
- Chatbot ecommerce: duplicate commerce and finance authority.
- Chatbot customer tags: duplicate CRM authority.
- MagicAI migration package: incompatible schema authority.
- Menu and Mega Menu: duplicate interface authority.
- Chat Setting: duplicate company and AI settings authority.
- Content Manager: global CMS authority outside this runtime boundary.
- Live Customizer: duplicate global theme authority.
- Maintenance: unsafe public-maintenance patterns.

## Security decisions

- Credentials are stored only as Titan Vault references.
- Share tokens are returned once and persisted only as SHA-256 hashes.
- Company identity is server resolved and cannot be supplied as tenant authority in tool payloads.
- Agent tools must exist in the Titan capability registry or governed WorkCore registries.
- Connector, provider and voice keys must exist in configured allowlists.
- Intelligence summaries expose counts and lifecycle queues, not memory content, prompts, transcripts, tokens or credentials.
- Missing intelligence migrations fail closed with unavailable counts instead of application failure.

## Quarantined sources

All original Base App and AI donor archives remain outside runtime autoload. Their original routes, controllers, models, migrations, webhooks, direct provider clients, payment logic, settings logic and UI authorities were not installed.

## Remaining execution boundary

This checkpoint supplies a native governed runtime and configuration records. A connected environment is still required to implement and validate each chosen provider or connector adapter, execute Laravel migrations, run Eloquent tests, process queues and verify provider-specific terms and scopes.
