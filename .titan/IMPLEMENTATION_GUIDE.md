# Implementation Guide - Quick Reference

**Start Here**: Read this to understand the system and get started.

---

## System Overview

This is an **enterprise-grade autonomous agent orchestration system** that enables:

- **Multi-Agent Collaboration**: Specialized agents (planner, implementer, reviewer, tester, security, documentation) work together
- **Type-Safe Operations**: Every task, handoff, and decision is strongly typed
- **Complete Auditability**: Immutable change ledger records every action
- **Safety First**: Sandboxing, approval gates, policy enforcement, secrets management
- **Continuous Learning**: System improves from execution data and feedback
- **Production Ready**: Release coordination, health scoring, rollback capability

---

## 41 Implementation Issues Organized by Phase

### Phase 1: Foundation (4 weeks)

**Core infrastructure - MUST complete first**

| Issue | Title | Effort | Dependencies |
|-------|-------|--------|--------------|
| 1.1 | Agent Manifests & Capability Registry | 2w | None |
| 1.2 | Typed Task Graphs & Plan-as-Code | 2w | 1.1 |
| 1.3 | Durable Execution Engine | 2w | 1.1, 1.2 |
| 1.4 | Agent Memory System | 1.5w | 1.1, 1.2 |

**What gets built**: `.titan/` infrastructure for manifests, task graphs, execution state, memory

---

### Phase 2: Knowledge Layer (3.5 weeks)

**Understanding the codebase deeply**

| Issue | Title | Effort | Dependencies |
|-------|-------|--------|--------------|
| 2.1 | Knowledge Graph | 2.5w | Phase 1 |
| 2.2 | Repository Constitution | 1.5w | 2.1 |
| 2.3 | Architectural Drift Detection | 2w | 2.1, 2.2 |

**What gets built**: Graph of all code relationships, architectural rules, violation detection

---

### Phase 3: Execution Control (5.5 weeks)

**Managing multi-agent work**

| Issue | Title | Effort | Dependencies |
|-------|-------|--------|--------------|
| 3.1 | Specialist Agent Teams | 2.5w | Phase 1-2 |
| 3.2 | File Ownership Locks | 1.5w | 3.1 |
| 3.3 | Branch-per-Agent Workflow | 2w | 3.1, 3.2 |

**What gets built**: Agent orchestration, file locking, Git branching coordination

---

### Phase 4: Safety & Governance (6 weeks)

**Security and policy enforcement - CRITICAL**

| Issue | Title | Effort | Dependencies |
|-------|-------|--------|--------------|
| 4.1 | Policy Engine | 2w | Phase 2 |
| 4.2 | Sandboxed Execution | 3w | 4.1 |
| 4.3 | Human Approval Gates | 2w | 4.1, Phase 3 |
| 4.4 | Secrets Broker | 2w | 4.2, 4.1 |

**What gets built**: Policy enforcement, Docker sandboxes, approval workflow, credential management

---

### Phase 5: Validation & Quality (4.5 weeks)

**Ensuring code quality**

| Issue | Title | Effort | Dependencies |
|-------|-------|--------|--------------|
| 5.1 | Evidence-Based Completion | 2w | Phase 1 |
| 5.2 | Static Analysis Pipeline | 2.5w | Phase 4 |
| 5.3 | Security Review Agent | 2w | 5.2, Phase 4 |

**What gets built**: Quality gates, SAST, security scanning, automated validation

---

### Phase 6: Integration & Compatibility (3.5 weeks)

**External connectivity**

| Issue | Title | Effort | Dependencies |
|-------|-------|--------|--------------|
| 6.1 | MCP Compatibility | 2w | Phase 1, 3 |
| 6.2 | Model Router | 1.5w | 6.1, Phase 7 |

**What gets built**: Claude/ChatGPT integration, tool adapters, model selection

---

### Phase 7: Observability & Learning (4 weeks)

**Visibility and continuous improvement**

| Issue | Title | Effort | Dependencies |
|-------|-------|--------|--------------|
| 7.1 | Change Ledger | 1.5w | Phase 1 |
| 7.2 | Observability Dashboard | 2.5w | 7.1, Phase 3 |
| 7.3 | Self-Improvement Loop | 2w | 7.1, 7.2, Phase 2, 5 |

**What gets built**: Audit trail, real-time dashboard, automatic optimization

---

### Phase 8: Operationalization (5.5 weeks)

**Production operations**

