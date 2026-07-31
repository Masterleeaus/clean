# 📑 Comprehensive Workflows Index

**Complete workflow documentation for all 36 agents showing practical tool and plugin usage**

---

## Overview

Comprehensive workflow specifications show **how agents actually accomplish work** using the 10 internal tools and 11 external plugins.

These specs answer:
- **How does the agent accomplish tasks?** (step-by-step workflows)
- **What tools/plugins does it use?** (and when)
- **How do workflows coordinate?** (agent-to-agent handoffs)
- **What are real-world examples?** (concrete scenarios)
- **What is the agent's daily routine?** (operational cadence)

---

## Available Tools & Plugins

### 10 Internal Tools (All Agents Can Access)
1. **analyze-structure** – Understand repository layout, domains, extensions
2. **validate-extensions** – Check extension health and validity
3. **export-command-registry** – Export all WorkCore commands and schemas
4. **export-schemas** – Export data models and contracts
5. **validate-wizards** – Validate wizard/workflow definitions
6. **run-tests** – Discover and check test infrastructure
7. **test-capability** – Test if a specific capability exists and works
8. **audit-domain** – Audit domain health and structure
9. **analyze-dependencies** – Check PHP/Node dependencies
10. **generate-docs** – Generate API documentation and OpenAPI specs

### 11 External Plugins (Agents Can Access)
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

## All 36 Comprehensive Workflows

### Original 20 Agents

| # | Agent | File | Status | Primary Workflows |
|---|-------|------|--------|-------------------|
| 01 | Workcore Agent | `Agent-01-comprehensive-workflow.md` | Pending | Create work order type, Process work order, Integrate with WorkCore |
| 02 | Platform Agent | `Agent-02-comprehensive-workflow.md` | Pending | System architecture review, Infrastructure planning, Cross-domain coordination |
| 03 | PWA Agent | `Agent-03-comprehensive-workflow.md` | Pending | PWA deployment, Offline sync setup, Performance optimization |
| 04 | API Agent | `Agent-04-comprehensive-workflow.md` | Pending | Design REST endpoint, Create GraphQL schema, Generate API docs |
| 05 | Database Agent | `Agent-05-comprehensive-workflow.md` | Pending | Design data model, Create migration, Validate schema |
| 06 | Performance Agent | `Agent-06-comprehensive-workflow.md` | Pending | Performance audit, Optimization planning, Benchmark comparison |
| 07 | Security Agent | `Agent-07-comprehensive-workflow.md` | Pending | Security audit, Vulnerability assessment, Compliance check |
| 08 | Testing Agent | `Agent-08-comprehensive-workflow.md` | Pending | Write test suite, Validate coverage, QA sign-off |
| 09 | Debugging Agent | `Agent-09-comprehensive-workflow.md` | Pending | Root cause analysis, Bug reproduction, Fix validation |
| 10 | Chatbot Agent | `Agent-10-comprehensive-workflow.md` | Pending | Configure AI runtime, Add intent handler, Train model |
| 11 | Interaction Engine Agent | `Agent-11-comprehensive-workflow.md` | Pending | Design wizard, Create workflow, Integrate engine |
| 12 | Extensions Agent | `Agent-12-comprehensive-workflow.md` | Pending | Validate extension, Publish to marketplace, Monitor health |
| 13 | Integration Agent | `Agent-13-comprehensive-workflow.md` | Pending | Connect external API, Set up webhook, Test integration |
| 14 | AI Router Agent | `Agent-14-comprehensive-workflow.md` | Pending | Model selection logic, Cost optimization, Performance routing |
| 15 | DevOps Agent | `Agent-15-comprehensive-workflow.md` | Pending | CI/CD setup, Deployment automation, Infrastructure management |
| 16 | Configuration Agent | `Agent-16-comprehensive-workflow.md` | Pending | Environment setup, Feature flags, Config management |
| 17 | Migration Agent | `Agent-17-comprehensive-workflow.md` | Pending | Schema migration, Data transformation, Rollback planning |
| 18 | Documentation Agent | `Agent-18-comprehensive-workflow.md` | Pending | API documentation, User guides, Architecture diagrams |
| 19 | Coordination Agent | `Agent-19-comprehensive-workflow.md` | Pending | Task routing, Multi-agent dispatch, Escalation handling |
| 20 | Architecture Agent | `Agent-20-comprehensive-workflow.md` | Pending | System design, Refactoring planning, Technical decisions |

### PWA Specialists Guild (21-36)

