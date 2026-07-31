#!/usr/bin/env node

/**
 * Agent Workspace Setup
 *
 * Initializes a complete agent workspace for multi-pass task execution.
 * Creates templates, progress tracking, and links to .titan resources.
 *
 * Usage:
 *   npm run titan:agent:setup -- --agent-type code --issue 245
 *   npm run titan:agent:setup -- --agent-type research --issue 246 --title "Audit security gaps"
 */

const fs = require('fs');
const path = require('path');

// Parse arguments
const args = process.argv.slice(2);
const config = {};

for (let i = 0; i < args.length; i += 2) {
  const key = args[i].replace(/^--/, '');
  const value = args[i + 1];
  config[key] = value;
}

const { agentType = 'code', issue = '000', title = 'Issue' } = config;

if (!issue || issue === '000') {
  console.error('❌ Error: --issue is required (e.g., --issue 245)');
  process.exit(1);
}

if (!['code', 'research', 'planning', 'execution', 'monitoring'].includes(agentType)) {
  console.error(`❌ Error: Invalid agent type. Must be one of: code, research, planning, execution, monitoring`);
  process.exit(1);
}

const workspaceDir = `.agent-workspace`;

// Create workspace directories
function createDirectories() {
  const dirs = [
    workspaceDir,
    path.join(workspaceDir, 'progress'),
    path.join(workspaceDir, 'logs'),
  ];

  dirs.forEach(dir => {
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
      console.log(`✅ Created: ${dir}`);
    }
  });
}

// Create issue metadata
function createIssueMetadata() {
  const metadata = {
    issue_number: parseInt(issue),
    issue_title: title,
    agent_type: agentType,
    created_at: new Date().toISOString(),
    branch_name: `agents/${agentType}/${issue}-${title.toLowerCase().replace(/\s+/g, '-').slice(0, 40)}`,
    workspace: workspaceDir,
  };

  fs.writeFileSync(
    path.join(workspaceDir, `issue-${issue}.json`),
    JSON.stringify(metadata, null, 2)
  );

  console.log(`✅ Created: issue-${issue}.json`);
}

