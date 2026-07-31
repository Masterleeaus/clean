# ChatGPT Agent Quick Reference Guide

**Repository:** `Masterleeaus/clean` — Titan Zero Integration Workspace  
**Last Updated:** 2026-07-29  
**Purpose:** Quick lookup for repository capabilities, architecture, and API access

---

## Architecture at a Glance

```
MagicAI (Laravel Host)
├── Authentication & Multi-tenancy
├── Extension Platform
├── API Gateway
│
├── WorkCore Domain (Operational Core)
│   ├── CRM (Customers, Companies)
│   ├── Finance (Invoices, Payments, Accounting)
│   ├── Jobs & Scheduling
│   ├── Compliance & Verticals
│   ├── Audit & Event Store
│   └── Permissions & Authorization
│
├── Interaction Engine (Wizard & Workflow Executor)
│   ├── Wizard Definitions (JSON/YAML)
│   ├── Universal Wizard Runtime
│   ├── Local Intelligence (Perception, Memory, Reasoning)
│   ├── Memory Management (Working, Episodic, Semantic)
│   └── Confidence & Abstention Scoring
│
├── Extensions (100+)
│   ├── AI Providers (OpenAI, OpenRouter, Azure, Perplexity)
│   ├── AI Tools (Chat Pro, Agent, Voice, Image, Video)
│   ├── Channels (Chatbot, Messenger, WhatsApp, Telegram)
│   ├── Integrations (Gmail, Calendar, Drive, Notion, HubSpot)
│   └── Business Tools (Marketing Bot, Content Manager, SEO)
│
└── Titan Zero Chatbot PWA (Device-First)
    ├── Service Worker & Offline Sync
    ├── Encrypted Command Outbox
    ├── Local WorkCore Client Adapter
    ├── Interaction Engine TypeScript Runtime
    ├── Generative UI Components
    ├── Voice & Device Tools
    └── Conflict Resolution & Vault
```

---

## Domain Structure Map

### 1. WorkCore (`app/Domains/WorkCore/`)
**Purpose:** Authoritative business operations and data

| Component | Location | Responsibility |
|-----------|----------|-----------------|
| Models | `System/Models/` | Business entities |
| Commands | `System/Commands/` | Business actions |
| Queries | `System/Queries/` | Data access |
| Repositories | `System/Repositories/` | Data persistence |
| Verticals | `System/Verticals/` | Industry-specific modules |
| Contracts | `Contracts/` | Data contracts |
| Policies | `System/Authorization/` | Permission rules |
| Events | `Events/` | Domain events |
| Migrations | `Database/Migrations/` | Schema changes |

**Key Verticals:**
- TradeCompliance - Compliance, permits, safety
- Finance - Invoicing, payments, accounting
- CRM - Customer & company management
- Jobs - Work orders and scheduling
- Reporting - Business intelligence

### 2. Interaction Engine (`packages/titan-zero/interaction-engine/` & `app/Domains/Engine/`)
**Purpose:** Wizard definitions, workflow execution, local intelligence

| Component | Location | Responsibility |
|-----------|----------|-----------------|
| Wizard Registry | `Services/` | Definition discovery |
| Wizard Engine | `Services/UniversalWizardEngine.php` | Execution logic |
| Validation | `Services/WizardValidationEngine.php` | Field validation |
| Memory | `Services/Memory/` | Knowledge storage |
| Intelligence | `Services/LocalBrain/` | Perception, reasoning |
| Definitions | `definitions/` | JSON/YAML templates |
| Contracts | `Contracts/` | Engine contracts |

**Wizard Capabilities:**
- Multi-step workflows with conditional logic
- Field validation and transformation
- Permission-based step visibility
- Offline execution with sync
- Confidence scoring
- Memory integration
- Audit trail

### 3. Extensions (`app/Extensions/`)
**Purpose:** Pluggable features and integrations

**AI Provider Extensions:**
- `OpenRouter` - Multi-model routing
- `MultiModel` - Parallel execution
- `ModelCouncil` - Consensus voting
- `Perplexity` - Research
- `OpenAIRealtimeChat` - Voice
- `NanoBanana`, `SeeDreamV4` - Image generation
- `Midjourney` - Creative

**AI Tool Extensions:**
- `AIAgent` - Agent framework with tools
- `AIChatPro` - Advanced chat with features
- `AIChatProSkills` - Skill management
- `AIImagePro` - Image creation
- `AIVideoPro` - Video generation
- `AiPresentation`, `AiMusic`, `AiAvatar` - Creative

**Channel Extensions:**
- `Chatbot` - Base chatbot runtime
- `TitanZeroChatbot` - Device-first PWA
- `ChatbotMessenger` - Facebook Messenger
- `ChatbotWhatsapp` - WhatsApp
- `ChatbotTelegram` - Telegram
- `ChatbotInstagram` - Instagram

