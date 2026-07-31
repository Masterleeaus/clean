# Phase 2: Knowledge Layer (Weeks 5-8)

Deep understanding of codebase through multiple lens systems.

## Issue 2.1: Knowledge Graph Construction & Querying

**Effort**: 2.5 weeks  
**Priority**: P0 - Enables intelligent routing  
**Status**: `todo`  
**Dependencies**: Phase 1 (1.1, 1.2, 1.3, 1.4)

### Description

Build a queryable graph connecting files, classes, services, routes, events, tests, and data models so agents can understand relationships, dependencies, and impact of changes.

### Graph Node Types

- **Files**: PHP, YAML, JSON, Markdown, configuration
- **Classes**: Service, Repository, Controller, Model, Listener, Job
- **Functions**: Public methods, helpers, event handlers
- **Routes**: HTTP endpoints with middleware, verbs, parameters
- **Events**: Custom events and listeners
- **Services**: Registered in container, dependencies
- **Models**: Database tables, relations, scopes
- **Migrations**: Schema changes, reversibility
- **Tests**: Unit, Feature, Integration tests
- **Extensions**: Plugins and their capabilities
- **Config**: Environment, runtime configuration

### Graph Edge Types

- **imports**: File A imports/uses File B
- **extends**: Class A extends/implements Class B
- **calls**: Function A calls Function B
- **handles**: Event listener handles Event
- **depends_on**: Service A depends on Service B
- **belongs_to**: Method belongs to Class
- **tests**: Test tests Function/Class
- **migrates**: Migration affects Table
- **provides**: Extension provides Capability
- **modifies**: Change modifies Node

### Acceptance Criteria

- [ ] Knowledge graph populated from code analysis
- [ ] 2000+ nodes (files, classes, functions) indexed
- [ ] 5000+ edges representing relationships
- [ ] Query engine supports pattern matching
- [ ] Impact analysis traces changes through graph
- [ ] Export graph for visualization tools
- [ ] Performance: queries return in <100ms

### Key Tasks

1. Build code parser for PHP (classes, methods, imports)
2. Build code parser for routes and configuration
3. Build code parser for tests and test coverage
4. Implement graph storage (Neo4j or in-memory)
5. Implement relationship discovery
6. Build query engine (DSL or GraphQL)
7. Create graph visualization (Mermaid, D3.js)
8. Add impact analyzer (who changes this? what breaks?)
9. Write comprehensive tests

### Deliverables

- Knowledge Graph schema and storage
- Code parsers (PHP, routes, tests)
- Query engine with DSL
- Impact analyzer
- Graph visualization tool

---

## Issue 2.2: Repository Constitution & Architectural Boundaries

**Effort**: 1.5 weeks  
**Priority**: P0 - Defines allowed operations  
**Status**: `todo`  
**Dependencies**: 2.1

### Description

Codify architectural rules, ownership boundaries, service contracts, and enforcement policies in machine-readable format so violations are detected and prevented.

### Constitution Structure

```yaml
version: 1.0
name: "Clean SaaS"

ownership:
  bounded_contexts:
    - name: "Payment"
      files: ["src/Payment/**"]
      owner: "billing-team"
      
    - name: "Auth"
      files: ["src/Auth/**"]
      owner: "security-team"

services:
  - name: "PaymentService"
    public_interface: ["charge()", "refund()"]
    private: ["sendToGateway()"]
    
boundaries:
  - from: "Auth"
    to: "Payment"
    allowed: ["charge", "refund"]
    forbidden: ["internal methods"]

standards:
  - name: "Service Dependency Injection"
    pattern: "class Service { __construct(Dependency $dep) {} }"
    violation: "new Service() or $service->method()"
```

### Acceptance Criteria

- [ ] Constitution file defines all boundaries and rules
- [ ] Ownership map covers all packages/namespaces
- [ ] Service contracts are explicit and validated
- [ ] Violations are detectable at merge time
- [ ] Constitution is human-readable and machine-parseable
- [ ] Changes to constitution require approval

### Key Tasks

1. Design constitution schema
2. Build boundary validator
3. Implement ownership enforcement
4. Create service contract validator
5. Add approval gate for constitution changes
6. Build violation reporter
7. Write comprehensive tests

### Deliverables

- `.titan/constitution.yaml` - Architecture ruleset
- Boundary enforcer service
- Ownership system
- Violation detector and reporter

---

## Issue 2.3: Architectural Drift Detection & Health Scoring

**Effort**: 2 weeks  
**Priority**: P0 - Prevents decay  
**Status**: `todo`  
**Dependencies**: 2.1, 2.2

### Description

Automatically detect violations, duplicates, anti-patterns, and ownership issues that creep in over time and report them with suggested fixes.

### Drift Types

- **Boundary Violations**: Code crossing service boundaries
- **Circular Dependencies**: Cyclic imports or references
- **Orphaned Code**: Functions/classes with no tests
- **Duplicated Logic**: Copy-pasted implementations
- **Anti-Patterns**: Service locator, global state, tight coupling
- **Dead Code**: Unreferenced functions, classes, test files
- **Test Gaps**: Critical functions without tests
- **Documentation Gaps**: Public APIs without docblocks
- **Configuration Drift**: Inconsistent config patterns
- **Dependency Bloat**: Unused packages, outdated versions

### Health Scoring Formula

```
Health Score = (100 - violations) × coverage_factor × architecture_factor

violations = boundary_violations + anti_patterns + duplicates + dead_code
coverage_factor = (tested_lines / total_lines) × 100
architecture_factor = (enforced_rules / total_rules) × 100
```

### Acceptance Criteria

- [ ] Scan identifies all drift types
- [ ] Health score updates weekly
- [ ] Violations ranked by impact/severity
- [ ] Suggested fixes provided for each violation
- [ ] Trend analysis shows improvement/decay
- [ ] Reports generated automatically

### Key Tasks

1. Define drift violation types and scoring
2. Implement boundary crossing detector
3. Implement circular dependency detector
4. Build orphaned code analyzer
5. Build duplicate code detector
6. Implement anti-pattern scanner
7. Build health scorer
8. Create drift report generator
9. Write comprehensive tests

### Deliverables

- Drift detector service
- Health score calculator
- Automated reporting
- Trend tracking

