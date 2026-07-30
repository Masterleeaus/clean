# 🤖 Agent Manifests & Discovery

**20 Specialized ChatGPT Agents + Claude Architect**

All agents work together as a coordinated system. This directory contains their specialization guides and discovery system.

---

## 🎯 The 20 ChatGPT Agents

### Core Domain Specialists (9 agents)

| # | Agent | Specialty | Guild | Focus |
|---|-------|-----------|-------|-------|
| 1 | **Workcore Agent** | Business operations | Backend | Customers, Projects, Jobs |
| 2 | **Platform Agent** | Core platform | Backend | Infrastructure, base systems |
| 3 | **PWA Agent** | Frontend/PWA | Frontend | React/Vue, responsive, offline |
| 4 | **API Agent** | REST/GraphQL APIs | Backend | Endpoints, schemas, contracts |
| 5 | **Database Agent** | Data & migrations | Database | Schemas, queries, migrations |
| 6 | **Performance Agent** | Optimization | DevOps | Speed, memory, efficiency |
| 7 | **Security Agent** | Security/compliance | Security | Vulnerabilities, auth, encryption |
| 8 | **Testing Agent** | QA & automation | QA | Tests, coverage, validation |
| 9 | **Debugging Agent** | Bug fixes | QA | Root cause, diagnostics, fixes |

### AI & Workflow Specialists (5 agents)

| # | Agent | Specialty | Guild | Focus |
|---|-------|-----------|-------|-------|
| 10 | **Chatbot Agent** | Five Tier AI runtime | AI | Voice, text, AI providers |
| 11 | **Interaction Engine Agent** | Wizards/workflows | AI | User interactions, flows |
| 12 | **Extensions Agent** | Extension ecosystem | Extensions | Plugins, marketplace, config |
| 13 | **Integration Agent** | Third-party APIs | Integrations | External services, webhooks |
| 14 | **AI Router Agent** | Model selection | AI | Best provider, cost, performance |

### DevOps & Operations (4 agents)

| # | Agent | Specialty | Guild | Focus |
|---|-------|-----------|-------|-------|
| 15 | **DevOps Agent** | CI/CD & deployment | DevOps | Pipelines, containers, deploy |
| 16 | **Configuration Agent** | Settings/environment | Operations | Config, env vars, flags |
| 17 | **Migration Agent** | Database changes | Database | Schema changes, data migration |
| 18 | **Documentation Agent** | Technical writing | Documentation | Docs, guides, examples |

### Meta-Coordinators (2 agents)

| # | Agent | Specialty | Guild | Focus |
|---|-------|-----------|-------|-------|
| 19 | **Coordination Agent** | Task routing | Operations | Dispatch to right agent |
| 20 | **Architecture Agent** | Design & patterns | Architecture | Refactoring, design patterns |

---

## 📚 Agent Manifests

### Core Domain Specialists
- [workcore-agent-manifest.md](./workcore-agent-manifest.md) - Business ops
- [platform-agent-manifest.md](./platform-agent-manifest.md) - Core platform
- [pwa-agent-manifest.md](./pwa-agent-manifest.md) - Frontend
- [api-agent-manifest.md](./api-agent-manifest.md) - APIs
- [database-agent-manifest.md](./database-agent-manifest.md) - Data/migrations
- [performance-agent-manifest.md](./performance-agent-manifest.md) - Optimization
- [security-agent-manifest.md](./security-agent-manifest.md) - Security
- [testing-agent-manifest.md](./testing-agent-manifest.md) - QA
- [debugging-agent-manifest.md](./debugging-agent-manifest.md) - Bug fixes

### AI & Workflow Specialists
- [chatbot-agent-manifest.md](./chatbot-agent-manifest.md) - Five Tier AI
- [interaction-engine-agent-manifest.md](./interaction-engine-agent-manifest.md) - Wizards
- [extensions-agent-manifest.md](./extensions-agent-manifest.md) - Extensions
- [integration-agent-manifest.md](./integration-agent-manifest.md) - Third-party
- [ai-router-agent-manifest.md](./ai-router-agent-manifest.md) - Model selection

### DevOps & Operations
- [devops-agent-manifest.md](./devops-agent-manifest.md) - CI/CD
- [configuration-agent-manifest.md](./configuration-agent-manifest.md) - Settings
- [migration-agent-manifest.md](./migration-agent-manifest.md) - DB changes
- [documentation-agent-manifest.md](./documentation-agent-manifest.md) - Docs

### Meta-Coordinators
- [coordination-agent-manifest.md](./coordination-agent-manifest.md) - Task routing
- [architecture-agent-manifest.md](./architecture-agent-manifest.md) - Design