// Create workspace README
function createWorkspaceReadme() {
  const content = `# Agent Workspace for Issue #${issue}

**Issue**: ${title}
**Agent Type**: ${agentType}
**Created**: ${new Date().toISOString()}

## Quick Start

1. **Understand the issue**
   - Read: https://github.com/Masterleeaus/clean/issues/${issue}
   - Review acceptance criteria
   - Note any dependencies

2. **Create your branch**
   \`\`\`bash
   git checkout -b agents/${agentType}/${issue}-[descriptor] origin/integration
   git push -u origin agents/${agentType}/${issue}-[descriptor]
   \`\`\`

3. **Create your pass plan**
   - Edit: \`pass-plan.md\`
   - Define 3-8 execution passes
   - Each pass has clear goal and output

4. **Execute each pass**
   - Do the work
   - Document progress in \`progress/pass-[N].md\`
   - Commit with: \`pass-[N]: [Goal]\`

5. **Track findings**
   - Log bugs/improvements in \`findings.md\`
   - Record learnings in \`knowledge.md\`

6. **Final pass: Update .titan**
   - By pass N-1 or N, update .titan documentation
   - Add new patterns to docs
   - Update registry if needed
   - Commit .titan updates

7. **Create PR**
   - Target: \`integration\` (not main)
   - Include knowledge summary
   - Reference acceptance criteria

## File Structure

- **issue-${issue}.json** - Issue metadata
- **pass-plan.md** - Your 3-8 pass execution plan
- **progress/** - One file per pass (pass-1.md, pass-2.md, etc.)
- **findings.md** - Bugs, improvements, security issues discovered
- **knowledge.md** - Patterns, best practices, learnings to add to .titan
- **logs/** - Optional: execution logs

## Workflow

### Pass Structure (Example)

\`\`\`
Pass 1: Investigation
  → Read code, identify issue, write test
  → Output: Root cause analysis + failing test

Pass 2: Fix Implementation
  → Implement solution, test locally
  → Output: Working fix + passing tests

Pass 3: Hardening
  → Add checks, improve error handling
  → Output: Robust implementation

Pass 4: Documentation & Integration  ⚠️ CRITICAL
  → Update .titan, create PR
  → Output: Complete documentation
\`\`\`

### Documentation Requirements

**By Pass N-1 (Second-to-Last)**:
- Compile all findings and learnings
- Prepare .titan updates

**On Pass N (Final Pass)**:
- Implement .titan updates
- Update documentation
- Update registry
- Commit .titan changes
- Create PR

## Required Updates to .titan

You MUST update .titan during your final passes:

- [ ] Add new patterns to \`.titan/docs/architecture/PATTERNS.md\`
- [ ] Document best practices in \`.titan/docs/agents/BEST_PRACTICES.md\`
- [ ] Update security checklist if needed
- [ ] Update registry entries if components changed
- [ ] Create new doc if topic is novel
- [ ] Reference relevant blueprints in \`.titan/blueprints/\`

See: \`.titan/workflows/AGENT_WORKFLOW.md\` → Section 8: Update .titan

## Resources

### Agent Development
- \`.titan/docs/agents/AGENT_DEVELOPMENT.md\` - Your agent archetype guide
- \`.titan/docs/AGENT_OS.md\` - Agent OS overview
- \`.titan/MANIFEST.md\` - Complete system inventory

### Patterns & Blueprints
- \`.titan/blueprints/README.md\` - All 34 system design blueprints
- \`.titan/docs/system-blueprints/\` - Detailed documentation

### Reference
- \`.titan/docs/QUICK_REFERENCE.md\` - Command cheat sheet
- \`.titan/docs/CAPABILITIES.md\` - Complete feature inventory

### Communication
- \`.titan/docs/protocols/AGENT_COMMUNICATION.md\` - How agents communicate
- \`.titan/docs/observability/OBSERVABILITY.md\` - Logging & tracing

## Important Notes

1. **Branch naming matters**: Use \`agents/[type]/[issue#]-[name]\` exactly
2. **Multi-pass execution is mandatory**: Not a single commit
3. **Document every pass**: Progress tracking is required
4. **.titan updates are critical**: Do by pass N-1 at latest
5. **Findings matter**: Log bugs/improvements even if not in original issue
6. **Knowledge capture is mandatory**: Add learnings to .titan

## Workflow Diagram

\`\`\`
Issue #${issue}
  ↓
Setup workspace (this script)
  ↓
Create branch + push
  ↓
Plan passes (3-8)
  ↓
Pass 1: [Goal] → Commit
  ↓
Pass 2: [Goal] → Commit
  ↓
[Continue...]
  ↓
Pass N-1: Review findings + prepare .titan updates ⚠️
  ↓
Pass N: Update .titan + Create PR ⚠️
  ↓
PR Review & Merge to integration
  ↓
Done
\`\`\`

## Commands

\`\`\`bash
# Update progress after each pass
echo "## Pass 1: [Goal]

Work completed:
- Task 1
- Task 2

Findings:
- [Finding]

Next pass:
- [Next goal]" > progress/pass-1.md

# Track findings
echo "- **Bug**: [Description] → [Location]" >> findings.md

# Track knowledge
echo "- **Pattern**: [New understanding]" >> knowledge.md

# Commit work
git add .
git commit -m "pass-1: [Goal]

- What you did
- Key changes
- What's working

Issue #${issue}"

git push origin agents/${agentType}/${issue}-[name]
\`\`\`

## Questions?

See: \`.titan/workflows/AGENT_WORKFLOW.md\` for complete agent workflow guide.

---

**Agent Type**: ${agentType}
**Workspace Created**: ${new Date().toISOString()}
**Ready for**: Multi-pass task execution
`;

  fs.writeFileSync(
    path.join(workspaceDir, 'WORKSPACE-README.md'),
    content
  );

  console.log(`✅ Created: WORKSPACE-README.md`);
}

