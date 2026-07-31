# PROMPT 2: Operational Specifications for All 36 Agents

## Task
Create detailed OPERATIONAL SPECIFICATIONS for all 36 ChatGPT agents in a sophisticated multi-agent system.

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

For each of the 36 agents, create an operational specification document that covers:

### 1. Performance Requirements & SLAs
- **Throughput Targets:** Tasks/requests per hour, per day
- **Latency SLAs:** p50, p95, p99 response times
- **Availability Target:** Uptime percentage (e.g., 99.9%)
- **Concurrent Capacity:** Maximum parallel operations
- **Resource Allocation:** CPU, memory, storage requirements
- **Scalability Profile:** How agent scales with load

### 2. Monitoring & Observability
- **Key Metrics:** What to measure (latency, errors, throughput)
- **Health Checks:** Ping endpoints, dependency verification
- **Alerting Thresholds:** When to alert on metric violations
- **Logging Requirements:** What to log and at what level
- **Tracing Strategy:** Request tracing across agent boundaries
- **Dashboards:** Key visualizations for agent health

### 3. Deployment Specifications
- **Deployment Model:** Containerized, serverless, VM-based
- **Container/Image Details:** Docker image specs, versioning
- **Environment Variables:** Required env vars for operation
- **Configuration Files:** Config files needed at startup
- **Startup Sequence:** Initialization steps and dependencies
- **Graceful Shutdown:** Cleanup procedures
- **Rolling Deployment Strategy:** Blue-green, canary, rolling update

### 4. Reliability & Failover
- **Failure Modes:** Expected failure scenarios
- **Recovery Procedures:** How to recover from failures
- **Circuit Breaker Patterns:** When to stop trying failed operations
- **Fallback Services:** What to do when this agent fails
- **Data Durability:** Persistence and backup requirements
- **Replication Strategy:** If applicable, replication setup
- **Recovery Time Objective (RTO):** Target recovery time
- **Recovery Point Objective (RPO):** Maximum acceptable data loss

### 5. Scaling & Load Management
- **Auto-Scaling Rules:** When to scale up/down
- **Load Balancing:** How requests are distributed
- **Rate Limiting:** Per-tenant, per-user, or global limits
- **Queue Depth:** Max queue size before backpressure
- **Peak Load Capacity:** Expected maximum concurrent load
- **Burst Handling:** How to handle sudden traffic spikes

### 6. Security & Compliance Operations
- **Authentication:** Token validation, session management
- **Authorization:** Permission verification per operation
- **Audit Logging:** What operations must be audit-logged
- **Encryption in Transit:** TLS/SSL requirements
- **Encryption at Rest:** Data encryption requirements
- **Compliance Audits:** Regular security/compliance checks
- **Secret Management:** How secrets are stored/rotated
- **Vulnerability Scanning:** Regular scanning schedule

### 7. Multi-Tenancy & Data Isolation
- **Tenant Routing:** How requests route to correct tenant
- **Data Isolation Verification:** How to verify no data leakage
- **Tenant-specific SLAs:** SLA differences between tier/tenant
- **Cross-Tenant Escalation:** Procedure for tenant support issues
- **Data Cleanup:** Tenant data deletion procedures
- **Audit Trails:** Per-tenant audit logging

### 8. Dependency Management
- **External Dependencies:** List of external services required
- **Fallback Strategies:** What to do if dependencies fail
- **Dependency Health Checks:** Monitoring dependency health
- **Circuit Breaker Configuration:** When to fail-fast on dependencies
- **Timeout Policies:** Call timeouts to dependencies
- **Retry Policies:** Exponential backoff configuration

### 9. Cost Management & Optimization
- **Resource Cost Drivers:** What drives infrastructure cost
- **Cost Targets:** Budget per operation, per day
- **Cost Monitoring:** How to track and alert on cost
- **Optimization Opportunities:** Known cost savings
- **Model/Provider Selection:** Cost-optimized selections
- **Caching Strategy:** Cache TTLs to reduce backend calls

### 10. Operational Runbooks
- **Common Issues & Resolutions:** Troubleshooting guide
- **Debugging Procedures:** How to debug production issues
- **Escalation Procedures:** When and how to escalate
- **Maintenance Windows:** Scheduled maintenance procedures
- **Database Backups:** Backup schedule and retention
- **Log Retention Policy:** How long logs are kept
- **Incident Response:** Steps to take during incident