| Issue | Title | Effort | Dependencies |
|-------|-------|--------|--------------|
| 8.1 | Release Orchestrator | 3w | Phase 7, 4 |
| 8.2 | Runtime Service API | 2.5w | Phase 7, 1 |
| 8.3 | Repository Health Score | 2w | Phase 7, 2, 5 |

**What gets built**: Automated releases, job queue API, health dashboard

---

## File Organization

```
.titan/
├── ROADMAP.yaml                 # Phase overview
├── TODO.md                       # Master checklist (this becomes your TODO)
├── IMPLEMENTATION_GUIDE.md       # You are here
│
├── issues/                       # Detailed issue specs
│   ├── PHASE_1_FOUNDATION.md
│   ├── PHASE_2_KNOWLEDGE.md
│   ├── PHASE_3_EXECUTION.md
│   ├── PHASE_4_SAFETY.md
│   ├── PHASE_5_VALIDATION.md
│   ├── PHASE_6_INTEGRATION.md
│   ├── PHASE_7_OBSERVABILITY.md
│   └── PHASE_8_OPERATIONS.md
│
├── schemas/                      # JSON Schema for validation
│   ├── agent-manifest.schema.json
│   ├── task-graph.schema.json
│   ├── capability-registry.schema.json
│   ├── handoff-packet.schema.json
│   └── policy.schema.json
│
├── agents/                       # Agent definitions
│   ├── planner.yaml
│   ├── implementer.yaml
│   ├── tester.yaml
│   ├── reviewer.yaml
│   ├── security_agent.yaml
│   ├── documentation_agent.yaml
│   └── release_agent.yaml
│
├── memory/                       # Knowledge storage
│   ├── global/                   # Shared principles
│   ├── repository/               # Decisions, ADRs
│   │   ├── adr-*.md
│   │   └── known-defects.yaml
│   └── README.md
│
├── registry/
│   └── capabilities.yaml         # Global capability registry
│
├── constitution.yaml             # Architecture rules & policies
│
└── analysis/
    └── config.yaml               # Analysis tool config
```

---

## Starting Phase 1

### Week 1: Agent Manifests (Issue 1.1)

**Goal**: Foundation for agent definition

```bash
# 1. Create directories
mkdir -p .titan/agents .titan/schemas .titan/registry

# 2. Create agent manifest schema
# File: .titan/schemas/agent-manifest.schema.json
# Define: version, name, role, capabilities, authority, constraints, memory_access

# 3. Create first agents
# File: .titan/agents/planner.yaml
# Define: Planner agent (task decomposition)

# 4. Create capability registry
# File: .titan/registry/capabilities.yaml
# Track all available agents and tools
```

**Deliverables**:
- Schema files (JSON Schema format)
- Sample agent manifests
- Registry system

---

### Week 2: Task Graphs (Issue 1.2)

**Goal**: Executable task representation

```bash
# 1. Create task graph schema
# File: .titan/schemas/task-graph.schema.json
# Define: tasks, dependencies, checkpoints, approval gates

# 2. Create example plan
# File: .titan/plans/example-task.yaml
# Define: Simple task graph with dependencies

# 3. Build parser and validator
# Code: .titan/src/TaskGraphExecutor.php
# Functionality: Load, validate, execute task graphs
```

**Deliverables**:
- Task graph parser
- Schema validation
- Simple executor

---

### Week 3-4: Execution & Memory (Issues 1.3 & 1.4)

**Goal**: Persistent execution state and context

```bash
# 1. Implement durable execution
# Files: .titan/execution/sessions/, checkpoints/
# Functionality: Save/resume task state

# 2. Implement memory system
# Files: .titan/memory/global/, repository/
# Functionality: Store and query decisions, patterns

# 3. Create context builder
# Code: .titan/src/ContextBuilder.php
# Functionality: Assemble relevant context for each task
```

**Deliverables**:
- Durable executor with checkpoints
- Memory manager with scoping
- Context builder

---

## Key Concepts to Implement

### Agent Manifests

