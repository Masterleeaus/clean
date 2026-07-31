# PROMPT 3: Comprehensive Agent Workflows with Plugins & Tools

**For all 36 agents in the system: Complete workflow documentation including internal tools, external plugins, and real-world task examples.**

---

## Overview

This prompt generates comprehensive workflow documentation for all 36 agents. Each agent specification includes:

1. **Agent Profile** – Role, domain, guild, responsibilities
2. **Available Internal Tools** – 10 actions they can invoke
3. **Available External Plugins** – 11 plugins they can access
4. **Primary Workflows** – Step-by-step task examples
5. **Tool/Plugin Matrix** – When to use each tool/plugin
6. **Coordination Protocols** – How they work with other agents
7. **Real-World Examples** – Concrete scenarios and solutions

---

## System Context

### 36 Agents in 11 Guilds

**Original 20 Agents:**
1. Agent-01: Workcore Agent
2. Agent-02: Platform Agent
3. Agent-03: PWA Agent
4. Agent-04: API Agent
5. Agent-05: Database Agent
6. Agent-06: Performance Agent
7. Agent-07: Security Agent
8. Agent-08: Testing Agent
9. Agent-09: Debugging Agent
10. Agent-10: Chatbot Agent
11. Agent-11: Interaction Engine Agent
12. Agent-12: Extensions Agent
13. Agent-13: Integration Agent
14. Agent-14: AI Router Agent
15. Agent-15: DevOps Agent
16. Agent-16: Configuration Agent
17. Agent-17: Migration Agent
18. Agent-18: Documentation Agent
19. Agent-19: Coordination Agent
20. Agent-20: Architecture Agent

**PWA Specialists (16 agents):**
21. Agent-21: PWA Designer Agent
22. Agent-22: PWA UI Agent
23-36: Titan app specialists (Go, Dispatch, Hub, Money, Teams, Locker, Analytics, Front Desk, Marketing, Social, Office, Quality, Sprout, Chatbot PWA)

---

## 10 Internal Tools (All Agents Can Access)

1. **analyze-structure** – Understand repository layout, domains, extensions, code metrics
2. **validate-extensions** – Check extension health and validity
3. **export-command-registry** – Export all WorkCore commands and schemas
4. **export-schemas** – Export data models and contracts
5. **validate-wizards** – Validate wizard/workflow definitions
6. **run-tests** – Discover and check test infrastructure
7. **test-capability** – Test if a specific capability exists and works
8. **audit-domain** – Audit domain health and structure
9. **analyze-dependencies** – Check PHP/Node dependencies
10. **generate-docs** – Generate API documentation and OpenAPI specs

---

## 11 External Plugins (Agents Can Access)

1. **GitHub** – Repository operations, code search, PR/issue management
2. **CodeRabbit** – AI code review, bug detection, test validation
3. **Build Web Apps** – React/Vue PWA scaffolding, responsive design
4. **Build MCP Apps** – MCP server generation, tool creation
5. **MiniUp** – Static site hosting, ZIP publishing, API hosting
6. **Manufact** – MCP deployment, CI/CD, cross-client testing
7. **Tavily AI** – Web research, site crawling, document extraction
8. **Superpowers** – Architecture planning, TDD workflows, process design
9. **Goodnotes** – Diagram generation, flowcharts, UML diagrams
10. **Process Documentation AI** – SOP generation, workflow documentation
11. **Hugging Face** – AI model discovery, dataset exploration

---

## What to Generate for Each Agent

### 1. Agent Profile
- **Name & ID:** Agent-XX: [Name]
- **Role:** Primary responsibility in the system
- **Domain:** Technical domain (backend, frontend, AI, etc.)
- **Guild:** Which guild they belong to
- **Guild Lead:** Who they report to
- **Team Members:** Other agents in their guild
- **Responsibility Summary:** 2-3 sentence overview

### 2. Primary Workflows
For each agent, document 3-5 primary workflows:

**Workflow Template:**
```
Workflow: [Name]
Trigger: [What initiates this workflow]
Goal: [What the agent is trying to accomplish]
Steps:
  1. [Step with tool/plugin used]
  2. [Step with tool/plugin used]
  3. [Continue steps...]
Completion Criteria: [How agent knows it's done]
Next Agent: [Who to hand off to]
```

**Example - Agent-01 (Workcore Agent):**

*Workflow: Create New Work Order Type*
- Trigger: Business requests new work order category (e.g., "Emergency Plumbing")
- Goal: Design and implement new work order type with all validations
- Steps:
  1. Use **export-schemas** to understand existing work order models
  2. Use **export-command-registry** to see available WorkCore commands
  3. Use **Superpowers** plugin to design new type schema and API
  4. Use **Build MCP Apps** to generate new MCP endpoints
  5. Use **test-capability** to verify new endpoints work
  6. Use **generate-docs** to auto-document new type
  7. Coordinate with Agent-04 (API Agent) to expose endpoints
  8. Coordinate with Agent-08 (Testing Agent) to write tests
- Completion: New work order type deployed, documented, tested
- Next Agent: Agent-15 (DevOps Agent) for deployment

### 3. Tool/Plugin Usage Matrix
For each agent, specify:

| Tool/Plugin | Frequency | Use Case | Example |
|---|---|---|---|
| analyze-structure | Occasional | Understanding codebase layout | "Where is WorkCore domain defined?" |
| export-schemas | Frequent | Understand data models | "Show me work order schema" |
| Build MCP Apps | Primary | Create new endpoints | "Generate order processing endpoint" |
| CodeRabbit | Primary | Validate code quality | "Review my order validation code" |
| GitHub | Frequent | Push changes, see history | "Find where job scheduling logic is" |

### 4. Coordination Protocols
For each agent, document:

**Incoming Dependencies:** Which agents call on this agent?
```
Agent-02 → Agent-01: "Create platform status report"
Agent-19 → Agent-01: "Process work order from field agent"
Agent-15 → Agent-01: "Validate work order schema before deploy"
```

**Outgoing Dependencies:** Which agents does this agent call?
```
Agent-01 → Agent-04: "Expose work order endpoints"
Agent-01 → Agent-05: "Migrate work order schema"
Agent-01 → Agent-13: "Integrate with external system"
```

**Escalation Path:**
```
Normal issue → Guild Lead (Agent-XX)
Performance issue → Agent-06 (Performance Agent)
Data issue → Agent-05 (Database Agent)
Security issue → Agent-07 (Security Agent)
Architectural issue → Agent-20 (Architecture Agent)
Critical issue → Claude Architect
```

### 5. Real-World Workflows

For each agent, provide 2-3 concrete end-to-end scenarios:

**Scenario 1: Bug in Work Order Creation**

*Triggering Event:* User reports work orders not saving

*Agent Flow:*
1. Agent-09 (Debugging) detects issue, uses **test-capability** to confirm
2. Agent-09 uses **audit-domain** to check WorkCore domain health
3. Agent-09 uses **export-schemas** to verify schema is valid
4. Agent-09 coordinates with Agent-01 to investigate business logic
5. Agent-01 uses **analyze-structure** to find work order handler
6. Agent-01 uses **CodeRabbit** plugin to review work order creation code
7. Agent-01 identifies issue: missing validation
8. Agent-01 uses **Build MCP Apps** to fix endpoint
9. Agent-01 coordinates with Agent-08 (Testing) to add test case
10. Agent-08 uses **run-tests** to verify fix
11. Agent-01 coordinates with Agent-15 (DevOps) to deploy
12. Agent-15 uses **Manufact** plugin to deploy with auto-testing
13. Agent-09 verifies issue resolved

**Scenario 2: New Feature - Recurring Work Orders**

*Triggering Event:* Business wants recurring work order capability