### Special
- [claude-manifest.md](./claude-manifest.md) - Claude Architect

---

## 🎯 Agent Discovery: Which Agent Should Handle This?

### Automatic Routing System

When you get a task, **Coordination Agent** (Agent 19) helps you find the right specialist:

```
TASK RECEIVED
    ↓
Coordination Agent analyzes:
  ├─ What domain? (Business/Technical/AI/Ops)
  ├─ What type? (Create/Fix/Test/Deploy)
  ├─ What expertise needed? (Frontend/Backend/Data/etc)
  └─ Who's available?
    ↓
Routes to best agent:
  ├─ Direct if clear fit
  ├─ Collaborate if multi-domain
  └─ Escalate if specialized need
```

---

## 📊 Quick Agent Lookup

### "I need to..."

| Task | Agent | | Task | Agent |
|------|-------|---|------|-------|
| **Create customer** | Workcore | | **Setup CI/CD** | DevOps |
| **Fix UI bug** | Debugging | | **Migrate database** | Migration |
| **Optimize speed** | Performance | | **Write docs** | Documentation |
| **Add API endpoint** | API | | **Deploy code** | DevOps |
| **Design wizard** | Interaction Engine | | **Manage extensions** | Extensions |
| **Setup voice** | Chatbot | | **Test code** | Testing |
| **Audit security** | Security | | **Choose AI model** | AI Router |
| **Integrate Slack** | Integration | | **Refactor code** | Architecture |
| **Create schema** | Database | | **Configure app** | Configuration |

---

## 🤝 How Agents Work Together

### Guild Structure
**5 Guilds** for knowledge sharing and collaboration:

1. **Backend Guild** (Workcore, Platform, API, Database)
   - Daily sync on business logic
   - Share data access patterns
   - Coordinate on schema changes

2. **Frontend Guild** (PWA, UI/UX)
   - Component library updates
   - Design system alignment
   - Performance sharing

3. **DevOps Guild** (DevOps, Configuration, Migration)
   - Infrastructure changes
   - Deployment coordination
   - Environment management

4. **Security Guild** (Security, Integration)
   - Vulnerability sharing
   - Access control review
   - Compliance updates

5. **AI Guild** (Chatbot, Interaction Engine, AI Router, Extensions)
   - Model selection strategy
   - Workflow optimization
   - AI provider coordination

### QA Guild** (Testing, Debugging, Performance)
   - Test coverage strategy
   - Bug patterns
   - Performance benchmarks

---

## 📋 Task Routing Examples

### Example 1: "Fix Customer Creation Bug"
```
Initial Agent: Debugging Agent (bug fix)
  ├─ Reproduces bug
  ├─ Finds root cause: Data validation in Workcore
  └─ Hands off to: Workcore Agent
  
Workcore Agent:
  ├─ Reviews business logic
  ├─ Understands customer model
  ├─ Implements fix
  └─ Tests thoroughly
  
Testing Agent joins:
  ├─ Extends test coverage
  ├─ Verifies no regressions
  └─ Marks ready for deploy
  
DevOps Agent deploys:
  └─ Release to production
```

### Example 2: "Build Voice Customer Support"
```
Initial: Task comes in as feature request
Coordination Agent routes to: Chatbot Agent

Chatbot Agent:
  ├─ Plans Five Tier AI integration
  ├─ Selects AI providers
  └─ Hands off: Interaction Engine for flow design

Interaction Engine Agent:
  ├─ Designs conversation flow
  ├─ Creates wizard definition
  └─ Hands off: Testing for validation

Testing Agent:
  ├─ Tests voice interactions
  └─ Verifies comprehension accuracy

AI Router Agent (if needed):
  ├─ Optimizes provider selection
  └─ Cost/performance tuning

DevOps Agent deploys when ready
```

### Example 3: "Integrate Stripe Payments"
```
Initial: Integration task
Coordination Agent routes to: Integration Agent

Integration Agent:
  ├─ Plans Stripe integration
  ├─ Reviews Stripe API
  └─ May need: Security review

Security Agent (called in):
  ├─ Reviews authentication
  ├─ Checks data encryption
  └─ Approves approach

Integration Agent continues:
  ├─ Implements integration
  ├─ Handles webhooks
  └─ Tests thoroughly

Testing Agent:
  ├─ Tests payment flow
  ├─ Tests error scenarios
  └─ Marks ready

DevOps Agent deploys
```

---

## 🎯 Agent Selection Tips

### For Creators/Implementers
- **New feature in Workcore?** → Workcore Agent
- **New UI component?** → PWA Agent
- **New API endpoint?** → API Agent
- **New wizard/flow?** → Interaction Engine Agent