// Create pass plan template
function createPassPlan() {
  const passPlans = {
    code: `# Pass Plan for Issue #${issue}: ${title}

## Strategy
Work through bug fix/feature in logical phases:
Investigation → Implementation → Hardening → Documentation

## Pass 1: Investigation & Root Cause
Understand the problem and identify where to fix it.

- [ ] Read related code
- [ ] Trace execution flow
- [ ] Write failing test case
- [ ] Identify root cause
- [ ] Document findings

**Output**: Root cause analysis + failing test

## Pass 2: Fix Implementation
Implement the solution.

- [ ] Implement fix
- [ ] Verify test passes
- [ ] Check for regressions
- [ ] Add inline comments
- [ ] Commit work

**Output**: Working fix + passing tests

## Pass 3: Hardening
Make the fix robust and defensive.

- [ ] Add defensive checks
- [ ] Improve error handling
- [ ] Add logging
- [ ] Consider edge cases
- [ ] Add related improvements

**Output**: Hardened implementation

## Pass 4: Documentation & Integration ⚠️ CRITICAL
Complete documentation and prepare for merge.

- [ ] Update README if needed
- [ ] Add code comments
- [ ] Update .titan knowledge base
- [ ] Commit .titan updates
- [ ] Prepare PR

**Output**: Complete documentation + PR ready for review
`,
    research: `# Pass Plan for Issue #${issue}: ${title}

## Strategy
Research phase by phase, documenting findings and recommendations.

## Pass 1: Initial Investigation
Gather information and understand the scope.

- [ ] Define research scope
- [ ] Identify key areas
- [ ] Collect baseline data
- [ ] Note initial observations

**Output**: Research scope + baseline findings

## Pass 2: Deep Analysis
Analyze findings in detail.

- [ ] Study components/systems
- [ ] Test hypotheses
- [ ] Document patterns
- [ ] Identify gaps

**Output**: Detailed analysis + patterns

## Pass 3: Recommendations
Synthesize findings into recommendations.

- [ ] Prioritize findings
- [ ] Develop solutions
- [ ] Estimate effort
- [ ] Create action plan

**Output**: Prioritized recommendations + effort estimates

## Pass 4: Documentation & Integration ⚠️ CRITICAL
Document findings for team and update .titan.

- [ ] Write research report
- [ ] Create summary for team
- [ ] Update .titan knowledge base
- [ ] Commit .titan updates

**Output**: Complete research documentation + PR
`,
    planning: `# Pass Plan for Issue #${issue}: ${title}

## Strategy
Create comprehensive plan through iterative refinement.

## Pass 1: Requirements & Scope
Understand what needs to be done.

- [ ] Clarify requirements
- [ ] Identify constraints
- [ ] Define scope
- [ ] List dependencies

**Output**: Requirements document

## Pass 2: Architecture & Design
Design the solution.

- [ ] Create architecture diagram
- [ ] Define data models
- [ ] Plan components
- [ ] Document interfaces

**Output**: Architecture + design document

## Pass 3: Implementation Plan
Break design into implementable work.

- [ ] Create work breakdown
- [ ] Define implementation phases
- [ ] Estimate effort per phase
- [ ] Identify risks

**Output**: Detailed implementation plan

## Pass 4: Documentation & Integration ⚠️ CRITICAL
Finalize plan and update .titan.

- [ ] Review plan for completeness
- [ ] Create summary documentation
- [ ] Update .titan with patterns
- [ ] Prepare for team review

**Output**: Complete plan + PR with design docs
`,
    execution: `# Pass Plan for Issue #${issue}: ${title}

## Strategy
Execute the work in logical phases, delivering value each pass.

## Pass 1: Setup & Foundation
Prepare everything needed.

- [ ] Setup infrastructure
- [ ] Create base structures
- [ ] Setup testing framework
- [ ] Verify setup works

**Output**: Working foundation

## Pass 2: Core Functionality
Implement main features.

- [ ] Build core components
- [ ] Implement main logic
- [ ] Add basic tests
- [ ] Verify functionality

**Output**: Core functionality working

## Pass 3: Enhancement & Polish
Add robustness and refinement.

- [ ] Add edge case handling
- [ ] Improve error messages
- [ ] Add logging
- [ ] Refine interfaces

**Output**: Polished implementation

## Pass 4: Documentation & Integration ⚠️ CRITICAL
Complete everything and prepare for review.

- [ ] Add documentation
- [ ] Complete test coverage
- [ ] Update .titan
- [ ] Prepare PR

**Output**: Production-ready work + PR
`,
    monitoring: `# Pass Plan for Issue #${issue}: ${title}

## Strategy
Setup monitoring progressively with validation at each step.

## Pass 1: Baseline & Instrumentation
Instrument the system.

- [ ] Identify metrics to track
- [ ] Setup collectors
- [ ] Create dashboards
- [ ] Verify data collection

**Output**: Working instrumentation

## Pass 2: Alerts & Thresholds
Setup alerting.

- [ ] Define alert conditions
- [ ] Setup notification channels
- [ ] Test alert firing
- [ ] Tune thresholds

**Output**: Working alerts

## Pass 3: Analysis & Insights
Create analysis and documentation.

- [ ] Analyze baseline data
- [ ] Document patterns
- [ ] Create playbooks
- [ ] Setup runbooks

**Output**: Analysis docs + playbooks

## Pass 4: Documentation & Integration ⚠️ CRITICAL
Complete monitoring setup and update .titan.

- [ ] Document monitoring setup
- [ ] Update .titan knowledge base
- [ ] Create operator guide
- [ ] Prepare PR

**Output**: Complete monitoring + PR
`,
  };

  const plan = passPlans[agentType] || passPlans.code;

  fs.writeFileSync(
    path.join(workspaceDir, 'pass-plan.md'),
    plan
  );

  console.log(`✅ Created: pass-plan.md (${agentType} template)`);
}