*Agent Flow:*
1. Agent-20 (Architecture) uses **Superpowers** to design feature
2. Agent-01 uses **export-schemas** to understand current model
3. Agent-01 uses **Build MCP Apps** to scaffold recurring order endpoints
4. Agent-05 uses **analyze-dependencies** to understand tech stack
5. Agent-05 designs schema migration
6. Agent-17 (Migration) uses **run-tests** to validate migration
7. Agent-04 uses **generate-docs** to create API documentation
8. Agent-21 (PWA Designer) uses **Goodnotes** to diagram feature flow
9. Agent-22 (PWA UI) uses **Build Web Apps** to create UI for recurring setup
10. Agent-08 uses **CodeRabbit** to review all code
11. Agent-15 uses **Manufact** to deploy with canary rollout
12. Agent-18 uses **Process Documentation AI** to create user guide

### 6. Guild-Specific Workflows

Each guild has coordinated workflows:

**Backend Specialists Guild (Agents 1, 2, 4, 5):**
- Weekly sync on data models
- Coordinate API contracts
- Share database migration plans
- Joint security reviews with Agent-07

**Frontend Specialists Guild (Agents 3, 21, 22):**
- Design system governance
- Component library maintenance
- Accessibility audits (WCAG AA)
- Cross-app UI consistency

**DevOps Guild (Agents 15, 16, 17):**
- Deployment coordination
- Infrastructure changes
- Scaling decisions
- Incident response

### 7. Tool Mastery Guidance

For each agent, show mastery progression:

**Agent-01 (Workcore) Tool Mastery:**
- **export-schemas** – Used 10+ times daily; expert in WorkCore data model
- **export-command-registry** – Used 5+ times daily; knows all available commands
- **Build MCP Apps** – Used 3+ times weekly; can generate production-ready endpoints
- **CodeRabbit** – Used for all code; catches business logic errors
- **GitHub** – Used daily for work order history, blame tracking
- **Superpowers** – Used in planning phase for complex features
- **test-capability** – Used to verify new endpoints before deployment
- **Tavily AI** – Used occasionally for regulatory research (WHS, insurance, etc.)

---

## Workflow Execution Rules

### 1. Tool Selection Rules

**Use Internal Tools When:**
- Exploring codebase (analyze-structure, audit-domain)
- Understanding data models (export-schemas, export-command-registry)
- Validating code (validate-extensions, validate-wizards)
- Running tests (run-tests, test-capability)
- Creating documentation (generate-docs)

**Use External Plugins When:**
- Doing code review (CodeRabbit)
- Building UIs (Build Web Apps)
- Creating backends (Build MCP Apps)
- Deploying (Manufact)
- Researching external info (Tavily AI)
- Planning architecture (Superpowers)
- Creating diagrams (Goodnotes)
- Writing procedures (Process Documentation AI)

### 2. Coordination Rules

**Always Coordinate When:**
- Changing shared data structures (with Agent-05)
- Creating new APIs (with Agent-04)
- Modifying tests (with Agent-08)
- Deploying to production (with Agent-15)
- Security implications (with Agent-07)
- Performance implications (with Agent-06)

**Escalate When:**
- Issue affects multiple guilds
- Requires architectural decision
- Blocks multiple agents
- Has security/compliance impact
- Goes beyond agent authority

### 3. Handoff Rules

**Handoff Sequence:**
1. Document current state using internal tools
2. Create artifact/output for next agent
3. Use GitHub plugin to push code/changes
4. Notify next agent with clear context
5. Next agent verifies handoff completeness

---

## Multi-Tenancy in Workflows

**Every workflow must:**
- Start with company_id validation
- Scope all data access to company_id
- Use GitHub to search code (no manual file access)
- Document company-specific decisions
- Escalate cross-tenant issues to Agent-19

---

## Real-World Agent Personas