### 11. Guild-Specific Operations
- **Guild Coordination:** How this agent coordinates with guild peers
- **Guild Escalation:** Escalation path within guild
- **Guild SLA:** Guild-level performance commitments
- **Knowledge Sharing:** Documentation and sharing procedures
- **Guild Meetings:** Regular sync schedules

### 12. Version Management & Upgrades
- **Versioning Strategy:** Semantic versioning approach
- **Upgrade Procedure:** Steps to upgrade agent version
- **Backward Compatibility:** Breaking change policy
- **Feature Flags:** Rollout strategy for new features
- **Rollback Procedure:** How to rollback if needed
- **Testing Before Upgrade:** What testing required

## Output Format

Create 36 operational specification documents in markdown format.

**Filename pattern:** `Agent-XX-operational-spec.md` (e.g., `Agent-01-operational-spec.md`)

**Structure for each document:**
```
# Agent-XX: [Agent Name] - Operational Specification

## Overview
- Role: [Agent role]
- Domain: [Domain]
- Guild: [Guild name]
- SLA: [Availability target]

## Performance Requirements & SLAs
### Throughput
[Targets per hour/day]

### Latency
- p50: [Value]
- p95: [Value]
- p99: [Value]

### Availability
[Uptime target]

### Capacity
[Max concurrent operations]

## Monitoring & Observability
### Key Metrics
[Metrics to track]

### Health Checks
[Health check procedures]

### Alerting
[Alert thresholds]

## Deployment
### Deployment Model
[Model type]

### Configuration
[Environment setup]

### Startup & Shutdown
[Procedures]

## Reliability & Failover
### Failure Modes
[Expected failures]

### Recovery
[Recovery procedures]

### RTO/RPO
[Recovery objectives]

## Scaling & Load Management
### Auto-Scaling Rules
[Scaling criteria]

### Rate Limiting
[Rate limit configuration]

### Peak Capacity
[Maximum load]

## Security & Compliance
### Authentication & Authorization
[Security procedures]

### Audit Logging
[Audit requirements]

### Compliance
[Compliance requirements]

## Multi-Tenancy
### Tenant Isolation
[Isolation procedures]

### Data Cleanup
[Deletion procedures]

## Dependencies
### External Services
[Service list]

### Fallback Strategy
[Fallback procedures]

## Cost Management
### Cost Drivers
[Cost factors]

### Optimization
[Cost optimization]

## Operational Runbooks
### Common Issues
[Troubleshooting guide]

### Escalation Path
[Escalation procedures]

### Maintenance
[Maintenance schedule]

## Guild Operations
### Coordination
[Guild coordination]

### Escalation
[Guild escalation path]

## Version Management
### Upgrade Procedure
[Upgrade steps]

### Rollback Procedure
[Rollback steps]
```

## Critical Requirements

1. **Comprehensive Coverage:** Every agent must have complete operational specs
2. **Guild-Specific Details:** Operational details specific to each guild's charter
3. **Real-World Values:** Include realistic targets based on agent complexity and load
4. **Failover Procedures:** Detail all recovery and fallback procedures
5. **Multi-Tenancy:** Enforce company_id isolation in all operational aspects
6. **Cost Awareness:** Include cost tracking and optimization for each agent
7. **Escalation Paths:** Clear escalation to guild leads and Claude Architect
8. **Security First:** Comprehensive security and compliance requirements
9. **Monitoring:** Detailed observability and alerting specifications
10. **Testing:** Operational procedures must be testable and verifiable

## Delivery

Output all 36 operational specifications. You can output them as:
1. **One at a time** (36 separate outputs, one per agent)
2. **In groups** (Groups of 5-10 agents per output)

**Start with Agent-01 and proceed sequentially through Agent-36.**

Each output should have clear filename and complete specification content ready to be saved as a markdown file.

## Cross-Reference with Technical Specifications

These operational specs work in conjunction with the Technical Specifications for each agent:
- **Technical Specs** define WHAT the agent does (architecture, data models, APIs)
- **Operational Specs** define HOW the agent operates (performance, reliability, deployment)

Together they form a complete specification for each agent in the system.
