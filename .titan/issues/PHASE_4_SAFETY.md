# Phase 4: Safety & Governance (Weeks 13-16)

Security, policy enforcement, and credential management.

## Issue 4.1: Policy Engine & Authority Enforcement

**Effort**: 2 weeks  
**Priority**: P0 - Critical for safety  
**Status**: `todo`  
**Dependencies**: Phase 2

### Description

Centralized rules engine that defines and enforces what agents are allowed to do, preventing unauthorized or dangerous operations.

### Policy Types

```yaml
policies:
  - id: "payment_modifications"
    description: "Only security team can modify payment processing"
    subject: "agent[role=implementer]"
    action: "modify_file"
    resource: "src/Payment/**"
    effect: "deny"
    exceptions:
      - "agent[role=security_team]"
      - "agent[approval=payment_admin]"

  - id: "schema_changes"
    description: "Database schema changes require migration + testing"
    subject: "agent[*]"
    action: "create_migration"
    resource: "*"
    condition: "must_include(tests, documentation)"
    effect: "deny"

  - id: "secret_access"
    description: "Secrets only accessed via broker, never hardcoded"
    subject: "agent[*]"
    action: "commit"
    resource: "**/*.env"
    effect: "deny"

  - id: "external_api_calls"
    description: "API calls must use rate limiter and retry logic"
    subject: "agent[*]"
    action: "call_external_api"
    condition: "has_rate_limiter AND has_retry_logic"
    effect: "require"
```

### Acceptance Criteria

- [ ] Policy language is expressive and human-readable
- [ ] Policies are validated against schema
- [ ] Policy evaluation fast (<10ms per decision)
- [ ] Audit trail logs all policy decisions
- [ ] Exceptions can be granted via approval
- [ ] Policies stored in Git for version control

### Key Tasks

1. Design policy schema and DSL
2. Implement policy validator
3. Build policy evaluator with decision caching
4. Implement exception granting
5. Add audit logging
6. Create policy testing framework
7. Write comprehensive tests

### Deliverables

- Policy schema and DSL
- Policy engine evaluator
- Policy audit system
- Exception management

---

## Issue 4.2: Sandboxed Execution & Runtime Isolation

**Effort**: 3 weeks  
**Priority**: P0 - Critical for safety  
**Status**: `todo`  
**Dependencies**: 4.1

### Description

Run agents in disposable, isolated execution environments (Docker containers or remote sandboxes) with enforced resource limits, no persistent state, and credential injection.

### Sandbox Features

- **Ephemeral**: Fresh container per agent task, thrown away after
- **Resource Limits**: CPU, memory, disk, network bandwidth bounded
- **Network Isolation**: No outbound access except approved APIs
- **Filesystem Isolation**: No access to host filesystem
- **Credential Injection**: Secrets provided at runtime, never stored
- **No Persistence**: No agent state persists between tasks
- **Automatic Cleanup**: Container destruction on task completion

### Sandbox Orchestration

```yaml
sandbox:
  image: "clean/agent-runtime:latest"
  resources:
    cpu: "1000m"
    memory: "2Gi"
    disk: "5Gi"
  network:
    egress_allowed:
      - "github.com"
      - "api.stripe.com"
      - "https://*.internal.example.com"
  timeout: "30m"
  cleanup_on_exit: true
```

### Acceptance Criteria

- [ ] Agents run in isolated containers
- [ ] No container can access host filesystem
- [ ] Resource limits enforced
- [ ] Network access restricted to whitelist
- [ ] Credentials injected at runtime
- [ ] Container destroyed after task
- [ ] Performance: container startup <5s

### Key Tasks

1. Design sandbox architecture
2. Build Docker image for agent runtime
3. Implement resource limiting
4. Build credential injection system
5. Create network policy enforcement
6. Implement cleanup and garbage collection
7. Add monitoring and resource tracking
8. Write comprehensive tests

### Deliverables

