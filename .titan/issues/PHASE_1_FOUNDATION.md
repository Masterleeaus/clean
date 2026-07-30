# Phase 1: Foundation (Weeks 1-4)

Core infrastructure for agent definition, task representation, and state persistence.

## Issue 1.1: Agent Manifests & Capability Registry

**Effort**: 2 weeks  
**Priority**: P0 - Blocker for all other phases  
**Status**: `todo`  
**Dependencies**: None

### Description

Create versioned machine-readable manifests for every agent, tool, plugin, skill, and capability so the system can route work intelligently and validate permissions.

### Acceptance Criteria

- [ ] Every agent has versioned manifest defining role, capabilities, authority, constraints
- [ ] Capability Registry is queryable by capability, provider, version, status
- [ ] Schema validation prevents invalid manifests
- [ ] CLI allows registering/updating agents and tools
- [ ] Documentation covers manifest authoring

### Key Tasks

1. Define Agent Manifest schema (JSON Schema)
2. Define Capability Registry schema
3. Create `.titan/` directory structure
4. Write manifest parser and validator
5. Implement registry loader and query interface
6. Create CLI for registering agents/tools
7. Add schema validation tests

### Deliverables

- `.titan/agents/*.yaml` - Agent manifests
- `.titan/registry/capabilities.yaml` - Global capability registry
- `.titan/schemas/agent-manifest.schema.json`
- `.titan/schemas/capability-registry.schema.json`

---

## Issue 1.2: Typed Task Graphs & Plan-as-Code

**Effort**: 2 weeks  
**Priority**: P0 - Foundation for execution  
**Status**: `todo`  
**Dependencies**: 1.1

### Description

Replace loose prompt chains with explicit, verifiable, resumable directed task graphs that define steps, dependencies, branching, retries, and completion criteria.

### Acceptance Criteria

- [ ] Plans are explicit, versioned, auditable YAML files
- [ ] Task graphs support dependencies, branching, retries, timeouts
- [ ] Tasks can resume from checkpoints without restarting
- [ ] Approval gates prevent risky changes without authorization
- [ ] Execution traces are complete and queryable

### Key Tasks

1. Design Task Graph schema (JSON Schema validation)
2. Implement task graph parser and validator
3. Build task execution engine with checkpoint/resume
4. Implement dependency resolution and parallel execution
5. Add retry and backoff strategies
6. Create approval gate system
7. Write comprehensive tests

### Deliverables

- `.titan/plans/*.yaml` - Task graphs
- `.titan/schemas/task-graph.schema.json`
- Task executor service with resumable execution

---

## Issue 1.3: Durable Execution Engine & Checkpoint System

**Effort**: 2 weeks  
**Priority**: P0 - Critical for resilience  
**Status**: `todo`  
**Dependencies**: 1.1, 1.2

### Description

Persist workflow state at strategic checkpoints so interrupted tasks can resume without losing progress or re-executing completed work.

### Acceptance Criteria

- [ ] Execution state persists to `.titan/execution/` after each task
- [ ] Tasks can resume from last checkpoint without re-executing
- [ ] Checkpoint verification proves completion (tests passing)
- [ ] Rollback restores previous checkpoint state
- [ ] Execution trace is queryable for debugging

### Key Tasks

1. Design checkpoint schema and storage format
2. Implement DurableExecutor with state persistence
3. Build checkpoint verification system
4. Implement pause/resume functionality
5. Add rollback capability (Git integration)
6. Create checkpoint evidence collection
7. Write comprehensive tests

### Deliverables

- `.titan/execution/sessions/` - Execution state
- `.titan/execution/artifacts/` - Task outputs
- Durable execution engine

---

## Issue 1.4: Agent Memory System & Context Management

**Effort**: 1.5 weeks  
**Priority**: P0 - Essential for agent reasoning  
**Status**: `todo`  
**Dependencies**: 1.1, 1.2

### Description

Provide agents with fast, scoped memory access to relevant decisions, architecture, code patterns, and prior fixes without overwhelming context.

### Memory Hierarchy

```
Global Memory (Shared)
├── Architecture Principles
├── Coding Standards
├── Security Policies
└── Known Patterns & Anti-Patterns

Repository Memory
├── Architecture Decisions (ADRs)
├── Known Defects & Solutions
├── File Ownership Map
└── Service Boundaries

Branch Memory
├── Feature Specification
├── Related Changes
└── Blockers & Dependencies

Task Memory
├── Task Specification
├── In-Progress Work
├── Intermediate Results
└── Evidence & Test Results
```

### Acceptance Criteria

- [ ] Agents access only relevant context for their scope
- [ ] Similar past solutions are searchable
- [ ] Architecture decisions and lessons are preserved
- [ ] Memory doesn't grow unbounded (archival strategy)
- [ ] Context is built deterministically

### Key Tasks

1. Design memory schema and storage format
2. Implement memory manager with scoping
3. Build memory search (keyword + semantic)
4. Create context builder for different agent types
5. Implement memory cleanup/archival
6. Add memory access audit logging
7. Write comprehensive tests

### Deliverables

- `.titan/memory/` - Memory storage
- Memory manager service
- Context builder service
