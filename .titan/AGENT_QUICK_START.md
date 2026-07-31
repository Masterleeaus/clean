# Agent Quick Start Guide

**Version**: 1.0  
**Audience**: All AI agents working on GitHub issues  
**Purpose**: Navigate .titan and understand your workflow quickly

---

## 🎯 You've Been Assigned an Issue

### Step 1: Read the Issue (5 min)
GitHub issue → Read description, acceptance criteria, your assigned agent type

### Step 2: Setup Your Workspace (2 min)
```bash
npm run titan:agent:setup -- --agent-type [code|research|planning|execution|monitoring] --issue [NUMBER]
```

**Creates**:
- `.agent-workspace/` with all templates
- `pass-plan.md` - Your 3-8 execution passes
- `progress/` - Track work each pass
- `findings.md` - Log bugs/improvements
- `knowledge.md` - Learnings to add to .titan

### Step 3: Create Your Branch (2 min)
```bash
git checkout -b agents/[type]/[issue#]-[name] origin/integration
git push -u origin agents/[type]/[issue#]-[name]
```

### Step 4: Plan Your Passes (10 min)
Edit `.agent-workspace/pass-plan.md`
- Define 3-8 execution passes
- Each pass has clear goal
- Example: Investigation → Fix → Hardening → Documentation

### Step 5: Execute & Document (varies)
For each pass:
```bash
# Do the work
# ... make changes, add tests, commit ...

# Document in progress/pass-[N].md
# Log findings in findings.md
git push origin agents/[type]/[issue#]-[name]
```

### Step 6: Final Passes - Update .titan ⚠️
By pass N-1 or N:
```bash
# Update .titan with learnings
# See: .titan/workflows/AGENT_WORKFLOW.md → Section 8

git add .titan/
git commit -m "knowledge: Capture learnings from Issue #[NUMBER]"
git push origin agents/[type]/[issue#]-[name]
```

### Step 7: Create PR
```
Target: integration (NOT main)
Include: Links to .agent-workspace knowledge
Reference: Acceptance criteria met
```

---

## 🗺️ Navigate .titan By Your Role

### I'm a Code Agent (Implementation)

**My .titan Resources**:
- `.titan/docs/agents/AGENT_DEVELOPMENT.md` → Read "Code Agent" section
- `.titan/docs/AGENT_OS.md` → Understand architecture
- `.titan/docs/runtime/RUNTIME_API.md` → API reference when coding
- `.titan/blueprints/04-AI-CORE-BLUEPRINT.md` → Patterns for multi-agent coordination
- `.titan/blueprints/10-WORKFLOW-STATE-MACHINE-BLUEPRINT.md` → Task graph patterns
- `.titan/docs/system-blueprints/07-workflows/` → Workflow implementations

**Common Tasks**:
- [x] Bug fix? → Read `.titan/workflows/AGENT_WORKFLOW.md` → Pass 1-4 example
- [x] Feature? → Start with `.titan/blueprints/` for patterns
- [x] Refactor? → Check `.titan/docs/architecture/` for design principles
- [x] Test improvements? → See test examples in codebase

**Update .titan With**:
- New patterns found
- Best practices discovered
- Code smells you fixed
- Security issues you fixed

---

### I'm a Research Agent (Analysis)

**My .titan Resources**:
- `.titan/docs/agents/AGENT_DEVELOPMENT.md` → Read "Research Agent" section
- `.titan/blueprints/README.md` → Pick high-priority blueprints to research
- `.titan/docs/system-blueprints/` → Deep-dive documentation
- `.titan/MANIFEST.md` → System inventory when auditing
- `.titan/docs/observability/OBSERVABILITY.md` → Audit metrics/logging

**Common Tasks**:
- [x] Security audit? → Check `.titan/blueprints/22-SECURITY-PERMISSIONS-AUDIT-BLUEPRINT.md`
- [x] Code quality? → Review `.titan/docs/CAPABILITIES.md` for what exists
- [x] Dependency audit? → Use `.titan/MANIFEST.md` as reference
- [x] Architecture review? → Read `.titan/blueprints/` first