**Integration Extensions:**
- `AIChatProGmail` - Gmail access
- `AIChatProGoogleCalendar` - Calendar
- `AIChatProGoogleDrive` - File storage
- `AIChatProNotion` - Notion sync
- `AIChatProOutlook` - Outlook
- `Mailchimp` - Email marketing
- `Hubspot` - CRM

---

## Key API Routes

### Authentication
```
POST /api/auth/register          — Register user
POST /api/auth/forgot-password   — Reset password
POST /api/auth/login             — OAuth login
POST /api/auth/google-login      — Google OAuth
POST /api/auth/apple-login       — Apple OAuth
```

### User & Profile
```
GET  /api/user                   — Current user
GET  /api/auth/profile           — User profile
PATCH /api/auth/profile          — Update profile
DELETE /api/auth/profile         — Delete account
```

### Credits & Usage
```
GET  /api/v1/shared-credit/balance   — Credit balance
GET  /api/v1/shared-credit/history   — Transaction history
GET  /api/v1/shared-credit/cost/{entity} — Cost calculator
```

### Application Settings
```
GET  /api/app/email-confirmation-setting
GET  /api/app/get-setting
GET  /api/app/usage-data
GET  /api/app/currency/{id}
```

### AI Chat (General)
```
POST /api/aichat/stream-test
POST /api/aichat/change-chat-title
GET  /api/general/recent-documents
GET  /api/general/favorite-openai
POST /api/general/search
```

### Chat Templates
```
GET  /api/aichat/chat-templates           — List templates
GET  /api/aichat/chat-templates/{id}      — Get template
POST /api/aichat/chat-templates           — Create template
```

---

## Data Models & Relationships

### Core WorkCore Models
```php
User
├── company (Company)
├── workspaces (Workspace[])
├── roles (Role[])
└── permissions (Permission[])

Company
├── users (User[])
├── properties (Property[])
├── customers (Customer[])
├── jobs (Job[])
└── invoices (Invoice[])

Customer
├── company (Company)
├── properties (Property[])
├── contacts (Contact[])
└── jobs (Job[])

Job
├── customer (Customer)
├── property (Property)
├── tasks (Task[])
├── schedule (Schedule)
└── invoice (Invoice)

Invoice
├── company (Company)
├── customer (Customer)
├── line_items (LineItem[])
└── payments (Payment[])

Payment
├── invoice (Invoice)
├── company (Company)
└── audit_log (AuditLog[])
```

---

## Wizard Definition Schema

### Basic Structure
```json
{
  "id": "wizard-id",
  "version": "1.0.0",
  "title": "Create Customer",
  "description": "Wizard to create a new customer",
  "offline_policy": "cache|sync|online_only",
  
  "steps": [
    {
      "id": "step-1",
      "type": "question|confirmation|entity_select|checklist",
      "prompt": "What is the customer name?",
      "field": "customer.name",
      "validation": {
        "required": true,
        "min_length": 3,
        "max_length": 100
      },
      "conditions": [
        {
          "field": "step-1-visible",
          "operator": "equals",
          "value": true
        }
      ]
    }
  ],
  
  "completion": {
    "command": "workcore.customer.create",
    "requires_approval": false,
    "approval_amount": 1000,
    "audit": true
  },
  
  "permissions": {
    "required_roles": ["manager", "admin"],
    "tenant_scoped": true
  }
}
```

### Step Types
- `question` - Simple text input
- `confirmation` - Yes/no prompt
- `entity_select` - Choose from WorkCore entities
- `checklist` - Multi-select items
- `evidence` - Document upload
- `approval` - Manager approval
- `summary` - Completion summary

---

## WorkCore Commands Registry

### Customer Commands
```
workcore.customer.create      — Create customer
workcore.customer.update      — Update customer
workcore.customer.delete      — Delete customer
workcore.customer.tag         — Add customer tag
```

### Job Commands
```
workcore.job.create           — Create job
workcore.job.update           — Update job
workcore.job.complete         — Mark complete
workcore.job.assign           — Assign to worker
workcore.job.add-material     — Add material
```

### Finance Commands
```
workcore.invoice.create       — Create invoice
workcore.invoice.send         — Send invoice
workcore.payment.record       — Record payment
workcore.expense.record       — Record expense
```

### Compliance Commands
```
workcore.permit.register      — Register permit
workcore.safety.record        — Record safety check
workcore.evidence.add         — Add compliance evidence
```

---

## Extension Capability Matrix

| Extension | AI Provider | Channels | Integrations | Features |
|-----------|-------------|----------|--------------|----------|
| AIAgent | — | — | Tools | Workflows, automation |
| AIChatPro | OpenAI, OpenRouter | Web | File, Memory | Deep research, skills |
| ModelCouncil | Multi-provider | Web | — | Consensus voting |
| OpenRouter | Multi-model | Web | — | Model routing |
| AIImagePro | Stable Diffusion, Flux | Web | — | Image generation |
| AiPresentation | OpenAI, DALL-E | Web | — | Slide generation |
| TitanZeroChatbot | Any | PWA | Local | Offline-first |