- Agent runtime Docker image
- Sandbox orchestration service
- Resource limiter
- Credential injector
- Cleanup system

---

## Issue 4.3: Human Approval Gates & Escalation

**Effort**: 2 weeks  
**Priority**: P0 - Critical for oversight  
**Status**: `todo`  
**Dependencies**: 4.1, Phase 3

### Description

Require explicit human approval for risky operations (deployments, schema changes, security-sensitive changes) with escalation and audit trail.

### Gate Types

```yaml
approval_gates:
  - task: "delete_data"
    risk_level: "critical"
    approvers: ["dba_oncall", "security_lead"]
    timeout: "4h"

  - task: "deploy_to_production"
    risk_level: "high"
    approvers: ["release_manager"]
    requires_all: true
    notification: "slack://#deployments"

  - task: "modify_payment_logic"
    risk_level: "high"
    approvers: ["finance_lead", "security_lead"]
    requires_all: true
    evidence_required: ["tests", "documentation"]
```

### Approval Workflow

1. Task identified as requiring approval
2. Approval request created with evidence (diff, tests, security scan results)
3. Notification sent to approvers (email, Slack, SMS)
4. Approver reviews evidence, decides
5. Decision logged with reason
6. If denied: escalate to higher authority
7. If approved: task proceeds with audit trail

### Acceptance Criteria

- [ ] Approval gates configurable per task type
- [ ] Multiple approvers supported with OR/AND logic
- [ ] Evidence-based approvals (tests, scans, docs)
- [ ] Notifications via multiple channels
- [ ] Audit trail of all approvals
- [ ] Approval timeout and escalation
- [ ] Approvers tracked and held accountable

### Key Tasks

1. Design approval gate schema
2. Build approval request system
3. Implement notification system
4. Build approval workflow
5. Create escalation logic
6. Add audit logging
7. Build UI for approvers
8. Write comprehensive tests

### Deliverables

- Approval gate system
- Request management
- Notification service
- Approval UI
- Audit trail

---

## Issue 4.4: Secrets Broker & Credential Management

**Effort**: 2 weeks  
**Priority**: P0 - Critical for security  
**Status**: `todo`  
**Dependencies**: 4.2, 4.1

### Description

Manage secrets and credentials with short-lived, scoped tokens that are only provided when tasks require them, with automatic rotation and leak detection.

### Secrets Architecture

```yaml
secrets:
  stripe_api_key:
    scope: ["payment_service", "payment_tests"]
    ttl: "1h"
    rotation: "90d"
    access_policy: "payment-team"
    leak_detection: true

  github_token:
    scope: ["implementer_agent", "reviewer_agent"]
    ttl: "1h"
    permissions: ["repo:read", "repo:write", "workflow:trigger"]
    audit_every_use: true

  database_password:
    scope: ["migration_agent"]
    ttl: "30m"
    rotation: "weekly"
    requires_approval: true
```

### Features

- **Short-Lived Tokens**: Credentials expire after use or timeout
- **Scoped Access**: Tokens only allow specific permissions
- **Automatic Rotation**: Regular credential rotation
- **No Persistence**: Secrets never written to disk
- **Leak Detection**: Monitor for exposed secrets
- **Audit Trail**: Every secret access logged
- **Revocation**: Ability to immediately revoke credentials

### Acceptance Criteria

- [ ] Secrets stored encrypted at rest
- [ ] Tokens generated on-demand, never long-lived
- [ ] Scope limited to required permissions
- [ ] Automatic rotation implemented
- [ ] Leak detection active
- [ ] Complete audit trail
- [ ] Revocation tested and working

### Key Tasks

1. Design secret storage schema
2. Implement encryption at rest
3. Build token generation service
4. Implement scope enforcement
5. Build credential rotation
6. Add leak detection
7. Build audit logging
8. Write comprehensive tests

### Deliverables

- Secrets broker service
- Token generator
- Scope enforcer
- Rotation system
- Leak detector

