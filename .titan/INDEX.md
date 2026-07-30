# .TITAN Agent OS - Complete File Index

This is the **master index** for the `.titan/` autonomous agent orchestration system. All files in this directory define the complete specification, schemas, agents, and configuration for the multi-agent architecture.

**Last Updated**: 2025-08-15  
**Status**: Phase 1 Foundation Complete (Specification)  
**Total Files**: 18 (+ directories for execution, memory, analysis)

---

## 📋 Core Planning Files

### ROADMAP.yaml
**Purpose**: Master roadmap defining all 8 phases (32 weeks)  
**Content**:
- Epic overview: "Enterprise-Grade Autonomous Agent Orchestration System"
- All 41 implementation issues across 8 phases
- Effort estimates and dependencies
- Technology recommendations (LangGraph, E2B, MCP)
- Success metrics and critical success factors
- Implementation sequence

**Key Sections**:
- Phase 1-8 issue definitions with effort
- Critical success factors
- Technology stack recommendations
- Success metrics (80%+ autonomous, zero unintended side effects, etc.)

**Used By**: Project managers, system architects, stakeholders

---

### TODO.md
**Purpose**: Master checklist and progress tracker  
**Content**:
- Phase-by-phase breakdown with checkbox tracking
- Status field for each phase (Planning/In Progress/Done)
- Sub-tasks and deliverables for each issue
- Existing GitHub issues (#54-#69) documented
- Getting started guide
- Success criteria for final system
- Key files to create

**Checkboxes**:
- ✅ Phase 1.1: Agent Manifests (unchecked - ready to start)
- ✅ Phase 1.2: Task Graphs (unchecked - ready to start)
- ✅ Phase 1.3: Durable Execution (unchecked - ready to start)
- ✅ Phase 1.4: Memory System (unchecked - ready to start)
- ... Phases 2-8 with blocked status

**Used By**: Development team, progress tracking

---

### IMPLEMENTATION_GUIDE.md
**Purpose**: Quick reference for developers starting Phase 1  
**Content**:
- System overview (11,000 words+)
- Week-by-week breakdown for Phase 1
- Key concepts explained (Agent Manifests, Task Graphs, Checkpoints, Memory Scoping)
- File organization guide
- Implementation checklist
- Testing strategy
- Tools and technologies

**Sections**:
- System Overview (autonomy, type-safety, auditability, safety, learning, operations)
- Phase 1 week-by-week guide
- Key concepts
- Implementation checklist
- Success criteria

**Used By**: Developers implementing Phase 1

---

## 📁 Detailed Phase Specifications

### issues/PHASE_1_FOUNDATION.md
**Issues**: 1.1 - 1.4  
**Effort**: 4 weeks  
**Dependencies**: None

**Issue 1.1** (2 weeks): Agent Manifests & Capability Registry
- Deliverables: `.titan/agents/*.yaml`, registry, schema files
- Key tasks: Define manifests, create registry, write parser/validator

**Issue 1.2** (2 weeks): Typed Task Graphs & Plan-as-Code
- Deliverables: `.titan/plans/*.yaml`, task executor service
- Key tasks: Design schema, implement executor, add checkpoints

**Issue 1.3** (2 weeks): Durable Execution Engine
- Deliverables: `.titan/execution/` storage, DurableExecutor class
- Key tasks: Checkpoint schema, state persistence, rollback

**Issue 1.4** (1.5 weeks): Agent Memory System
- Deliverables: `.titan/memory/` storage, MemoryManager, ContextBuilder
- Key tasks: Memory hierarchy, manager, search, context builders

---

### issues/PHASE_2_KNOWLEDGE.md
**Issues**: 2.1 - 2.3  
**Effort**: 3.5 weeks  
**Dependencies**: Phase 1

**Issue 2.1**: Knowledge Graph Construction (2.5 weeks)
- Build queryable graph connecting files, classes, services, routes, tests
- 2000+ nodes, 5000+ edges, impact analysis

**Issue 2.2**: Repository Constitution (1.5 weeks)
- Codify architectural rules, boundaries, ownership
- `.titan/constitution.yaml` storage

**Issue 2.3**: Architectural Drift Detection (2 weeks)
- Detect boundary violations, dead code, anti-patterns
- Health score calculation

---

### issues/PHASE_3_EXECUTION.md
**Issues**: 3.1 - 3.3  
**Effort**: 5.5 weeks  
**Dependencies**: Phase 1-2

**Issue 3.1**: Specialist Agent Teams (2.5 weeks)
- Create 7 specialist agents, implement orchestration
- Handoff packet system

**Issue 3.2**: File Ownership Locks (1.5 weeks)
- Prevent concurrent edits, conflict detection, merge

**Issue 3.3**: Branch-per-Agent Workflow (2 weeks)
- Isolated branches, merge coordination, auto-revert

---

### issues/PHASE_4_SAFETY.md
**Issues**: 4.1 - 4.4  
**Effort**: 6 weeks  
**Dependencies**: Phase 2-3

**Issue 4.1**: Policy Engine (2 weeks)
- Centralized rules, authority enforcement, approval gates

**Issue 4.2**: Sandboxed Execution (3 weeks)
- Docker containers, resource limits, credential injection

**Issue 4.3**: Human Approval Gates (2 weeks)
- Approval workflow, notifications, escalation

**Issue 4.4**: Secrets Broker (2 weeks)
- Short-lived tokens, automatic rotation, leak detection

---

### issues/PHASE_5_VALIDATION.md
**Issues**: 5.1 - 5.3  
**Effort**: 4.5 weeks  
**Dependencies**: Phase 1-4

**Issue 5.1**: Evidence-Based Completion (2 weeks)
- Task contracts, evidence collection, completion validation

**Issue 5.2**: Static Analysis Pipeline (2.5 weeks)
- PHPStan, Psalm, PHPCS, Deptrac, PHPCPD integration
- SARIF output format

**Issue 5.3**: Security Review Agent (2 weeks)
- Vulnerability scanning, SAST, taint analysis

---

### issues/PHASE_6_INTEGRATION.md
**Issues**: 6.1 - 6.2  
**Effort**: 3.5 weeks  
**Dependencies**: Phase 1-5

**Issue 6.1**: MCP Compatibility (2 weeks)
- Expose 20+ tools via Model Context Protocol
- External client integration

**Issue 6.2**: Model Router (1.5 weeks)
- Claude, GPT-4, Gemini routing by task type, cost, performance

---

### issues/PHASE_7_OBSERVABILITY.md
**Issues**: 7.1 - 7.3  
**Effort**: 4 weeks  
**Dependencies**: Phase 1-6

**Issue 7.1**: Change Ledger (1.5 weeks)
- Append-only audit trail, immutable, cryptographically signed

**Issue 7.2**: Observability Dashboard (2.5 weeks)
- Real-time monitoring, WebSocket updates, visualizations

**Issue 7.3**: Self-Improvement Loop (2 weeks)
- Routing optimization, prompt improvement, context optimization

---

### issues/PHASE_8_OPERATIONS.md
**Issues**: 8.1 - 8.3  
**Effort**: 5.5 weeks  
**Dependencies**: Phase 1-7

**Issue 8.1**: Release Orchestrator (3 weeks)
- Version management, deployment pipeline, blue-green, canary, rollback

**Issue 8.2**: Runtime Service API (2.5 weeks)
- REST API, WebSocket server, job queue, workers

**Issue 8.3**: Repository Health Score (2 weeks)
- Coverage, security, architecture, documentation scoring
- Trend analysis, improvement suggestions

---

## 📊 JSON Schemas

### schemas/agent-manifest.schema.json
**Used For**: Validating agent manifest YAML files  
**Validates**:
- version, name, role (required)
- capabilities array
- authority object (risk_level, file_patterns, forbidden_patterns)
- constraints (max_concurrent_tasks, max_execution_time, max_tokens)
- memory_access (boolean flags)
- handoff_rules array

**Example Manifest**: `.titan/agents/planner.yaml`

---

### schemas/task-graph.schema.json
**Used For**: Validating task graph YAML files  
**Validates**:
- version, name, tasks (required)
- Task properties: id, type, depends_on, inputs, outputs, timeout
- Checkpoints and approval gates
- Retry strategies, completion criteria

**Example Usage**: `.titan/plans/example-plan.yaml` (not yet created)

---

### schemas/capability-registry.schema.json
**Used For**: Validating capability registry  
**Validates**:
- version, capabilities array (required)
- Capability object: id, name, category, providers, risk_level
- Parameters and return types
- Related capabilities

**Registry File**: `.titan/registry/capabilities.yaml`

---

### schemas/policy.schema.json
**Used For**: Validating policy files  
**Validates**:
- version, policies array (required)
- Policy object: id, subject, action, resource, effect
- Conditions, exceptions, escalation

**Policy File**: `.titan/constitution.yaml` (uses policies section)

---

## 👥 Agent Manifests

All 7 specialist agents defined. Each has:
- Capabilities (what they can do)
- Authority (what they're allowed to do)
- Constraints (execution limits)
- Memory access (what data they see)
- Handoff rules (when to pass to next agent)

### agents/planner.yaml
**Role**: Decompose goals into tasks  
**Capabilities**: decompose_tasks, create_task_graphs, estimate_effort, risk_analysis  
**Risk Level**: High  
**Handoff**: To implementer when graph created, to security if risk detected

### agents/implementer.yaml
**Role**: Write production code  
**Capabilities**: write_code, refactor, write_unit_tests, commit_code  
**Risk Level**: High  
**Handoff**: To tester when tests pass, to reviewer if refactor needed

### agents/reviewer.yaml
**Role**: Review code quality  
**Capabilities**: code_review, architecture_review, test_coverage_review, performance_review  
**Risk Level**: Medium  
**Handoff**: To implementer if issues found, to tester if approved

### agents/tester.yaml
**Role**: Write tests and validate  
**Capabilities**: write_tests, integration_testing, measure_coverage, regression_testing  
**Risk Level**: Medium  
**Handoff**: To security when tests pass, to implementer if failures

### agents/security_agent.yaml
**Role**: Scan for vulnerabilities  
**Capabilities**: static_security_analysis, dependency_scanning, secret_detection, pattern_validation  
**Risk Level**: Critical  
**Handoff**: To implementer if vulnerabilities, to documentation if approved

### agents/documentation_agent.yaml
**Role**: Write documentation  
**Capabilities**: api_documentation, architecture_documentation, user_documentation, changelog_generation  
**Risk Level**: Low  
**Handoff**: To release_agent when complete

### agents/release_agent.yaml
**Role**: Manage releases and deployments  
**Capabilities**: version_management, deployment_coordination, canary_deployment, rollback_management  
**Risk Level**: Critical  
**Handoff**: To ops_team when successful, to planner if failed

---

## 🏗️ Architecture Definition

### constitution.yaml
**Purpose**: Codify architectural rules and policies (490+ lines)  
**Contains**:

**Bounded Contexts** (5):
- Payment: billing-team owner
- Auth: security-team owner
- Users: platform-team owner
- Extensions: platform-team owner
- Webhooks: integration-team owner

**Architectural Boundaries**:
- Payment ↔ Auth: allowed operations defined
- Auth ↔ Users: forbidden
- Users ↔ Payment: forbidden
- Extensions: can call any public API

**Service Definitions**:
- PaymentService: charge, refund methods
- AuthService: authenticate, authorize methods

**Architecture Rules** (7 core):
- No hardcoded secrets (critical severity)
- Service injection only (high severity)
- Query builder parameterized (critical)
- Event-driven communication (high)
- Immutable invoices (critical)
- Transactional outbox (high)
- Idempotent operations (critical)

**Testing Requirements**:
- Global: 85% coverage minimum
- Critical services (Payment, Auth): 95% coverage
- Mutation score: 80% global, 90% critical

**Naming Conventions**:
- Services end in "Service"
- Repositories end in "Repository"
- Models in PascalCase
- Migrations with date prefix

**Ownership & Approval**:
- Payment/* requires billing-team approval
- Auth/* requires security-team + CTO
- Migrations require DBA + tech lead
- .env* requires devops-team

---

## 📚 Memory System

### memory/README.md
**Purpose**: Document the agent memory hierarchy and access control  
**Contains**:

**Memory Hierarchy**:
- Global Memory (shared): principles, standards, security policies, patterns
- Repository Memory: ADRs, known defects, ownership map, boundaries
- Branch Memory: feature spec, related changes, blockers
- Task Memory: task spec, progress, results, evidence
- Agent Memory: recent work, success patterns, failures

**Access Matrix** (7 agents × 5 memory types):
- Shows which agents can access which memory levels
- Most agents have broad access
- Reviewer has limited access (no task/agent memory)

**Storage Strategy**:
- Stored in Git for version control and auditability
- Sensitive info uses Secrets Broker instead
- Archival strategy to prevent unbounded growth

**API Examples**:
- CLI: `titan memory search --scope repository --query "payment"`
- PHP: `MemoryManager::getInstance()->search('repository', 'payment processing')`

**ADR Template**: Example Architecture Decision Record format

**Guidelines** for what to store in each memory level

---

## 🔍 Analysis Pipeline

### analysis/config.yaml
**Purpose**: Configure static analysis tools and pipeline (250+ lines)  
**Contains**:

**Global Settings**:
- Output: `.titan/analysis/results/` in SARIF format
- Failure condition: fail on error, warn on warning
- Max execution: 5 minutes
- Caching enabled (7-day TTL)

**PHP Tools**:

Type Checking:
- PHPStan level max with doctrine/laravel extensions
- Psalm with security plugin and taint analysis

Linting:
- PHP-CS-Fixer (PSR-12 standard)
- PHPCS (PSR-12)

Architecture:
- Deptrac (dependency checker)
- PHPCPD (copy-paste detector, min 5 lines)

Security:
- Psalm security checks
- SensioLabs SecurityChecker
- gitleaks (secret scanner)

**JavaScript/TypeScript Tools**:
- ESLint with JSON output
- Prettier (format check)
- TypeScript (strict mode)

**Dependency Audit**:
- composer-audit (Composer packages)
- composer-unused (unused packages)
- npm-audit (JavaScript packages)
- symfony-security-checker

**Code Metrics**:
- PhpMetrics (metrics, coupling, complexity)
- phploc (lines of code analysis)

**Dynamic Analysis** (CI/CD only):
- PHPUnit with coverage (85% threshold)
- Infection mutation testing (80% MSI, 85% covered)

**Output & Reporting**:
- SARIF format for standardization
- HTML report generation
- GitHub PR integration
- Slack integration (optional)
- Fail conditions defined (0 critical issues, <5 high, 85% coverage)

**Performance**:
- Parallel execution with 4 threads
- Result caching

**CI/CD Integration**:
- GitHub Actions support
- Pre-commit hook (quick mode)
- CI step (full mode)

---

## 📂 Directory Structure (Complete)

```
.titan/
├── ROADMAP.yaml                    # Master roadmap (8 phases, 32 weeks)
├── TODO.md                          # Master checklist with status
├── IMPLEMENTATION_GUIDE.md          # Quick reference for Phase 1
├── INDEX.md                         # This file
│
├── issues/                          # Detailed specifications
│   ├── PHASE_1_FOUNDATION.md       # Issues 1.1-1.4 (4 weeks)
│   ├── PHASE_2_KNOWLEDGE.md        # Issues 2.1-2.3 (3.5 weeks)
│   ├── PHASE_3_EXECUTION.md        # Issues 3.1-3.3 (5.5 weeks)
│   ├── PHASE_4_SAFETY.md           # Issues 4.1-4.4 (6 weeks)
│   ├── PHASE_5_VALIDATION.md       # Issues 5.1-5.3 (4.5 weeks)
│   ├── PHASE_6_INTEGRATION.md      # Issues 6.1-6.2 (3.5 weeks)
│   ├── PHASE_7_OBSERVABILITY.md    # Issues 7.1-7.3 (4 weeks)
│   └── PHASE_8_OPERATIONS.md       # Issues 8.1-8.3 (5.5 weeks)
│
├── schemas/                         # JSON Schema validation files
│   ├── agent-manifest.schema.json  # Agent definition schema
│   ├── task-graph.schema.json      # Executable DAG schema
│   ├── capability-registry.schema.json  # Capability registry
│   └── policy.schema.json          # Policy engine schema
│
├── agents/                          # Agent manifests (7 agents)
│   ├── planner.yaml                # Planner agent
│   ├── implementer.yaml            # Implementer agent
│   ├── reviewer.yaml               # Reviewer agent
│   ├── tester.yaml                 # Tester agent
│   ├── security_agent.yaml         # Security agent
│   ├── documentation_agent.yaml    # Documentation agent
│   └── release_agent.yaml          # Release agent
│
├── registry/
│   └── capabilities.yaml           # Global capability registry (50+ capabilities)
│
├── constitution.yaml               # Architecture rules and policies
│
├── memory/
│   ├── README.md                   # Memory system documentation
│   ├── global/                     # Shared principles, standards
│   │   ├── principles.md           # Architecture principles (to create)
│   │   ├── standards.yaml          # Coding standards (to create)
│   │   └── patterns.yaml           # Known patterns (to create)
│   └── repository/                 # Per-repo decisions and defects
│       ├── adr-*.md                # Architecture Decision Records
│       ├── known-defects.yaml      # Known defects and fixes
│       └── ownership.yaml          # File ownership map
│
├── execution/                       # Execution state and artifacts (runtime)
│   ├── sessions/                   # Active/completed task sessions
│   │   └── {session-id}/
│   │       ├── checkpoint-1.json
│   │       ├── branch-memory.yaml
│   │       └── evidence/
│   ├── artifacts/                  # Task output files
│   └── logs/                        # Execution logs
│
├── analysis/
│   ├── config.yaml                 # Static analysis pipeline config
│   └── results/                    # Analysis output (runtime)
│       ├── analysis.sarif          # OWASP SARIF format
│       ├── report.html             # HTML report
│       └── cache/                  # Analysis cache
│
└── plans/                          # Executable task graphs (to create)
    ├── example-plan.yaml           # Example task graph
    └── {feature-name}.yaml         # Feature-specific plans
```

---

## 🚀 Getting Started

### For Project Managers
1. Read **ROADMAP.yaml** - Understand 8-phase plan (32 weeks)
2. Read **TODO.md** - See checklist and current status
3. Read **IMPLEMENTATION_GUIDE.md** - Understand Phase 1 approach

### For Developers (Phase 1)
1. Read **IMPLEMENTATION_GUIDE.md** - Week-by-week guide
2. Read **issues/PHASE_1_FOUNDATION.md** - Detailed specs for issues 1.1-1.4
3. Review **schemas/** - JSON schemas for validation
4. Review **agents/** - Agent manifest examples
5. Start implementing 1.1 (Agent Manifests)

### For Architects
1. Read **ROADMAP.yaml** - High-level design
2. Read **constitution.yaml** - Architectural rules
3. Review **agents/** - Specialist agent design
4. Review **schemas/** - Data structure definitions

### For Security Teams
1. Read **issues/PHASE_4_SAFETY.md** - Security & governance
2. Review **constitution.yaml** - Security policies
3. Review **agents/security_agent.yaml** - Security capabilities
4. Review **analysis/config.yaml** - Security scanning setup

---

## 📊 Implementation Status

| Phase | Name | Status | Effort | Dependencies |
|-------|------|--------|--------|--------------|
| 1 | Foundation | 🔵 Not Started | 4w | None |
| 2 | Knowledge Layer | ⚫ Blocked | 3.5w | Phase 1 |
| 3 | Execution Control | ⚫ Blocked | 5.5w | Phase 1-2 |
| 4 | Safety & Governance | ⚫ Blocked | 6w | Phase 2-3 |
| 5 | Validation & Quality | ⚫ Blocked | 4.5w | Phase 1-4 |
| 6 | Integration | ⚫ Blocked | 3.5w | Phase 1-5 |
| 7 | Observability | ⚫ Blocked | 4w | Phase 1-6 |
| 8 | Operationalization | ⚫ Blocked | 5.5w | Phase 1-7 |

**Total**: 32 weeks (~8 months)

---

## ✅ Success Criteria

### Phase 1 Complete When:
- ✅ Agents have versioned manifests
- ✅ Task graphs are executable
- ✅ State persists across checkpoints
- ✅ Memory system is queryable
- ✅ All tests passing

### Final System Success Metrics:
- ✅ 80%+ of repository tasks completed autonomously
- ✅ All generated code passes automated validation
- ✅ Zero unintended side effects
- ✅ Complete audit trail for every change
- ✅ 20%+ improvement in accuracy/speed per quarter
- ✅ 3-5x increase in team velocity

---

## 🔗 Related Files

**In Repository Root**:
- `.github/workflows/` - CI/CD pipelines (can be integrated with Phase 8)
- `composer.json` - PHP dependencies
- `package.json` - JavaScript dependencies (if applicable)

**In .titan/ (Not Yet Created)**:
- `.titan/plans/` - Executable task graphs (created during Phase 1)
- `.titan/memory/global/` - Shared principles and standards (created during Phase 2)
- `.titan/memory/repository/` - ADRs and defects (created during Phase 2)
- `.titan/execution/` - Runtime session and artifact storage (created during Phase 1)

---

## 📝 Notes

- **All YAML files** are validated against JSON schemas in `.titan/schemas/`
- **All changes** to constitution, policies, agents are version-controlled in Git
- **Memory is Git-based** for auditability and version control
- **Execution state** is stored in `.titan/execution/` and can be checkpointed/resumed
- **Analysis results** are stored in OWASP SARIF format for standardization

---

**System Version**: 1.0.0  
**Last Updated**: 2025-08-15  
**Status**: Specification Complete, Ready for Implementation  
**Next Step**: Begin Phase 1 Implementation (Issue 1.1 - Agent Manifests)
