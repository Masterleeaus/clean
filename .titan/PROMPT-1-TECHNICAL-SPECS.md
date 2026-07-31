# PROMPT 1: Technical Specifications for All 36 Agents

## Task
Create detailed TECHNICAL SPECIFICATIONS for all 36 ChatGPT agents in a sophisticated multi-agent system.

## System Context

The system has 36 specialized agents organized in 11 guilds:

### Original 20 Agents (Agents 1-20)
1. Agent-01: Workcore Agent (Business operations, WorkCore integration)
2. Agent-02: Platform Agent (Core infrastructure & systems)
3. Agent-03: PWA Agent (Frontend/React, progressive web apps)
4. Agent-04: API Agent (REST & GraphQL endpoints)
5. Agent-05: Database Agent (Data models, schemas, migrations)
6. Agent-06: Performance Agent (Speed optimization, benchmarking)
7. Agent-07: Security Agent (Security audits, compliance)
8. Agent-08: Testing Agent (QA automation, test coverage)
9. Agent-09: Debugging Agent (Root cause analysis, bug fixes)
10. Agent-10: Chatbot Agent (Five Tier AI runtime with 140+ internal agents, voice/text)
11. Agent-11: Interaction Engine Agent (Wizards, workflows, 80-engine library, LocalBrain)
12. Agent-12: Extensions Agent (Plugin ecosystem, marketplace)
13. Agent-13: Integration Agent (Third-party APIs, webhooks)
14. Agent-14: AI Router Agent (Model selection, cost optimization)
15. Agent-15: DevOps Agent (CI/CD pipelines, deployment)
16. Agent-16: Configuration Agent (Environment settings, feature flags)
17. Agent-17: Migration Agent (Database migrations, schema changes)
18. Agent-18: Documentation Agent (Technical writing, guides)
19. Agent-19: Coordination Agent (Task routing, multi-agent dispatch)
20. Agent-20: Architecture Agent (System design, refactoring)

### New 16 PWA Specialists (Agents 21-36)
21. Agent-21: PWA Designer Agent (Design systems, UX, accessibility WCAG AA)
22. Agent-22: PWA UI Agent (Components, responsive, theming)
23. Agent-23: Titan Go Agent (Field technician operations, GPS, offline)
24. Agent-24: Titan Dispatch Agent (Real-time scheduling, routing)
25. Agent-25: Titan Hub Agent (Customer service, booking)
26. Agent-26: Titan Money Agent (Financial management, invoicing)
27. Agent-27: Titan Teams Agent (HR, workforce management)
28. Agent-28: Titan Locker Agent (Inventory, barcode scanning)
29. Agent-29: Titan Analytics Agent (Business intelligence, dashboards)
30. Agent-30: Titan Front Desk Agent (Reception, scheduling)
31. Agent-31: Titan Marketing Agent (Multi-channel campaigns)
32. Agent-32: Titan Social Agent (Social media management)
33. Agent-33: Titan Office Agent (Document management, collaboration)
34. Agent-34: Titan Quality Agent (Quality audits, compliance)
35. Agent-35: Titan Sprout Agent (Lead generation, CRM)
36. Agent-36: Chatbot PWA Agent (Multi-channel AI: WhatsApp, Telegram, Instagram, SMS)

## What to Specify for Each Agent

For each of the 36 agents, create a technical specification document that covers:

### 1. Agent API Contract
- **Input Schema:** What data/messages the agent accepts (JSON examples)
- **Output Schema:** What the agent returns (JSON examples)
- **Methods/Endpoints:** Available operations the agent can perform
- **Request/Response Examples:** Real-world usage examples

### 2. Data Models
- **Core Data Structures:** Key entities the agent works with
- **Database Schema:** Tables/collections (if applicable)
- **Data Relationships:** How entities relate to each other
- **State Management:** How agent maintains state across operations

### 3. Integration Points
- **Agent-to-Agent Communication:** Which other agents this agent coordinates with
- **External System APIs:** External services this agent calls
- **Event Subscriptions:** Events this agent listens to
- **Message Brokers:** Any message queue integrations

### 4. Message Protocols
- **Task Message Format:** Structure of incoming task messages
- **Status Update Format:** How agent reports progress
- **Error Message Format:** Error response structure
- **Escalation Message Format:** How agent escalates to humans/Claude