// Create findings template
function createFindingsTemplate() {
  const content = `# Findings for Issue #${issue}

**Agent Type**: ${agentType}
**Created**: ${new Date().toISOString()}

## Bugs Found (Not in Original Issue)

Example format:
\`\`\`
- **Bug Name**: [Description of bug]
  - **Location**: File: [path:line]
  - **Impact**: [What breaks]
  - **Fix**: [How to fix]
  - **Related Issue**: [If known]
\`\`\`

---

## Code Smells

Example format:
\`\`\`
- **Smell**: [What smells]
  - **Location**: [Where in code]
  - **Improvement**: [How to fix]
  - **Reasoning**: [Why better]
\`\`\`

---

## Performance Issues

Example format:
\`\`\`
- **Slow Operation**: [What's slow]
  - **Measurement**: [Current performance]
  - **Cause**: [Why it's slow]
  - **Improvement**: [How to speed up]
  - **Effort**: [Estimated work]
\`\`\`

---

## Security Concerns

Example format:
\`\`\`
- **Vulnerability**: [What's at risk]
  - **Severity**: [Low/Medium/High/Critical]
  - **Cause**: [Root cause]
  - **Fix**: [How to address]
  - **Testing**: [How to verify fix]
\`\`\`

---

## Design Improvements

Example format:
\`\`\`
- **Idea**: [Better way to do this]
  - **Current**: [How it works now]
  - **Proposed**: [How it could work]
  - **Benefit**: [Why it's better]
  - **Effort**: [Estimated work]
  - **Priority**: [Low/Medium/High]
\`\`\`

---

## Missing Documentation

Example format:
\`\`\`
- **Component**: [Name] lacks [what]
  - **Impact**: [Why this matters]
  - **Suggested Addition**: [What to document]
  - **Location**: [Where to add]
\`\`\`

---

## Duplicate or Related Issues

- Related to Issue #[NUMBER]
- Similar to Issue #[NUMBER]

---

**Instructions**:
- Add findings as you discover them
- Use consistent format
- Reference exact line numbers or file paths
- Keep severity accurate
- Suggest solutions, not just problems
`;

  fs.writeFileSync(
    path.join(workspaceDir, 'findings.md'),
    content
  );

  console.log(`✅ Created: findings.md`);
}