---

## Permission Model

### Role-Based Access Control
```
Role
├── admin             — Full system access
├── manager           — Team management
├── operator          — Execution only
├── readonly          — View only
├── finance           — Financial operations
├── compliance        — Compliance operations
└── custom roles
```

### Resource Permissions
```
Resource (Document, Job, Invoice, etc.)
├── view              — Read access
├── create            — Can create new
├── edit              — Can modify
├── delete            — Can delete
├── approve           — Can approve actions
└── audit             — Can access audit log
```

### Permission Checks
```php
// Wizard step visibility
$user->can('execute', 'workcore.job.create')
// Approval requirements
$wizard->requires_approval_for($amount)
// Tenant isolation
$job->company_id === $user->company_id
// Device/device restrictions
$capability->allowed_on_device($device_type)
```

---

## Offline Mode Capabilities

### What Works Offline
- View cached customer/job data
- Execute local validation
- Complete wizard offline
- Queue commands for sync
- Access cached knowledge
- Run local intelligence
- Manage encrypted vault
- Handle conflicts

### What Requires Online
- WorkCore command execution
- Financial transactions
- Compliance registration
- External integrations
- Real-time syncing
- Permission updates
- Knowledge base refresh

### Offline Data Storage
```
IndexedDB
├── wizard_definitions (signed manifests)
├── wizard_sessions (draft state)
├── command_outbox (encrypted queue)
├── cached_customers (snapshot)
├── cached_jobs (snapshot)
└── sync_metadata
```

### Sync Protocol
```
1. Detect online
2. Load command outbox
3. Validate encryption keys
4. Batch commands by idempotency key
5. Post to /api/offline-commands
6. Mark envelopes as synced
7. Handle conflicts
8. Update local snapshots
```

---

## Testing Hierarchy

```
Level 1: PHP Syntax
├── php -l                      (Lint)
├── Architecture tests          (Domain isolation)
└── Contract tests              (Interface compliance)

Level 2: Unit Tests
├── Model tests
├── Command tests
├── Service tests
└── Validator tests

Level 3: Integration Tests
├── Wizard engine tests
├── WorkCore adapter tests
├── Extension integration tests
└── Permission tests

Level 4: E2E Tests
├── Wizard execution (offline)
├── Sync scenarios
├── Conflict resolution
└── Multi-tenant isolation
```

---

## Configuration Files

### Key Configuration Locations
```
.env                           — Environment variables
config/app.php                 — App configuration
config/database.php            — Database
config/auth.php                — Authentication
app/Domains/WorkCore/Config/   — WorkCore settings
app/Domains/Engine/Config/     — Engine settings
app/Extensions/*/extension.json — Extension manifests
```

### Environment Variables
```
APP_NAME=MagicAI
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=magicai
DB_USERNAME=root
DB_PASSWORD=

TENANCY_DB_HOST=localhost      — Multi-tenancy database
TENANCY_ENABLED=true

OFFLINE_MODE_ENABLED=true       — PWA offline support
CACHE_SIGNED_DEFINITIONS=true   — Cache wizard definitions

OPENAI_API_KEY=sk-...           — AI provider keys
OPENROUTER_API_KEY=sk-...
AZURE_OPENAI_KEY=...
```

---

## Common ChatGPT Agent Tasks

### "What extensions are available?"
→ Run: `Extension Capability Mapping Workflow`  
→ Check: `app/Extensions/*/extension.json`  
→ Result: Full capability matrix with features

### "Can I execute this command?"
→ Check: `WorkCore Command Registry Analysis`  
→ Verify: `app/Domains/WorkCore/System/Commands/`  
→ Validate: `workcore:export-commands`

### "How do I create a new workflow?"
→ Review: Wizard Definition Schema (above)  
→ Place at: `packages/titan-zero/interaction-engine/definitions/`  
→ Test: `Wizard Definition Validation Workflow`

### "What's the customer data structure?"
→ Check: `app/Domains/WorkCore/Models/Customer.php`  
→ Review: WorkCore Contracts via `Domain Contracts Export`  
→ Access: `Customer` in WorkCore Query Gateway

### "Will this break anything?"
→ Run: `Feature Branch Tests` on feature branch  
→ Run: `Integration Tests` for domains affected  
→ Check: `Domain Dependency Analysis`

### "How do I access WorkCore?"
→ Use: `WorkCore Query Gateway` (read-only)  
→ Execute: `WorkCore Command Gateway` (write operations)  
→ Check: Permissions via `Role-Based Access Control`