### Agent-01: Workcore Agent
"I'm the business logic expert. When you need work orders created, updated, or synchronized with WorkCore, I coordinate the effort. I use export-schemas and export-command-registry constantly to understand what's possible. When I build new features, I use Build MCP Apps to scaffold endpoints, CodeRabbit to ensure quality, and GitHub to manage the code. I escalate to Architecture when design questions come up, and to DevOps when it's time to ship."

### Agent-04: API Agent
"I'm the contract keeper. Every API endpoint must be well-designed and documented. I use export-command-registry to see what commands exist, generate-docs to create API specs, and CodeRabbit to enforce standards. I work closely with Agents 1 and 2 to understand backend logic, and with Agent-21/22 to ensure frontends have what they need. Manufact is my deployment partner."

### Agent-15: DevOps Agent
"I own the deployment pipeline. I use Manufact to manage CI/CD for all MCP servers. I coordinate with the DevOps guild on infrastructure changes. I use GitHub to track deployments, CodeRabbit to gate changes, and test-capability to verify everything works. I escalate infrastructure decisions to Agent-20 (Architecture)."

### Agent-21: PWA Designer
"I ensure design consistency across all 14 PWA applications. I use Goodnotes to create design system documentation, Build Web Apps to prototype components, and Superpowers to plan design architecture. I coordinate with Agents 22-36 (my PWA app specialists) on design implementation. I escalate accessibility issues and cross-app inconsistencies to my guild lead."

---

## Output Format

Generate comprehensive workflow documentation for all 36 agents in markdown format.

**Filename pattern:** `Agent-XX-comprehensive-workflow.md`

**Structure:**
```
# Agent-XX: [Agent Name] - Comprehensive Workflow

## Profile
[Role, domain, guild, responsibilities]

## Primary Workflows
[3-5 detailed workflows with steps]

## Tool/Plugin Usage Matrix
[Table showing frequency and use cases]

## Coordination Protocols
[Incoming/outgoing dependencies, escalation]

## Real-World Scenarios
[2-3 end-to-end concrete examples]

## Guild Operations
[How this agent works within their guild]

## Tool Mastery
[Proficiency levels and use frequency]

## Daily Routine
[Typical day workflow for this agent]

## Checklist: Ready to Work?
[What this agent needs to know]
```

---

## Critical Requirements

1. **Concrete Examples:** Every workflow must have real-world scenarios
2. **Tool Integration:** Show HOW to use tools, not just THAT they exist
3. **Plugin Coordination:** Show which plugins work together
4. **Multi-Tenancy:** Every workflow scopes to company_id
5. **Clear Handoffs:** Every workflow shows next agent and context
6. **Escalation Paths:** Document when and how to escalate
7. **Guild Integration:** Show how agent fits in their guild
8. **Dependencies:** Document all agent-to-agent and plugin dependencies
9. **Error Handling:** Include what to do when things fail
10. **Automation:** Show opportunities for automation within workflows

---

## Delivery

Output comprehensive workflow documentation for all 36 agents.

**Format Options:**
1. One agent at a time (36 separate outputs)
2. In groups by guild (5-10 agents per output)

**Start with Agent-01 and proceed through Agent-36.**

Each output should be production-ready, with:
- Clear step-by-step workflows
- Specific tool/plugin usage
- Real-world examples
- Ready-to-reference checklists

---

## Complementary Documentation

This workflow prompt works alongside:
- **PROMPT-1:** Technical Specifications (API contracts, data models)
- **PROMPT-2:** Operational Specifications (performance, SLAs, deployment)
- **PROMPT-3:** Comprehensive Workflows (this prompt – how agents actually work)

Together, all three provide complete documentation for the 36-agent system.

---

**Status:** Ready for generation  
**Agents:** 36 (20 original + 16 PWA specialists)  
**Scope:** Complete workflows with tools, plugins, and real-world examples  

Generate comprehensive, production-ready workflow documentation for all 36 agents that shows exactly how they use tools and plugins to accomplish their work.