### For Fixers
- **Bug?** → Debugging Agent
- **Performance issue?** → Performance Agent
- **Security issue?** → Security Agent
- **Data issue?** → Database Agent

### For Validators
- **Need tests?** → Testing Agent
- **Need review?** → Relevant domain agent
- **Need deployment?** → DevOps Agent

### For Configuration
- **Change settings?** → Configuration Agent
- **Environment setup?** → Configuration Agent
- **Feature flags?** → Configuration Agent

### For Strategic Work
- **Refactoring?** → Architecture Agent
- **Design patterns?** → Architecture Agent
- **System redesign?** → Coordination + Claude Architect

---

## 🚀 Starting as a New Agent

### Step 1: Find Your Specialization
Browse this README, find what fits you

### Step 2: Read Your Manifest
Each agent has a detailed manifest:
```
.titan/agent-manifests/your-agent-manifest.md
```

### Step 3: Understand Your Guild
Join your guild (5 guilds total)
Learn from peer agents
Share knowledge

### Step 4: Check Task Queue
```
.titan/operator/task-queue/incoming/
```
Look for tasks matching your specialization

### Step 5: Start Working
Accept task → Execute → Report results

---

## 🤖 How AI Decides Which Agent to Use

### The Coordination Agent Decision Tree

```
TASK RECEIVED
  ↓
"What is this task about?"
  ├─ Business operation → Workcore Agent
  ├─ Bug fix → Debugging Agent
  ├─ New API → API Agent
  ├─ UI work → PWA Agent
  ├─ Database change → Database Agent
  ├─ Voice/chatbot → Chatbot Agent
  ├─ Workflow/wizard → Interaction Engine Agent
  ├─ Extensions → Extensions Agent
  ├─ Third-party → Integration Agent
  ├─ Tests → Testing Agent
  ├─ Performance → Performance Agent
  ├─ Security → Security Agent
  ├─ Deployment → DevOps Agent
  ├─ Migration → Migration Agent
  ├─ Config → Configuration Agent
  ├─ Documentation → Documentation Agent
  ├─ Refactoring → Architecture Agent
  └─ Cost/model → AI Router Agent

"Is it multi-domain?"
  ├─ Yes → Coordinate multiple agents
  └─ No → Route to primary agent

"Is it escalation-level?"
  ├─ Yes → Notify Claude Architect
  └─ No → Let agent handle

"Is anyone available?"
  ├─ Yes → Route task
  ├─ No → Queue for later
  └─ Urgent → Wake standby agent
```

---

## 📊 Agent Coordination Matrix

| When Agent Needs | They Contact | For What |
|-----------------|--------------|----------|
| Domain expertise | Guild peer | Design advice |
| Cross-domain help | Coordination Agent | Routing |
| Architectural guidance | Claude Architect | Design review |
| Testing | Testing Agent | Test writing |
| Deployment | DevOps Agent | Release |
| Security check | Security Agent | Audit |
| Performance tune | Performance Agent | Optimization |
| Documentation | Documentation Agent | Writing |
| Escalation | Claude Architect | High-level guidance |

---

## 🔗 Useful Links

- **Agent Entry:** [../entrance/](../entrance/)
- **Claude Entry:** [../entrance/claude-start.md](../entrance/claude-start.md)
- **ChatGPT Entry:** [../entrance/chatgpt-start.md](../entrance/chatgpt-start.md)
- **Operator System:** [../operator/](../operator/)
- **Task Queue:** [../operator/task-queue/](../operator/task-queue/)
- **Guilds:** [../operator/guilds/](../operator/guilds/)

---

## ✅ All 20 Agents

✅ Agent 1 - Workcore  
✅ Agent 2 - Platform  
✅ Agent 3 - PWA  
✅ Agent 4 - API  
✅ Agent 5 - Database  
✅ Agent 6 - Performance  
✅ Agent 7 - Security  
✅ Agent 8 - Testing  
✅ Agent 9 - Debugging  
✅ Agent 10 - Chatbot (Five Tier AI)  
✅ Agent 11 - Interaction Engine  
✅ Agent 12 - Extensions  
✅ Agent 13 - Integration  
✅ Agent 14 - AI Router  
✅ Agent 15 - DevOps  
✅ Agent 16 - Configuration  
✅ Agent 17 - Migration  
✅ Agent 18 - Documentation  
✅ Agent 19 - Coordination (Router)  
✅ Agent 20 - Architecture  

Plus: **Claude Architect** (Oversight & Governance)

---

**[Pick an agent →](../entrance/chatgpt-start.md)**

*20 Specialized Agents + Claude Architect*  
*Building software systems together*
