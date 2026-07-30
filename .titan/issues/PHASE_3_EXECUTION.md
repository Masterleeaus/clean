# Phase 3: Execution Control (Weeks 9-12)

Managing multi-agent work with coordination and conflict prevention.

## Issue 3.1: Specialist Agent Teams & Dynamic Selection

**Effort**: 2.5 weeks  
**Priority**: P0 - Core orchestration  
**Status**: `todo`  
**Dependencies**: Phase 1, Phase 2

### Description

Create specialist agents for different roles (planner, implementer, reviewer, tester, security, documentation) and implement intelligent routing to select the right agent(s) for each task.

### Agent Roles

- **Planner**: Decomposes tasks into subtasks, identifies dependencies, creates task graphs
- **Implementer**: Writes code, creates features, fixes bugs
- **Reviewer**: Reviews code, identifies issues, suggests improvements
- **Tester**: Writes tests, validates acceptance criteria, catches regressions
- **Security Agent**: Scans for vulnerabilities, reviews security implications
- **Documentation Agent**: Writes docs, updates ADRs, generates changelog
- **Release Agent**: Coordinates releases, manages versioning, deployments

### Agent Selection Logic

```
SELECT agent FROM agents WHERE
  - agent.capabilities INTERSECT task.required_capabilities
  - agent.authority >= task.risk_level
  - agent.current_load < agent.max_concurrency
  - agent.success_rate(task.type) >= 0.85
ORDER BY historical_success DESC
LIMIT 1
```

### Agent Handoff

Agents communicate via typed **handoff packets** containing:
- Task specification
- Required outputs
- Approval gates
- Evidence expectations
- Escalation contacts

### Acceptance Criteria

- [ ] 7 specialist agents defined with manifests
- [ ] Agent selection algorithm implemented
- [ ] Handoff packet system for agent-to-agent communication
- [ ] Orchestration engine routes tasks to agents
- [ ] Agent success rates tracked and improved
- [ ] Team composition optimized by task type
- [ ] Agent monitoring and health dashboard

### Key Tasks

1. Define agent roles and capabilities
2. Build agent selector service
3. Implement handoff packet system
4. Build orchestration engine
5. Create agent communication layer
6. Implement team composition optimizer
7. Add monitoring and metrics
8. Write comprehensive tests

### Deliverables

- `.titan/agents/*.yaml` - 7 agent manifests
- Agent selector service
- Handoff packet system
- Orchestration engine

---

## Issue 3.2: File Ownership Locks & Conflict Prevention

**Effort**: 1.5 weeks  
**Priority**: P0 - Prevents race conditions  
**Status**: `todo`  
**Dependencies**: 3.1

### Description

Implement atomic file locking to prevent concurrent edits and accidental overwrites when multiple agents work simultaneously.

### Lock System

- **Acquisition**: Atomic compare-and-swap on lock file
- **Timeout**: Locks expire after 30 minutes (configurable)
- **Conflict Detection**: Detect overlapping edit regions
- **Resolution**: Merge, conflict resolution, escalation
- **Audit Trail**: Log all lock acquisitions and releases

### Acceptance Criteria

- [ ] File locks acquired atomically
- [ ] Conflicting edits detected and reported
- [ ] Lock timeouts prevent deadlock
- [ ] Merge conflicts resolved automatically when possible
- [ ] Escalation to human when automatic merge fails
- [ ] Complete lock audit trail

### Key Tasks

1. Design lock file format
2. Implement atomic lock acquisition
3. Build conflict detector
4. Implement automatic merge logic
5. Add escalation notification
6. Build lock audit system
7. Write comprehensive tests

### Deliverables

- File locking service
- Conflict detector
- Automatic merge resolver
- Lock audit system

---

## Issue 3.3: Branch-per-Agent Workflow & Merge Coordination

**Effort**: 2 weeks  
**Priority**: P0 - Isolation and traceability  
**Status**: `todo`  
**Dependencies**: 3.1, 3.2

### Description

Each agent gets an isolated Git branch, merging to main only after approval and all checks pass. Enables parallel work with clear commit history.

### Branch Strategy

```
main (protected)
├── feature/feature-name (from main)
│   ├── agent/planner-task-id (agent work)
│   ├── agent/implementer-task-id (agent work)
│   └── agent/reviewer-task-id (review, then merge to feature)
└── bugfix/bug-name (from main)
    ├── agent/implementer-bugfix-id (agent work)
    └── agent/tester-bugfix-id (tests, then merge to bugfix)
```

### Merge Coordination

1. Agent creates branch from task specification
2. Agent completes work, pushes to agent/* branch
3. Optional agent reviews/improves code
4. All checks must pass (tests, lint, security)
5. Approval gates: any blocked? escalate
6. Merge agent/* to feature/* (fast-forward)
7. Feature/* merges to main after final review
8. Main CI runs final validation
9. Auto-revert on failure

### Acceptance Criteria

- [ ] Agents work on isolated branches
- [ ] Branch names trace work to agents/tasks
- [ ] Merge coordination enforced
- [ ] No force-pushes to main allowed
- [ ] Commit history is clean and auditable
- [ ] Automatic revert on failure
- [ ] PR templates guide agent work

### Key Tasks

1. Design branch naming strategy
2. Build branch manager service
3. Implement Git worktree support
4. Build merge coordinator
5. Add automatic revert logic
6. Create branch cleanup
7. Write comprehensive tests

### Deliverables

- Branch manager service
- Merge coordinator
- Git worktree support
- Automatic revert system

