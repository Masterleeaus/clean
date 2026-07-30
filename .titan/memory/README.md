# Agent Memory System

The memory system provides scoped access to relevant context for each agent, preventing information overload while ensuring agents have the knowledge they need.

## Memory Hierarchy

```
Global Memory (Shared by all agents)
├── Architecture Principles
│   └── principles.md
├── Coding Standards
│   └── standards.yaml
├── Security Policies
│   └── security.yaml
└── Known Patterns & Anti-Patterns
    └── patterns.yaml

Repository Memory (Per-repository)
├── Architecture Decisions (ADRs)
│   ├── adr-0001-*.md
│   ├── adr-0002-*.md
│   └── ...
├── Known Defects & Solutions
│   └── known-defects.yaml
├── File Ownership Map
│   └── ownership.yaml
└── Service Boundaries
    └── boundaries.yaml

Branch Memory (Per-feature branch)
├── Feature Specification
│   └── spec.md
├── Related Changes
│   └── related.yaml
└── Blockers & Dependencies
    └── blockers.yaml

Task Memory (Per-task)
├── Task Specification
│   └── spec.md
├── In-Progress Work
│   └── progress.yaml
├── Intermediate Results
│   └── results.yaml
└── Evidence & Test Results
    └── evidence.yaml

Agent Memory (Per-agent)
├── Recent Work
│   └── recent.yaml
├── Success Patterns
│   └── patterns.yaml
└── Failure Analysis
    └── failures.yaml
```

## Memory Access

Each agent has different memory access levels:

### Planner Agent
- ✅ Global memory (principles, standards)
- ✅ Repository memory (ADRs, defects)
- ✅ Branch memory (spec, dependencies)
- ✅ Task memory (specification)
- ✅ Agent memory (own patterns)

### Implementer Agent
- ✅ Global memory (standards, patterns)
- ✅ Repository memory (ADRs, boundaries)
- ✅ Branch memory (spec, related changes)
- ✅ Task memory (specification, progress)
- ✅ Agent memory (own recent work)

### Reviewer Agent
- ✅ Global memory (standards, principles)
- ✅ Repository memory (ADRs, boundaries)
- ❌ Branch memory
- ❌ Task memory
- ❌ Agent memory

### Tester Agent
- ✅ Global memory (standards)
- ✅ Repository memory (defects, boundaries)
- ✅ Branch memory
- ✅ Task memory (specification)
- ❌ Agent memory

### Security Agent
- ✅ Global memory (security policies, patterns)
- ✅ Repository memory (defects, security)
- ✅ Branch memory (spec)
- ✅ Task memory (specification)
- ❌ Agent memory

### Documentation Agent
- ✅ Global memory (principles, standards)
- ✅ Repository memory (ADRs, decisions)
- ✅ Branch memory (spec, related)
- ✅ Task memory (spec)
- ❌ Agent memory

### Release Agent
- ✅ Global memory (principles)
- ✅ Repository memory (ADRs)
- ❌ Branch memory
- ✅ Task memory (spec)
- ❌ Agent memory

## Memory Management

### Storage

Memory is stored in Git for version control and auditability:
```
.titan/memory/
├── global/
│   ├── principles.md
│   ├── standards.yaml
│   └── patterns.yaml
├── repository/
│   ├── adr-*.md
│   ├── known-defects.yaml
│   └── ownership.yaml
└── README.md (this file)
```

Branch and task memory is stored in execution state:
```
.titan/execution/
├── sessions/
│   └── {session-id}/
│       ├── branch-memory.yaml
│       └── task-memory/
```

### Archival

Old memory is archived to prevent unbounded growth:
- Repository memory: Keep 2 years, archive older
- Task memory: Keep 1 year, delete after
- Agent memory: Keep 30 days, delete after
- Branch memory: Delete after branch closes

### Searching

Memory is searchable via the ContextBuilder:
```php
$context = ContextBuilder::forAgent('implementer')
    ->withScope('repository')
    ->search('payment processing')
    ->limit(10)
    ->build();
```

## Guidelines for Memory Authors

### When to Add Global Memory

- Architecture principle that applies everywhere
- Coding standard that all code must follow
- Security policy everyone needs to know
- Recurring pattern we want all agents to know

### When to Add Repository Memory

- Architecture decision we made (→ ADR)
- Known bug we discovered and fixed
- Important boundary or ownership rule
- Lesson learned from a difficult task

### When to Add Branch Memory

- What this feature is trying to accomplish
- What other features it depends on
- What's blocking us
- What's related and might need coordination

### When to Add Task Memory

- Specification for the specific task
- Progress and intermediate results
- Evidence (tests, scans, reviews)
- Learnings specific to this task

## Example: ADR (Architecture Decision Record)

```markdown
# ADR-0005: Transactional Outbox Pattern for Event Publishing

## Context
We need to guarantee that domain events are published if and only if their
corresponding entity is persisted.

## Decision
Implement the Transactional Outbox pattern: events are written to an outbox
table within the same database transaction, then a separate process publishes
them to the event bus.

## Rationale
- Guarantees no events are lost
- No need for distributed transactions
- Simple to understand and maintain
- Proven pattern in event-driven systems

## Consequences
- Need additional outbox table
- Need background job to publish events
- Slight delay between persistence and publication
- But strong consistency guarantees

## Status
Accepted

## Date
2025-08-15
```

## Querying Memory

### From CLI

```bash
# Search repository memory
titan memory search --scope repository --query "payment" --limit 10

# Get specific ADR
titan memory get --scope repository --path "adr-0005"

# List all defects
titan memory list --scope repository --type "defect"
```

### From Code

```php
$memory = MemoryManager::getInstance();

// Search repository memory
$results = $memory->search('repository', 'payment processing');

// Get specific ADR
$adr = $memory->get('repository', 'adr-0005');

// List known defects
$defects = $memory->list('repository', 'defect');
```

## Privacy & Access Control

All memory is version-controlled in Git. Sensitive information should NOT be stored
in memory (use .titan Secrets Broker instead).

Memory access is logged for audit trail purposes.