// Create knowledge template
function createKnowledgeTemplate() {
  const content = `# Knowledge Captured from Issue #${issue}

**Agent Type**: ${agentType}
**Issue**: ${title}

This is where you capture patterns, best practices, and learnings to add to .titan.

---

## New Patterns Discovered

### Pattern: [Pattern Name]
- **What**: [Description of pattern]
- **Where Found**: [Files/systems that use it]
- **Why It Works**: [Technical explanation]
- **When to Use**: [Conditions for applying]
- **When NOT to Use**: [When to avoid]
- **Example**:
  \`\`\`javascript
  // Example code
  \`\`\`
- **Related Patterns**: [Other similar patterns]
- **Reference**: [Blueprints or docs that cover this]

---

## Best Practices Discovered

### Practice: [Name of Best Practice]
- **What**: [Description]
- **Why**: [Reasoning and benefits]
- **How**: [Step-by-step or example]
- **Tools**: [What to use]
- **Common Mistakes**: [What to avoid]
- **Evidence**:
  - From: Issue #${issue}
  - Observed in: [Where/how this helped]

---

## Gotchas & Edge Cases

### Gotcha: [Name of Common Mistake]
- **What Happens**: [Symptom when you hit this]
- **Root Cause**: [Why it happens]
- **Prevention**: [How to avoid]
- **Recovery**: [What to do if you hit it]
- **Example**: [Code or scenario]

---

## Architecture Insights

### Insight: [Understanding About System]
- **What**: [The insight]
- **Why It Matters**: [Impact]
- **Affects**: [What systems/components]
- **Recommendation**: [What to do]
- **Reference**: [Related blueprints]

---

## Security Findings

### Issue: [Vulnerability or Risk]
- **Severity**: [Low/Medium/High/Critical]
- **What's At Risk**: [What could be compromised]
- **Root Cause**: [Why it exists]
- **Fix**: [How to address]
- **Testing**: [How to verify fix works]
- **Prevention**: [How to prevent in future]
- **Related**: [CVEs or security guidelines]

---

## Performance Learnings

### Finding: [Performance Characteristic]
- **Metric**: [What was measured]
- **Baseline**: [Before optimization]
- **Improvement**: [After optimization or target]
- **Cost of Improvement**: [Effort/complexity tradeoff]
- **Scaling Behavior**: [How it scales]
- **Recommendation**: [When to apply]

---

## Operational Learnings

### Learning: [Operational Understanding]
- **What**: [What you learned]
- **Impact**: [Why it matters for ops]
- **Monitoring**: [How to track]
- **Alerting**: [What to alert on]
- **Runbook**: [Steps to handle]

---

## What Should Next Agent Know?

### Context for Future Work
- Important prerequisite: [What to know before]
- Common pattern: [Pattern they'll see]
- Gotcha to watch: [What to be careful about]
- Optimization opportunity: [Faster way to do X]

---

## Suggested .titan Updates

### Documentation Updates
- [ ] Add to \`.titan/docs/[section]/[filename].md\`
  - Topic: [What section]
  - Content: [Brief description]

### Registry Updates
- [ ] Update \`.titan/registry/[file].json\`
  - What changed: [Description]
  - Reason: [Why]

### Schema Updates
- [ ] Update \`.titan/schemas/[schema].json\`
  - What: [New field or change]
  - Why: [Reasoning]

### Blueprint References
- [ ] Update \`.titan/blueprints/README.md\`
  - Add reference to: [Which blueprint]
  - Reason: [Why relevant]

### Architecture Updates
- [ ] Create \`.titan/docs/architecture/[new-topic].md\`
  - Topic: [What to document]
  - Content: [New architecture pattern]

---

## Commits That Show This Learning

- Commit [hash]: [What this shows]
- Commit [hash]: [What this shows]

---

## Related Resources

### Blueprints Referenced
- \`.titan/blueprints/[number]-[NAME].md\`
- \`.titan/docs/system-blueprints/[section]/\`

### Existing Documentation
- \`.titan/docs/[section]/[file].md\`

### Code Examples
- File: [path:line]
- File: [path:line]

---

## Questions for Review

- [Question 1 for reviewer]
- [Question 2 for reviewer]
- [Clarification needed on]

---

**To Update .titan With This Knowledge**:
1. Review this document in your final pass
2. Choose which knowledge to add to .titan
3. Add to appropriate .titan files
4. Commit with: \`knowledge: Capture learnings from Issue #${issue}\`

See: \`.titan/workflows/AGENT_WORKFLOW.md\` → Section 8: Update .titan
`;

  fs.writeFileSync(
    path.join(workspaceDir, 'knowledge.md'),
    content
  );

  console.log(`✅ Created: knowledge.md`);
}