| # | Agent | File | Status | Primary Workflows |
|---|-------|------|--------|-------------------|
| 21 | PWA Designer Agent | `Agent-21-comprehensive-workflow.md` | Pending | Design system creation, Accessibility audit, Component library |
| 22 | PWA UI Agent | `Agent-22-comprehensive-workflow.md` | Pending | Component implementation, Responsive design, Theme system |
| 23 | Titan Go Agent | `Agent-23-comprehensive-workflow.md` | Pending | Field tech operations, GPS tracking, Offline sync |
| 24 | Titan Dispatch Agent | `Agent-24-comprehensive-workflow.md` | Pending | Schedule optimization, Route planning, Conflict resolution |
| 25 | Titan Hub Agent | `Agent-25-comprehensive-workflow.md` | Pending | Customer service, Booking management, Service requests |
| 26 | Titan Money Agent | `Agent-26-comprehensive-workflow.md` | Pending | Invoice generation, Payment tracking, Tax compliance |
| 27 | Titan Teams Agent | `Agent-27-comprehensive-workflow.md` | Pending | HR workflows, Permission management, Workforce planning |
| 28 | Titan Locker Agent | `Agent-28-comprehensive-workflow.md` | Pending | Inventory management, Barcode scanning, Stock tracking |
| 29 | Titan Analytics Agent | `Agent-29-comprehensive-workflow.md` | Pending | Dashboard creation, Report generation, Data analysis |
| 30 | Titan Front Desk Agent | `Agent-30-comprehensive-workflow.md` | Pending | Phone management, Reception tasks, Appointment scheduling |
| 31 | Titan Marketing Agent | `Agent-31-comprehensive-workflow.md` | Pending | Campaign planning, Multi-channel execution, Analytics tracking |
| 32 | Titan Social Agent | `Agent-32-comprehensive-workflow.md` | Pending | Social media management, Engagement tracking, Content scheduling |
| 33 | Titan Office Agent | `Agent-33-comprehensive-workflow.md` | Pending | Document management, Collaboration setup, Resource allocation |
| 34 | Titan Quality Agent | `Agent-34-comprehensive-workflow.md` | Pending | Quality audits, Compliance checking, Standards enforcement |
| 35 | Titan Sprout Agent | `Agent-35-comprehensive-workflow.md` | Pending | Lead generation, CRM management, Sales funnel tracking |
| 36 | Chatbot PWA Agent | `Agent-36-comprehensive-workflow.md` | Pending | Multi-channel AI, WhatsApp integration, Message routing |

---

## Workflow Categories

### Common Workflow Patterns

**Technical Implementation Workflows** (Agents 01-05, 12-14, 17-18)
- Use export-schemas to understand existing models
- Use Build MCP Apps to generate endpoints
- Use CodeRabbit for quality gates
- Use GitHub for code management
- Use test-capability to verify

**Frontend/PWA Workflows** (Agents 03, 21-36)
- Use Goodnotes for design documentation
- Use Build Web Apps for component scaffolding
- Use CodeRabbit for component review
- Use Manufact for deployment
- Use GitHub for version control

**Operations/Deployment Workflows** (Agents 15-16)
- Use Manufact for CI/CD orchestration
- Use GitHub for deployment tracking
- Use test-capability for validation
- Use analyze-structure for environment setup
- Use export-command-registry for command validation

**Analysis/Planning Workflows** (Agents 06-07, 09, 20)
- Use analyze-structure for codebase understanding
- Use audit-domain for health checks
- Use Superpowers for architecture planning
- Use Tavily AI for research
- Use Goodnotes for diagram generation

**Integration/Coordination Workflows** (Agents 13, 19)
- Use export-schemas for contract discovery
- Use GitHub for API documentation
- Use Tavily AI for external API research
- Use test-capability for integration testing
- Use Process Documentation AI for SOP generation

---

## Guild-Level Workflows

### Backend Specialists Guild (Agents 01, 02, 04, 05)
Weekly syncs coordinate:
- Data model evolution
- API contract alignment
- Database migration planning
- Integration dependencies

### Frontend Specialists Guild (Agents 03, 21, 22)
Ongoing collaboration on:
- Design system governance
- Component library maintenance
- Accessibility standards (WCAG AA)
- Cross-app UI consistency

### PWA Specialists Guild (Agents 21-36)
Coordinated workflows for:
- 14 PWA applications (Agents 23-36)
- Design & UI support (Agents 21-22)
- Offline-first architecture
- Multi-channel synchronization

### DevOps Guild (Agents 15-17)
Infrastructure coordination:
- Deployment sequencing
- Configuration management
- Migration oversight
- Incident response

---

## How to Use These Workflows

1. **For a new task:** Find the agent responsible in the manifest
2. **Reference their workflow:** Look up their comprehensive workflow file
3. **Follow the steps:** Use the tools/plugins in the documented sequence
4. **Coordinate handoffs:** Refer to the coordination protocols for next agent
5. **Document decisions:** Log any custom adaptations for this company_id

---

## Next Steps

These workflow specifications will be generated using **PROMPT-3-COMPREHENSIVE-AGENT-WORKFLOWS.md**.

Each specification will include:
- Agent profile and responsibilities
- 3-5 primary workflows with detailed steps
- Tool/plugin usage matrix showing frequency and use cases
- Coordination protocols for agent-to-agent handoffs
- 2-3 real-world scenario examples
- Guild-specific workflows and best practices
- Tool mastery guidance for each agent
- Daily routine and ready-to-work checklists

---

**Status:** Specifications ready for generation  
**Format:** Markdown (.md files)  
**Total Specs:** 36 comprehensive workflow files  
**Prompt Used:** PROMPT-3-COMPREHENSIVE-AGENT-WORKFLOWS.md

---

*Comprehensive Agent Workflow Specifications – How 36 agents use 21 tools and plugins to accomplish real work*