Every agent needs a YAML manifest defining:
- Role and responsibilities
- Capabilities (what it can do)
- Authority (what it's allowed to do)
- Constraints (time, tokens, concurrency)
- Memory access (what data it sees)
- Handoff rules (when to pass to next agent)

### Task Graphs

Plans are directed acyclic graphs (DAGs) where:
- Tasks are nodes
- Dependencies are edges
- Each task has inputs, outputs, completion criteria
- Tasks can have checkpoints for resume
- Approval gates can block execution

### Checkpoints

Execution pauses at checkpoints with evidence:
- What was completed
- Test results, build logs
- Status for resuming later
- Proof the checkpoint was reached

### Memory Scoping

Each agent gets only relevant context:
- Global: Architecture principles, standards
- Repository: Decisions, known solutions
- Task: Specific task context
- Agent: Agent's own recent work

---

## Implementation Checklist for Phase 1

### Before Starting
- [ ] Read ROADMAP.yaml to understand overall system
- [ ] Read TODO.md to see all tasks
- [ ] Review issues in .titan/issues/ for detailed requirements

### Issue 1.1: Agent Manifests
- [ ] Create `.titan/schemas/agent-manifest.schema.json`
- [ ] Create `.titan/agents/planner.yaml` (first agent)
- [ ] Create `.titan/registry/capabilities.yaml`
- [ ] Write manifest parser and validator
- [ ] Create CLI for `titan manifest validate`
- [ ] Add tests for schema validation

### Issue 1.2: Task Graphs
- [ ] Create `.titan/schemas/task-graph.schema.json`
- [ ] Create `.titan/plans/example-plan.yaml`
- [ ] Build TaskGraphExecutor class
- [ ] Implement dependency resolution
- [ ] Add checkpoint support
- [ ] Add approval gate support
- [ ] Add tests for execution

### Issue 1.3: Durable Execution
- [ ] Design checkpoint schema
- [ ] Implement `.titan/execution/sessions/` storage
- [ ] Build state persistence
- [ ] Add checkpoint verification
- [ ] Implement pause/resume
- [ ] Add rollback capability
- [ ] Add tests for recovery

### Issue 1.4: Memory System
- [ ] Design memory hierarchy
- [ ] Implement `.titan/memory/` storage
- [ ] Build MemoryManager class
- [ ] Create ContextBuilder for each agent type
- [ ] Add search functionality
- [ ] Implement memory cleanup
- [ ] Add tests for scoping

---

## Tools & Technologies to Use

**For Phase 1**:
- YAML for configs and manifests
- JSON Schema for validation
- PHP for core implementation
- Git for versioning
- Docker for eventual sandboxing

**Recommended Libraries**:
- `symfony/yaml` - YAML parsing
- `justinrainbow/json-schema` - JSON Schema validation
- `laravel/framework` - Application framework

---

## Testing Strategy

For each issue:
1. **Unit tests**: Test individual components
2. **Integration tests**: Test components working together
3. **Validation tests**: Test schema and data validation
4. **Recovery tests**: Test checkpoint and resume logic

```bash
# Example test run
phpunit tests/Unit/TaskGraphTest.php
phpunit tests/Integration/ExecutionTest.php
phpunit tests/Schema/ValidationTest.php
```

---

## Success Criteria for Phase 1

By end of Week 4, system should support:

✅ Defining agents with manifests  
✅ Creating task graphs as YAML  
✅ Executing tasks sequentially  
✅ Pausing and resuming execution  
✅ Storing and retrieving agent context  
✅ All tests passing  

---

## What Comes After Phase 1?

Phase 2 depends on Phase 1 being solid:
- Knowledge graph discovery (uses task graphs)
- Repository constitution (defines rules)
- Architectural drift detection (uses graphs)

Then Phase 3-4 can run in parallel:
- Agent teams and orchestration
- Policy enforcement and sandboxing

---

## Quick Commands (Future)

```bash
# Once implemented, these will work:
titan manifest validate .titan/agents/*.yaml
titan plan create --goal "Implement webhooks" --complexity medium
titan plan execute webhook-implementation.yaml
titan plan resume webhook-implementation --from checkpoint-name
titan agent list
titan memory search --scope repository --query "payment patterns"
titan health score
```

---

## Questions to Answer

1. **When implementing 1.1**: How detailed should agent manifests be? (More = more control, less = simpler)
2. **When implementing 1.2**: Should task graphs support loops or only DAGs? (DAGs for safety, loops for flexibility)
3. **When implementing 1.3**: Where to store execution state? (File system, database, S3?)
4. **When implementing 1.4**: How much history to keep in memory? (Archive old decisions)

---

## Resources Needed

- **Time**: ~4 person-weeks for Phase 1
- **Storage**: ~100MB for initial state and memory
- **Tools**: JSON Schema validator, YAML parser
- **Team**: 1-2 developers for Phase 1

---

## Next Steps

1. **Read** `.titan/ROADMAP.yaml` - See full system vision
2. **Review** `.titan/TODO.md` - See all tasks
3. **Read** `.titan/issues/PHASE_1_FOUNDATION.md` - See detailed specs
4. **Start** with Issue 1.1 - Agent Manifests
5. **Track** progress in `.titan/TODO.md`

Good luck! 🚀
