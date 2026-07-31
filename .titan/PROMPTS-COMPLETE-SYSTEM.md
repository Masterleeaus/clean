# Complete Agent Specification System – Three Prompts

**Comprehensive system for documenting and deploying all 36 ChatGPT agents with full technical, operational, and workflow specifications.**

---

## Overview

This system uses **three complementary prompts** to generate complete documentation for a 36-agent system:

| Prompt | Focus | Output | Use Case |
|--------|-------|--------|----------|
| **PROMPT 1** | Technical Architecture | 36 technical specs | "How does Agent-01 work technically?" |
| **PROMPT 2** | Operations & Reliability | 36 operational specs | "How do we deploy and monitor Agent-01?" |
| **PROMPT 3** | Workflows & Coordination | 36 workflow guides | "How does Agent-01 accomplish its work?" |

---

## PROMPT 1: Technical Specifications

**File:** `PROMPT-1-TECHNICAL-SPECS.md`

**Generates:** 36 files named `Agent-XX-technical-spec.md`

**Covers:**
- Agent API contracts (input/output schemas)
- Data models and database schemas
- Integration points with other agents
- Message protocols (task, status, error, escalation)
- Validation and error handling
- Dependencies and constraints
- Performance characteristics
- Multi-tenancy and security

**Example Output:** Agent-01-technical-spec.md
```
# Agent-01: Workcore Agent – Technical Specification

## API Contract
### Input Schema
{
  "operation": "create_work_order",
  "company_id": "uuid",
  "payload": { "title": "...", "priority": "..." }
}

## Data Models
WorkOrder: id, company_id, title, status, due_date, ...

## Integration Points
- Agent-04 (API Agent) for REST endpoints
- Agent-05 (Database) for persistence
- Agent-13 (Integration) for WorkCore sync
```

**When to Use:** Understanding the technical architecture and data flow

---

## PROMPT 2: Operational Specifications

**File:** `PROMPT-2-OPERATIONAL-SPECS.md`

**Generates:** 36 files named `Agent-XX-operational-spec.md`

**Covers:**
- Performance requirements and SLAs
- Monitoring and observability
- Deployment specifications
- Reliability and failover procedures
- Scaling and load management
- Security and compliance operations
- Multi-tenancy data isolation
- Dependency management
- Cost management and optimization
- Operational runbooks
- Guild-specific operations
- Version management and upgrades

**Example Output:** Agent-01-operational-spec.md
```
# Agent-01: Workcore Agent – Operational Specification

## Performance Requirements
- Throughput: 10,000 orders/hour
- Latency p95: < 500ms
- Availability: 99.95%

## Deployment
- Containerized on Kubernetes
- Auto-scaling: CPU > 70% → scale out
- Rolling deployment with canary

## Reliability
- Circuit breaker for WorkCore API
- RTO: 15 minutes
- RPO: 5 minutes
```

**When to Use:** Planning deployment, setting SLAs, monitoring setup

---

## PROMPT 3: Comprehensive Workflows ⭐ NEW

**File:** `PROMPT-3-COMPREHENSIVE-AGENT-WORKFLOWS.md`

**Generates:** 36 files named `Agent-XX-comprehensive-workflow.md`

**Covers:**
- Agent profile and responsibilities
- **3-5 primary workflows** (step-by-step with tools)
- **Tool/plugin usage matrix** (which tools to use when)
- Coordination protocols (incoming/outgoing dependencies)
- **Real-world scenarios** (end-to-end examples)
- Guild-specific workflows
- **Tool mastery guidance** (proficiency levels)
- **Daily routine** for the agent
- Ready-to-work checklists