**Update .titan With**:
- Security findings and fixes
- Code quality improvements
- Performance optimizations found
- Architecture insights
- Best practices discovered

---

### I'm a Planning Agent (Design)

**My .titan Resources**:
- `.titan/docs/agents/AGENT_DEVELOPMENT.md` → Read "Planning Agent" section
- `.titan/blueprints/` → All 34 system design blueprints
- `.titan/docs/system-blueprints/07-workflows/` → Workflow design patterns
- `.titan/blueprints/04-AI-CORE-BLUEPRINT.md` → Multi-agent orchestration
- `.titan/blueprints/10-WORKFLOW-STATE-MACHINE-BLUEPRINT.md` → State machines

**Common Tasks**:
- [x] Architecture design? → Start with `.titan/blueprints/03-FULL-ENGINE-BLUEPRINT.md`
- [x] Workflow design? → Use `.titan/blueprints/10-WORKFLOW-STATE-MACHINE-BLUEPRINT.md`
- [x] Component design? → See `.titan/blueprints/05-MODULE-BLUEPRINT.md`
- [x] System planning? → Review `.titan/blueprints/02-PLATFORM-BLUEPRINT.md`

**Update .titan With**:
- New architectural patterns
- Design decisions with rationale
- System diagrams and models
- Reference implementations

---

### I'm an Execution Agent (DevOps/SRE)

**My .titan Resources**:
- `.titan/docs/agents/AGENT_DEVELOPMENT.md` → Read "Execution Agent" section
- `.titan/scripts/` → Automation scripts (backup, restore, etc.)
- `.titan/config/` → System configuration
- `.titan/docs/observability/OBSERVABILITY.md` → Monitoring setup
- `.titan/blueprints/23-OBSERVABILITY-HEALTH-DOCTOR-BLUEPRINT.md` → Health systems

**Common Tasks**:
- [x] Setup infrastructure? → Use `.titan/scripts/` as templates
- [x] Deployment? → Check `.titan/WORKFLOW.md` for branch strategy
- [x] Configuration? → See `.titan/config/` files
- [x] Migration? → Plan with `.titan/docs/runtime/RUNTIME_API.md`

**Update .titan With**:
- Deployment procedures
- Configuration patterns
- Operational runbooks
- Infrastructure setup guides
- Troubleshooting procedures

---

### I'm a Monitoring Agent (Observability)

**My .titan Resources**:
- `.titan/docs/agents/AGENT_DEVELOPMENT.md` → Read "Monitoring Agent" section
- `.titan/docs/observability/OBSERVABILITY.md` → Complete observability guide
- `.titan/blueprints/23-OBSERVABILITY-HEALTH-DOCTOR-BLUEPRINT.md` → Health monitoring
- `.titan/docs/CAPABILITIES.md` → What to monitor
- `.titan/registry/` → Current registries to monitor

**Common Tasks**:
- [x] Setup monitoring? → Start with `.titan/docs/observability/`
- [x] Create alerts? → See health doctor blueprint
- [x] Metrics collection? → Reference observability guide
- [x] Health checks? → Use agent health monitoring patterns

**Update .titan With**:
- Monitoring setup procedures
- Alert definitions
- Health check procedures
- Performance baselines
- Troubleshooting guides for operations

---

## 📚 Find What You Need

### By Task Type

**"I need to understand how X works"**
1. Check: `.titan/docs/CAPABILITIES.md` - Is it listed?
2. Check: `.titan/MANIFEST.md` - What component handles this?
3. Check: `.titan/docs/AGENT_OS.md` - How is it architected?
4. Check: `.titan/docs/runtime/RUNTIME_API.md` - What's the API?

**"I'm implementing X, where's the pattern?"**
1. Check: `.titan/blueprints/README.md` - Which blueprint covers this?
2. Read: `.titan/blueprints/[number]-[NAME].md` - The relevant blueprint
3. Check: `.titan/docs/system-blueprints/` - Deep-dive documentation
4. Look at: Existing code using similar patterns

**"I found a bug/issue"**
1. Log it in: `.agent-workspace/findings.md`
2. Suggest fix in: `.agent-workspace/knowledge.md`
3. Update .titan with: Security/quality improvements
4. Reference in: PR description

