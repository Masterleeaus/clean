# Superpowers Architecture & Planning Guide

**Tool**: Superpowers - ChatGPT Plugin  
**Purpose**: Feature design, system architecture planning, implementation planning, TDD workflows  
**Best For**: Planning Agents, all agents during design/planning phases

---

## When to Use

### Feature Planning
- Breaking down complex features
- Defining acceptance criteria
- Planning implementation steps
- Identifying dependencies

### Architecture Design
- Designing system components
- Planning data models
- Defining API contracts
- Designing scalability approach

### Implementation Planning
- Creating step-by-step implementation plans
- Identifying critical paths
- Planning test strategy (TDD)
- Defining code review process

### Rigorous Engineering
- Using TDD methodology
- Designing test-first approach
- Planning comprehensive testing
- Ensuring quality gates

---

## How to Use

### Design New Feature
```
"Use Superpowers to design a new [feature] that:
- [Requirement 1]
- [Requirement 2]
- [Requirement 3]

Include:
- API design
- Data model
- User flows
- Acceptance criteria
- Implementation steps"

Example:
"Use Superpowers to design a job approval workflow that:
- Manager reviews pending jobs
- Can approve or request changes
- Notifies staff of decisions
- Tracks approval history

Include API design, database schema, UI wireframes, and 10 implementation steps"
```

### Architecture Design
```
"Use Superpowers to design [system component]:
- Current state: [what exists]
- New requirements: [what's needed]
- Constraints: [limitations]

Provide architecture diagram, component interactions, and scale plan"
```

### Implementation Planning
```
"Use Superpowers to create implementation plan for [feature]:
- Show all steps from design to deployment
- Identify critical path
- Highlight dependencies
- Plan testing strategy"
```

### Test-Driven Development
```
"Use Superpowers to plan a TDD approach for [feature]:
- What tests to write first
- Red-green-refactor cycles
- Test coverage goals
- Verification approach"
```

---

## Integration with Agent Workflow

### Planning Agent (Pass 2)
- **Goal**: Architecture & Design
- **Use Superpowers to**: Design system, plan components
- **Output**: Architecture documentation, design specs

### Planning Agent (Pass 3)
- **Goal**: Implementation Planning
- **Use Superpowers to**: Create step-by-step plan
- **Output**: Implementation roadmap

### Code Agent (Pass 1)
- **Goal**: Investigation
- **Use Superpowers to**: Plan approach to fix
- **Output**: Structured fix approach

### Code Agent (Pass 2)
- **Goal**: Implementation
- **Use Superpowers to**: Plan implementation approach
- **Output**: Step-by-step implementation guide

---

## Superpowers Capabilities

| Capability | Details |
|-----------|---------|
| **Feature Design** | Break down features, define acceptance criteria, plan user flows |
| **Architecture** | System design, component planning, scalability strategies |
| **API Design** | Endpoint design, request/response models, error handling |
| **Data Modeling** | Database schema, relationships, constraints |
| **Implementation** | Step-by-step plans, pseudocode, critical path |
| **Testing** | TDD workflows, test cases, coverage planning |
| **Code Review** | Process design, review criteria, quality gates |
| **Documentation** | Planning docs, architecture diagrams, runbooks |

---

## Output Types

**Superpowers Produces:**
- Feature specifications with acceptance criteria
- Architecture diagrams and documentation
- API specifications (OpenAPI-like)
- Database schemas (ERD)
- Implementation step lists
- Test plans and TDD workflows
- Code review guidelines
- Risk assessments

---

## Capabilities & Limitations

**Strengths:**
- Rigorous feature breakdown
- Comprehensive planning
- Implementation step clarity
- TDD workflow guidance
- Architecture validation
- Dependency identification
- Risk assessment
- Documentation generation