### "What's the offline strategy?"
→ Review: `PWA & Offline Runtime` section  
→ Check: `Offline Sync Simulation Workflow`  
→ Understand: Command outbox and encryption

### "How should the PWA handle this?"
→ Review: `app/Extensions/TitanZeroChatbot/`  
→ Check: TypeScript runtime in packages  
→ Understand: Offline-first principles

---

## Performance Metrics & Limits

### Database Query Performance
- WorkCore read queries: < 100ms (cached)
- WorkCore command execution: < 500ms
- Wizard step validation: < 50ms local
- Large result set (1000+ rows): paginate

### API Rate Limits
- Authenticated: 1000 req/hour
- Public: 100 req/hour
- Batch operations: 50 batch/hour

### File & Storage Limits
- Attachment size: 100MB
- Vault storage: 1GB per company
- IndexedDB quota: 50MB per domain
- Command queue: 10,000 pending

### PWA Performance
- Bundle size: < 3MB gzip
- Service worker: < 1MB
- IndexedDB size: < 50MB
- Offline response time: < 200ms

---

## Security & Compliance

### Multi-tenancy Isolation
- Every query scoped to `company_id`
- No cross-tenant data access
- Device tokens tenant-specific
- Audit logs tenant-scoped

### Credential Security
- No credentials in environment files
- API keys in secure vault
- Provider keys never cached
- Service worker secrets excluded

### Data Encryption
```
In Transit: HTTPS + TLS 1.3
At Rest: AES-256-GCM for sensitive data
Command Queue: AES-256-GCM encrypted envelopes
Vault: Client-side encryption before sync
Audit Logs: Immutable, hashed verification
```

### Audit Trail
- Every command logged with user/time/tenant
- Business actions tracked with outcomes
- Financial transactions immutable
- Compliance actions preserved

---

## Troubleshooting Quick Reference

| Issue | Likely Cause | Check |
|-------|-------------|-------|
| Extension won't load | Missing dependency | `extension:validate-dependencies` |
| Wizard fails at step 5 | Invalid field mapping | Check schema vs. WorkCore model |
| Command fails "permission denied" | User lacks role | Check `workcore:export-permissions` |
| Offline sync conflicts | Stale command queue | Review conflict detection logs |
| API returns 422 | Invalid input schema | Validate against exported schema |
| Extension hooks missing | Provider not registered | Check `extension:health-check` |
| Test suite fails architecture | Domain boundary violation | Run `Architecture tests` |
| PWA bundle too large | Unused code bundled | Run `PWA Bundle Analysis` |

---

## Useful Commands

### Artisan Commands
```bash
# Repository
php artisan tinker                              # PHP shell
php artisan route:list                          # Show routes
php artisan make:command CommandName            # Generate command

# WorkCore
php artisan workcore:export-commands            # Get commands
php artisan workcore:export-queries             # Get queries
php artisan workcore:validate-contracts         # Validate

# Extensions
php artisan extension:health-check              # Audit extensions
php artisan extension:validate-dependencies     # Check deps

# Wizards
php artisan wizard:validate-definitions         # Validate
php artisan wizard:test-definitions             # Test
php artisan wizard:export-schema                # Export schema

# Testing
php artisan test                                # Run tests
php artisan test --filter=Architecture          # Architecture only
php artisan test --parallel --coverage          # Parallel + coverage

# Database
php artisan migrate                             # Run migrations
php artisan migrate:rollback                    # Rollback
php artisan seed:DatabaseSeeder                 # Seed data
```

### Useful Routes for Investigation
```
GET  /routes                                    # All routes
GET  /vendor/telescope                          # Query debugger (if installed)
POST /api/workcore/test-command                 # Test runner (dev only)
```

---

## When to Escalate to Humans

- 🔴 Database corruption or schema mismatch
- 🔴 Cross-tenant data access issues
- 🔴 Undocumented architectural decisions
- 🔴 Changes to security/encryption
- 🔴 Vault or credential handling
- 🔴 Multi-step refactoring spanning domains
- 🟡 Complex permission model changes
- 🟡 PWA offline mode fundamentals
- 🟡 Billing/financial logic

---

## Quick Links & Reference

- **Repository**: `https://github.com/masterleeaus/clean`
- **Docs**: `/docs` directory
- **Upgrade Plans**: `MULTI_PASS_UPGRADE_PLAN.md`, `AGENT2-PWA-OFFLINE-UPGRADE-PLAN.md`
- **Extension Manifest**: `EXTENSIONS_IMPORT_MANIFEST.json`
- **Source Baseline**: Commit `a76eee53af7b72b9f740adb3fa757b3f4d527bd6`

---

**Last Updated:** 2026-07-29  
**Next Review:** When major architecture changes occur  
**Maintainer Notes:** Update domain structure on new verticals, refresh extension list quarterly