// Create pass 1 template
function createPass1Template() {
  const content = `# Pass 1: [Goal]

**Issue**: #${issue}
**Agent**: ${agentType}
**Started**: ${new Date().toISOString()}

## Objectives
- [ ] Objective 1
- [ ] Objective 2
- [ ] Objective 3

## Work Completed

### What You Did
- Done: [What was accomplished]
- Done: [What was accomplished]
- Done: [What was accomplished]

### Code Changes
- File: [path] - [What changed]
- File: [path] - [What changed]

### Testing
- [x] Local testing passed
- [x] Manual verification done
- [ ] Unit tests added

## Findings

### Issues Discovered
- **Issue**: [Description] → Location
- **Issue**: [Description] → Location

### Improvements Identified
- **Improvement**: [What could be better]
- **Improvement**: [What could be better]

## Learning Points
- Discovered: [New understanding]
- Pattern: [New pattern found]
- Best practice: [What works well]

## Next Pass
Pass 2 will focus on: [What comes next]

## Commits
- \`[hash]\` - [Commit message]

**Completed**: [Date]
`;

  fs.writeFileSync(
    path.join(workspaceDir, 'progress', 'pass-1-template.md'),
    content
  );

  console.log(`✅ Created: progress/pass-1-template.md`);
}

// Create .gitignore for workspace
function createGitignore() {
  const content = `.agent-workspace
`;

  fs.writeFileSync(
    path.join(workspaceDir, '.gitignore'),
    content
  );

  console.log(`✅ Created: .gitignore`);
}