**Example Output:** Agent-01-comprehensive-workflow.md
```
# Agent-01: Workcore Agent – Comprehensive Workflow

## Profile
Role: Business operations specialist
Guild: Backend Specialists (Agents 1, 2, 4, 5)
Responsibilities: Work orders, business logic, WorkCore integration

## Primary Workflows

### Workflow 1: Create New Work Order Type
1. Use export-schemas to understand existing models
2. Use export-command-registry to see available commands
3. Use Superpowers plugin to design schema
4. Use Build MCP Apps to generate endpoints
5. Use CodeRabbit to review code quality
6. Use GitHub to push changes
7. Use test-capability to verify
8. Hand off to Agent-04 (API Agent) to expose endpoints
9. Hand off to Agent-08 (Testing) to write tests

## Tool/Plugin Usage Matrix

| Tool | Frequency | When to Use |
|------|-----------|------------|
| export-schemas | 10+ daily | Understand data models |
| export-command-registry | 5+ daily | Find available commands |
| Build MCP Apps | 3× weekly | Create new endpoints |
| CodeRabbit | With every code change | Validate quality |
| GitHub | 5+ daily | Push, review, manage code |

## Real-World Scenario: Emergency Work Order Type
Business needs to add "Emergency Plumbing" category...
[Full step-by-step example with all tools/plugins used]

## Tool Mastery
- export-schemas: Expert (10+ daily usage)
- CodeRabbit: Expert (quality checks)
- Build MCP Apps: Expert (endpoint generation)
```

**When to Use:** Understanding how agents actually accomplish their work

---

## The 10 Internal Tools

All agents can access these tools directly:

1. **analyze-structure** – Understand code organization, domains, extensions
2. **validate-extensions** – Check extension health
3. **export-command-registry** – Export WorkCore commands
4. **export-schemas** – Export data models and contracts
5. **validate-wizards** – Validate workflow definitions
6. **run-tests** – Check test infrastructure
7. **test-capability** – Test if a capability works
8. **audit-domain** – Audit domain health
9. **analyze-dependencies** – Check dependencies
10. **generate-docs** – Generate API documentation

---

## The 11 External Plugins

Agents can access these plugins for extended capabilities:

1. **GitHub** – Repository ops, code search, PR management
2. **CodeRabbit** – AI code review, bug detection
3. **Build Web Apps** – PWA/React scaffolding
4. **Build MCP Apps** – MCP server generation
5. **MiniUp** – Static site hosting
6. **Manufact** – MCP deployment, CI/CD
7. **Tavily AI** – Web research, site crawling
8. **Superpowers** – Architecture planning, TDD workflows
9. **Goodnotes** – Diagram generation
10. **Process Documentation AI** – SOP generation
11. **Hugging Face** – AI model discovery

---

## How to Use All Three Prompts

### Step 1: Generate Technical Specifications
```bash
# Give PROMPT-1 to Agent 1
Prompt: PROMPT-1-TECHNICAL-SPECS.md
Output: Agent-01-technical-spec.md through Agent-36-technical-spec.md
Save to: .titan/agent-specs/technical/
```

### Step 2: Generate Operational Specifications
```bash
# Give PROMPT-2 to Agent 2
Prompt: PROMPT-2-OPERATIONAL-SPECS.md
Output: Agent-01-operational-spec.md through Agent-36-operational-spec.md
Save to: .titan/agent-specs/operational/
```

### Step 3: Generate Comprehensive Workflows ⭐ NEW
```bash
# Give PROMPT-3 to Agent 3
Prompt: PROMPT-3-COMPREHENSIVE-AGENT-WORKFLOWS.md
Output: Agent-01-comprehensive-workflow.md through Agent-36-comprehensive-workflow.md
Save to: .titan/agent-specs/workflows/
```

### Step 4: Organize and Package
```bash
# Create directory structure
mkdir -p .titan/agent-specs/workflows

# Move workflow files
mv Agent-*-comprehensive-workflow.md .titan/agent-specs/workflows/

# Create comprehensive index
# Create manifest zip file with all specs

# Commit everything
git add .titan/agent-specs/
git commit -m "Add comprehensive technical, operational, and workflow specs for all 36 agents"
git push
```

---

## Complete Directory Structure

After all three prompts are complete:

```
.titan/
├── agent-manifests/ (36 manifests - existing)
│   ├── workcore-agent-manifest.md
│   ├── platform-agent-manifest.md
│   └── ... (36 total)
│
├── agent-specs/
│   ├── README.md
│   │
│   ├── technical/ (PROMPT 1 output)
│   │   ├── Agent-01-technical-spec.md
│   │   ├── Agent-02-technical-spec.md
│   │   └── ... (36 total)
│   │
│   ├── operational/ (PROMPT 2 output)
│   │   ├── Agent-01-operational-spec.md
│   │   ├── Agent-02-operational-spec.md
│   │   └── ... (36 total)
│   │
│   ├── workflows/ (PROMPT 3 output - NEW)
│   │   ├── Agent-01-comprehensive-workflow.md
│   │   ├── Agent-02-comprehensive-workflow.md
│   │   └── ... (36 total)
│   │
│   └── index/
│       ├── TECHNICAL-SPECS-INDEX.md
│       ├── OPERATIONAL-SPECS-INDEX.md
│       └── WORKFLOWS-INDEX.md (NEW)
│
└── agent-manifests-36-complete.zip (all specs + manifests)
```

---

## What Each Agent Needs

### For Understanding Their Role
→ **Manifest** (what job they do) + **Workflow** (how they do it)

### For Building Features
→ **Technical Spec** (API/data structures) + **Workflow** (step-by-step with tools)

### For Deployment & Operations
→ **Operational Spec** (SLAs, scaling) + **Workflow** (deployment steps)

### For Guild Coordination
→ **Workflow** (coordination protocols) + **Operational Spec** (guild SLAs)

---

## Real-World Agent Setup

### Agent-01 (Workcore) receives:
1. ✅ Manifest – "You're the business operations expert"
2. ✅ Technical Spec – "Here's your API contract and data model"
3. ✅ Operational Spec – "Here's your SLA and scaling rules"
4. ✅ Workflow – "Here's how you accomplish work with tools/plugins"

Agent now has **complete picture** of their role and toolkit.

---

## Key Differentiators

| Aspect | PROMPT 1 | PROMPT 2 | PROMPT 3 |
|--------|----------|----------|----------|
| **What does it define?** | Architecture | Operations | Execution |
| **Who reads it?** | Architects, Developers | DevOps, SREs | All Agents |
| **Focus** | APIs, schemas, integration | Deployment, monitoring, SLAs | Real-world workflows, tools |
| **Tools covered** | Data structures | Infrastructure | Tools + Plugins |
| **Real examples** | API contracts | Config files | Step-by-step scenarios |

---

## Complete System Benefits

✅ **Comprehensive Coverage:** Technical + Operational + Workflow = complete spec  
✅ **Agent Empowerment:** Agents know exactly what tools to use  
✅ **Clear Workflows:** Step-by-step examples for common tasks  
✅ **Tool Integration:** Shows how tools/plugins work together  
✅ **Guild Coordination:** Clear handoff and coordination protocols  
✅ **Production Ready:** All specs can be deployed immediately  
✅ **Scalable:** System works for 36 agents, easily extends to more

---

## Next Steps

1. **Generate PROMPT-1** (Technical) with first agent
2. **Generate PROMPT-2** (Operational) with second agent  
3. **Generate PROMPT-3** (Workflows) with third agent
4. **Organize** all outputs into directory structure
5. **Create index** documents linking all three
6. **Package** into agent-manifests-36-complete.zip
7. **Commit & push** to repository
8. **Distribute** to all 36 agents

---

## Status

**PROMPT-1:** ✅ Created (Technical Specifications)  
**PROMPT-2:** ✅ Created (Operational Specifications)  
**PROMPT-3:** ✅ Created (Comprehensive Workflows) ← NEW  

**System Status:** Ready to generate complete agent documentation

**Total Output:** 108 files
- 36 agent manifests (existing)
- 36 technical specs (PROMPT-1)
- 36 operational specs (PROMPT-2)
- 36 workflow guides (PROMPT-3)
- Index documents
- Master zip file

---

**All three prompts are production-ready and can be executed in parallel for faster generation.**

Use PROMPT-1 with Agent 1, PROMPT-2 with Agent 2, PROMPT-3 with Agent 3 simultaneously.

---

*Complete Agent Specification System*  
*36 agents × 3 spec types = comprehensive multi-agent documentation*