**"I discovered a pattern/best practice"**
1. Document in: `.agent-workspace/knowledge.md`
2. Plan update to: Relevant `.titan/docs/` file
3. Add to: `.titan/blueprints/` if architectural
4. Commit to: `.titan/docs/architecture/PATTERNS.md` if novel

### By Location

**Architecture & Design**
- `.titan/blueprints/` - 34 system design documents
- `.titan/docs/system-blueprints/` - Detailed reference docs
- `.titan/docs/AGENT_OS.md` - Agent OS architecture

**Implementation Reference**
- `.titan/docs/runtime/RUNTIME_API.md` - API classes and methods
- `.titan/docs/agents/AGENT_DEVELOPMENT.md` - Agent development guide
- `.titan/docs/protocols/AGENT_COMMUNICATION.md` - Communication patterns

**Configuration & Setup**
- `.titan/config/` - System configuration files
- `.titan/scripts/` - Automation scripts
- `.titan/registry/` - Current system state

**Workflows & Processes**
- `.titan/workflows/` - Process documentation
- `.titan/WORKFLOW.md` - Git workflow for team
- `.titan/workflows/AGENT_WORKFLOW.md` - Your agent workflow (THIS DOCUMENT)

**Reference & Checklists**
- `.titan/QUICK_REFERENCE.md` - Command cheat sheet
- `.titan/CAPABILITIES.md` - Feature inventory
- `.titan/MANIFEST.md` - System inventory

---

## 🚀 Common Scenarios

### Scenario 1: Bug Fix (Code Agent)

```
1. Read issue → Understand bug
2. Setup workspace → npm run titan:agent:setup --agent-type code --issue 245
3. Plan 4 passes:
   - Pass 1: Investigation (root cause)
   - Pass 2: Fix (implement solution)
   - Pass 3: Hardening (defensive checks)
   - Pass 4: Documentation (update .titan & create PR)
4. Execute each pass → Document progress
5. On Pass 3 or 4: Update .titan with learnings
6. Create PR to integration
```

**Key Resources**:
- `.titan/docs/runtime/RUNTIME_API.md` - Understand APIs
- `.titan/docs/protocols/AGENT_COMMUNICATION.md` - Understand communication
- `.titan/blueprints/22-SECURITY-PERMISSIONS-AUDIT-BLUEPRINT.md` - Security patterns

---

### Scenario 2: Security Audit (Research Agent)

```
1. Read issue → Understand scope
2. Setup workspace → npm run titan:agent:setup --agent-type research --issue 246
3. Plan 5 passes:
   - Pass 1: Initial scan (identify areas)
   - Pass 2: Deep analysis (investigate findings)
   - Pass 3: Risk assessment (prioritize)
   - Pass 4: Recommendations (solutions)
   - Pass 5: Documentation (report + .titan updates)
4. Execute each pass → Log findings
5. On Pass 4: Prepare .titan updates
6. On Pass 5: Update .titan and create PR
```

**Key Resources**:
- `.titan/blueprints/22-SECURITY-PERMISSIONS-AUDIT-BLUEPRINT.md` - Security framework
- `.titan/docs/security/SECURITY_MODEL.md` - Security model
- `.titan/MANIFEST.md` - System inventory to audit

---

### Scenario 3: Architecture Design (Planning Agent)

```
1. Read issue → Understand requirements
2. Setup workspace → npm run titan:agent:setup --agent-type planning --issue 247
3. Plan 4 passes:
   - Pass 1: Requirements & constraints
   - Pass 2: Architecture & design
   - Pass 3: Implementation planning
   - Pass 4: Documentation & .titan updates
4. Execute each pass → Create designs/plans
5. On Pass 3 or 4: Update .titan with new architecture
6. Create PR to integration
```

**Key Resources**:
- `.titan/blueprints/04-AI-CORE-BLUEPRINT.md` - AI architecture
- `.titan/blueprints/10-WORKFLOW-STATE-MACHINE-BLUEPRINT.md` - Task graphs
- `.titan/docs/system-blueprints/` - Reference implementations

---

### Scenario 4: Deployment (Execution Agent)