// Create agent routing guide
function createAgentRoutingGuide() {
  if (!fs.existsSync(path.join(workspaceDir, 'AGENT-ROUTING.md'))) {
    const content = `# Agent Routing Guide

**Use this guide to understand which agent type should handle which issues.**

## By Agent Type

### Code Agent
**Archetype**: Developer
**Specialization**: Implementation, bugfixes, refactoring

**Should handle**:
- ✅ Bug fixes
- ✅ Feature implementation
- ✅ Code refactoring
- ✅ Performance optimization
- ✅ Test improvements
- ✅ Build/deployment code

**Should NOT handle**:
- ❌ Research/analysis
- ❌ Planning/architecture
- ❌ Monitoring setup
- ❌ Requirements gathering

**Typical Passes**: 4-5 (Investigation → Fix → Hardening → Tests → Documentation)

---

### Research Agent
**Archetype**: Analyst
**Specialization**: Investigation, analysis, discovery

**Should handle**:
- ✅ Security audits
- ✅ Code quality assessments
- ✅ Dependency analysis
- ✅ Performance profiling
- ✅ Architecture review
- ✅ Feasibility studies

**Should NOT handle**:
- ❌ Implementation
- ❌ Operational tasks
- ❌ Routine maintenance
- ❌ Production deployments

**Typical Passes**: 4-6 (Investigation → Analysis → Recommendations → Documentation)

---

### Planning Agent
**Archetype**: Architect
**Specialization**: Design, planning, coordination

**Should handle**:
- ✅ Architecture design
- ✅ System planning
- ✅ Component design
- ✅ Workflow definition
- ✅ Interface design
- ✅ Implementation planning

**Should NOT handle**:
- ❌ Direct implementation
- ❌ Debugging
- ❌ Operational tasks
- ❌ Research/investigation

**Typical Passes**: 4 (Scope → Design → Planning → Documentation)

---

### Execution Agent
**Archetype**: DevOps/SRE
**Specialization**: Setup, deployment, operations

**Should handle**:
- ✅ Infrastructure setup
- ✅ Deployments
- ✅ Configuration management
- ✅ Migration execution
- ✅ Data operations
- ✅ Automation setup

**Should NOT handle**:
- ❌ Architecture design
- ❌ Code implementation
- ❌ Planning
- ❌ Research/analysis

**Typical Passes**: 4-5 (Setup → Execution → Verification → Hardening → Documentation)

---

### Monitoring Agent
**Archetype**: Observer
**Specialization**: Observability, health, alerting

**Should handle**:
- ✅ Monitoring setup
- ✅ Alerting configuration
- ✅ Health checks
- ✅ Logging infrastructure
- ✅ Metrics collection
- ✅ Dashboard creation

**Should NOT handle**:
- ❌ Code implementation
- ❌ Architecture design
- ❌ Planning
- ❌ Direct operations

**Typical Passes**: 4 (Instrumentation → Alerts → Analysis → Documentation)

---

## By Issue Category

### Bug Fix
**Primary**: Code Agent
**Secondary**: Research Agent (if root cause unclear)
**Passes**: 4

### Feature Implementation
**Primary**: Code Agent
**Supporting**: Planning Agent (for design), Research Agent (for feasibility)
**Passes**: 5-6

### Security Audit
**Primary**: Research Agent
**Supporting**: Code Agent (for fixes)
**Passes**: 5

### Architecture/Design
**Primary**: Planning Agent
**Supporting**: Research Agent (for validation)
**Passes**: 4

### Deployment/Setup
**Primary**: Execution Agent
**Supporting**: Planning Agent (for design)
**Passes**: 4-5

### Performance Optimization
**Primary**: Code Agent
**Supporting**: Research Agent (for profiling), Monitoring Agent (for validation)
**Passes**: 5

### Infrastructure/DevOps
**Primary**: Execution Agent
**Supporting**: Monitoring Agent (for observability)
**Passes**: 4

### Documentation
**Primary**: Research Agent
**Supporting**: Any agent who worked on related feature
**Passes**: 3

### Testing/Quality
**Primary**: Code Agent
**Supporting**: Monitoring Agent (for metrics)
**Passes**: 4

---

## Multi-Agent Issues

Some complex issues benefit from multiple agents working in sequence:

### Pattern 1: Research → Implementation → Monitoring
1. **Research Agent**: Analyze problem, create recommendations
2. **Code Agent**: Implement solution
3. **Monitoring Agent**: Setup observability

### Pattern 2: Planning → Implementation → Testing
1. **Planning Agent**: Design solution
2. **Code Agent**: Implement
3. **Code Agent**: Add tests and documentation

### Pattern 3: Analysis → Design → Execution
1. **Research Agent**: Current state analysis
2. **Planning Agent**: Design improvements
3. **Execution Agent**: Deploy changes

---

## Routing Decision Tree

\`\`\`
Is it a problem to solve?
├─ Security issue?
│  └─ Research Agent (audit/analysis)
├─ Design/Architecture?
│  └─ Planning Agent (design/architecture)
├─ Code/Implementation?
│  ├─ Bug fix? → Code Agent
│  ├─ Feature? → Code Agent (+ Planning if complex)
│  └─ Refactor? → Code Agent
├─ Setup/Deployment?
│  └─ Execution Agent (+ Planning if complex design)
├─ Monitoring/Observability?
│  └─ Monitoring Agent
└─ Research/Investigation?
   └─ Research Agent

Is it complex/uncertain?
├─ Multiple skills needed?
│  └─ Route to primary agent, mark for collaboration
└─ Straightforward?
   └─ Route to specialist agent
\`\`\`

---

**Reference**: \`.titan/docs/agents/AGENT_DEVELOPMENT.md\` for agent archetype details
`;

    fs.writeFileSync(
      path.join(workspaceDir, 'AGENT-ROUTING.md'),
      content
    );

    console.log(`✅ Created: AGENT-ROUTING.md`);
  }
}

// Main execution
try {
  console.log(`\n🚀 Setting up agent workspace for Issue #${issue}\n`);

  createDirectories();
  createIssueMetadata();
  createWorkspaceReadme();
  createPassPlan();
  createFindingsTemplate();
  createKnowledgeTemplate();
  createPass1Template();
  createGitignore();
  createAgentRoutingGuide();

  console.log(`\n✅ Workspace setup complete!\n`);
  console.log(`📁 Location: ${workspaceDir}/`);
  console.log(`📖 Start with: ${workspaceDir}/WORKSPACE-README.md\n`);
  console.log(`Next steps:\n`);
  console.log(`1. Create your branch:`);
  console.log(`   git checkout -b agents/${agentType}/${issue}-[descriptor] origin/integration`);
  console.log(`   git push -u origin agents/${agentType}/${issue}-[descriptor]\n`);
  console.log(`2. Edit your pass plan:`);
  console.log(`   nano ${workspaceDir}/pass-plan.md\n`);
  console.log(`3. Execute passes 1-N with documentation\n`);
  console.log(`4. Update .titan in final passes\n`);
  console.log(`5. Create PR to integration\n`);

} catch (error) {
  console.error(`❌ Error: ${error.message}`);
  process.exit(1);
}