**Limitations:**
- Planning only (doesn't execute code)
- Relies on other tools to implement
- May miss domain-specific nuances
- Requires refinement for edge cases
- Not a replacement for domain expertise

---

## Workflow Integration

### Planning Agent Example (Full Workflow)
```
Pass 1: Requirements & Scope
  → Use Superpowers to understand scope
  → Break down requirements
  → Identify constraints
  → Define acceptance criteria

Pass 2: Architecture & Design
  → Use Superpowers to design system architecture
  → Plan component interactions
  → Design data models
  → Plan API contract

Pass 3: Implementation Planning
  → Use Superpowers to create implementation steps
  → Identify critical path
  → Plan testing strategy
  → Define code review process

Pass 4: Documentation
  → Use Goodnotes to visualize architecture
  → Document in .titan
  → Update blueprints with learnings
```

---

## Examples in Practice

### Example 1: Complete Feature Design
```
Task: "Design job approval workflow"
Query: "Use Superpowers to design job approval workflow:
- Requirement: Manager reviews pending jobs
- Requirement: Can approve or request revisions
- Requirement: Tracks approval history
- Requirement: Real-time notifications
- Constraint: Must work for 100+ concurrent managers
- Constraint: Mobile-friendly interface

Provide: API design, database schema, UI flow, 15 implementation steps"

Output: Complete design specification with all details
Next: Code Agent uses this to implement
```

### Example 2: System Architecture
```
Task: "Design payment system"
Query: "Use Superpowers to design payment processing system:
- Current: Direct payment handling
- New: Support multiple payment methods (card, ACH, crypto)
- Requirement: PCI-DSS compliance
- Requirement: Real-time settlement
- Constraint: Handle 1000 transactions/second

Provide: Architecture, component design, API spec, deployment plan"

Output: Production-grade architecture design
Next: Code Agent implements using Build MCP Apps
```

### Example 3: Implementation Plan
```
Task: "Implement authentication redesign"
Query: "Use Superpowers to create implementation plan:
- From: Session-based auth
- To: JWT token-based auth
- Requirement: No service downtime
- Requirement: Support both methods during transition
- Requirement: Comprehensive tests

Provide: 20+ step plan with dependencies, tests, verification"

Output: Phased implementation approach with testing
Next: Code Agent executes plan step-by-step
```

### Example 4: TDD Approach
```
Task: "Build invoice calculation engine"
Query: "Use Superpowers to plan TDD for invoice calculation:
- Requirement: Support multiple rate types (hourly, daily, project)
- Requirement: Tax calculation for multiple jurisdictions
- Requirement: Discount application rules
- Constraint: Must pass strict audit

Provide: Test-first workflow, test cases, red-green-refactor cycles"

Output: TDD plan with all test cases
Next: Code Agent implements tests first, then code
```

---

## Tips for Effective Use

1. **Be Comprehensive**: Provide all requirements and constraints
2. **Ask for Breakdown**: Request detailed step-by-step plans
3. **Plan Dependencies**: Ask for critical path and sequencing
4. **Plan Testing**: Always request testing strategy
5. **Risk Assessment**: Ask for risks and mitigations
6. **Validate Design**: Have domain experts review outputs

---

## Common Planning Tasks

1. **Feature Specification**: Requirements → Acceptance criteria → Implementation steps
2. **Architecture Design**: Current state → New design → Component planning
3. **API Contract**: Endpoints → Request/response → Error handling
4. **Data Model**: Entities → Relationships → Schema
5. **Implementation Road**: Goal → Steps → Dependencies → Timeline
6. **Test Strategy**: Coverage goals → Test types → TDD cycles

---

## Design Artifacts

**Superpowers Produces:**
- Feature specifications document
- Architecture diagrams (text-based, for Goodnotes to visualize)
- API specifications (OpenAPI-like)
- Database schemas (SQL DDL)
- Implementation step lists (pseudocode)
- Test plans (test case matrices)
- Risk assessments
- Verification criteria

---

## Integration with Goodnotes

**Workflow:**
1. Use Superpowers to design (text-based)
2. Use Goodnotes to visualize (diagrams)
3. Design is now visual + detailed
4. Share both for comprehensive understanding

---

## Related Tools

- **GitHub**: Use to understand current implementation before designing
- **Goodnotes**: Visualize Superpowers designs as diagrams
- **CodeRabbit**: Review implementation of Superpowers design
- **Build Web Apps**: Implement UI from Superpowers design
- **Build MCP Apps**: Implement API from Superpowers design
- **Process Documentation AI**: Document Superpowers output

---

## Best Practices

1. **Collaborative Design**: Share Superpowers output with team
2. **Validate Early**: Get feedback before implementation
3. **Document Decisions**: Capture WHY, not just WHAT
4. **Risk Planning**: Always consider failure scenarios
5. **Testability**: Design with testing in mind from start
6. **Scalability**: Plan for 10x growth in requirements

---

**Status**: Ready to use (Free tier available)  
**Last Updated**: July 31, 2026