### 5. Validation & Error Handling
- **Input Validation Rules:** What makes a valid request
- **Error Codes:** All possible error codes and meanings
- **Retry Logic:** When to retry, exponential backoff, max attempts
- **Fallback Strategies:** What to do if external service is unavailable

### 6. Dependencies & Constraints
- **Required Services:** What services must be available
- **Multi-Tenancy:** company_id scoping requirements
- **Rate Limiting:** API rate limits (if applicable)
- **Timeouts:** Max execution time per operation
- **Resource Limits:** Memory, CPU requirements

### 7. Performance Characteristics
- **Expected Throughput:** Tasks per hour/minute
- **Latency Targets:** p50, p95, p99 latency
- **Concurrency:** Max parallel operations

### 8. Multi-Tenancy & Security
- **Tenant Isolation:** How company_id is enforced
- **Data Scoping:** What data access rules apply
- **Permission Checks:** Authorization requirements
- **Audit Logging:** What operations must be logged

### 9. Available Tools & Actions
Document which of the 10 available tools each agent uses and how:

**10 Available Tools (All Agents Can Access):**
1. **analyze-structure** – Analyze repository structure, domains, extensions
2. **validate-extensions** – Validate all extensions in the repository
3. **export-command-registry** – Export all WorkCore commands and schemas
4. **export-schemas** – Export domain data schemas and contracts
5. **validate-wizards** – Validate wizard/workflow definitions
6. **run-tests** – Check test suite availability and configuration
7. **test-capability** – Test if a specific WorkCore capability exists
8. **audit-domain** – Audit a specific domain for health/structure
9. **analyze-dependencies** – Analyze PHP and Node dependencies
10. **generate-docs** – Generate API reference documentation

**For each agent, specify:**
- **Primary tools:** 2-3 tools most frequently used by this agent
- **Secondary tools:** 1-2 tools occasionally used
- **When to use:** Specific scenarios where agent would use each tool
- **Example usage:** Concrete example of how agent applies the tool

**Example (Agent-01 Workcore Agent):**
- Primary: export-command-registry (find WorkCore commands), export-schemas (understand data models)
- Secondary: test-capability (verify capabilities exist), audit-domain (audit WorkCore domain health)
- Usage: "When creating new work order type, use export-schemas to understand existing models, then export-command-registry to see available commands"

## Output Format

Create 36 technical specification documents in markdown format.

**Filename pattern:** `Agent-XX-technical-spec.md` (e.g., `Agent-01-technical-spec.md`)

**Structure for each document:**
```
# Agent-XX: [Agent Name] - Technical Specification

## Overview
- Role: [Agent role]
- Domain: [Domain]
- Guild: [Guild name]

## API Contract
### Input Schema
[JSON schema or example]

### Output Schema
[JSON schema or example]

### Operations/Methods
[List of available operations]

## Data Models
[Core entities and their structure]

## Integration Points
### Agent Dependencies
[List of agents this coordinates with]

### External APIs
[External systems this calls]

### Events
[Events published/subscribed]

## Message Protocols
[Formats for task, status, error, escalation]

## Validation & Error Handling
[Input validation rules, error codes]

## Dependencies & Constraints
[Required services, rate limits, timeouts]

## Performance
[Throughput, latency targets]

## Multi-Tenancy
[Tenant isolation, company_id scoping]

## Available Tools & Actions
### Primary Tools
[2-3 main tools this agent uses and why]

### Secondary Tools
[1-2 occasional tools]

### Tool Usage Examples
[Concrete scenarios showing how agent uses each tool]
```

## Critical Requirements

1. **Multi-Tenancy Enforcement:** ALL operations must scope to company_id
2. **Agent Coordination:** Document all agent-to-agent dependencies
3. **External Integrations:** Specify all external API calls with authentication
4. **Data Models:** Include validation rules and constraints
5. **Error Handling:** Be comprehensive with error codes and recovery
6. **Performance:** Include realistic targets based on agent complexity
7. **Security:** Specify all security/authorization requirements

## Delivery

Output all 36 technical specifications. You can output them as:
1. **One at a time** (36 separate outputs, one per agent)
2. **In groups** (Groups of 5-10 agents per output)

**Start with Agent-01 and proceed sequentially through Agent-36.**

Each output should have clear filename and complete specification content ready to be saved as a markdown file.