```
1. Read issue → Understand deployment needs
2. Setup workspace → npm run titan:agent:setup --agent-type execution --issue 248
3. Plan 4 passes:
   - Pass 1: Setup & preparation
   - Pass 2: Deployment execution
   - Pass 3: Verification & validation
   - Pass 4: Documentation & .titan updates
4. Execute each pass → Deploy and verify
5. On Pass 3 or 4: Update .titan with procedures
6. Create PR to integration
```

**Key Resources**:
- `.titan/scripts/` - Automation script examples
- `.titan/config/` - Configuration reference
- `.titan/WORKFLOW.md` - Branch strategy for deployments

---

### Scenario 5: Monitoring Setup (Monitoring Agent)

```
1. Read issue → Understand monitoring scope
2. Setup workspace → npm run titan:agent:setup --agent-type monitoring --issue 249
3. Plan 4 passes:
   - Pass 1: Instrumentation setup
   - Pass 2: Alerts & thresholds
   - Pass 3: Analysis & dashboards
   - Pass 4: Documentation & .titan updates
4. Execute each pass → Setup monitoring
5. On Pass 3 or 4: Update .titan with monitoring guide
6. Create PR to integration
```

**Key Resources**:
- `.titan/docs/observability/OBSERVABILITY.md` - Observability guide
- `.titan/blueprints/23-OBSERVABILITY-HEALTH-DOCTOR-BLUEPRINT.md` - Health systems
- `.titan/docs/CAPABILITIES.md` - What to monitor

---

## ⚠️ CRITICAL REMINDERS

1. **MUST CREATE MULTI-PASS PLAN** (3-8 passes minimum)
   - Not a single commit
   - Each pass documented
   - Clear progression

2. **MUST UPDATE .TITAN** (by pass N-1 at latest)
   - Add patterns discovered
   - Add best practices
   - Update registry/schemas
   - Add new documentation

3. **MUST LOG FINDINGS**
   - Bugs and improvements
   - Security issues
   - Performance insights
   - Learnings for next agent

4. **MUST TARGET INTEGRATION** (not main)
   - Main is owner-only
   - Integration is team staging area
   - Your PR goes to integration
   - Owner syncs to main

5. **MUST DOCUMENT PROGRESS**
   - Each pass gets its own file
   - Progress tracked in `progress/pass-[N].md`
   - Findings in `findings.md`
   - Knowledge in `knowledge.md`

---

## 🔗 Quick Links

**Start Here**:
- `.titan/workflows/AGENT_WORKFLOW.md` - Complete workflow (this level of detail)
- `.titan/AGENT_QUICK_START.md` - This file
- `.titan/docs/agents/AGENT_DEVELOPMENT.md` - Your agent type guide

**System Blueprints**:
- `.titan/blueprints/README.md` - All 34 blueprints indexed
- `.titan/blueprints/04-AI-CORE-BLUEPRINT.md` - Multi-agent orchestration
- `.titan/blueprints/10-WORKFLOW-STATE-MACHINE-BLUEPRINT.md` - Task graphs

**Implementation Reference**:
- `.titan/docs/runtime/RUNTIME_API.md` - Complete API reference
- `.titan/docs/protocols/AGENT_COMMUNICATION.md` - How agents communicate
- `.titan/docs/CAPABILITIES.md` - All system capabilities

**Team Workflow**:
- `.titan/WORKFLOW.md` - Git workflow (feature → integration → main)
- `.titan/PROTECTION.md` - Protection & backup system
- `.titan/README.md` - System overview

---

## 📞 Need Help?

1. **Confused about workflow?**
   → Read: `.titan/workflows/AGENT_WORKFLOW.md`

2. **Don't know which agent type?**
   → Read: `.agent-workspace/AGENT-ROUTING.md`

3. **Need to find something?**
   → Check: `.titan/docs/README.md` or `.titan/MANIFEST.md`

4. **Need examples?**
   → Look at: Existing code or `.titan/blueprints/`

5. **Need documentation?**
   → Check: `.titan/docs/` for your agent type

---

**Status**: ✅ Ready for agent work  
**Last Updated**: July 31, 2026  
**Workflow**: Multi-pass execution with .titan knowledge updates (MANDATORY)
