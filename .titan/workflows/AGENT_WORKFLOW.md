# Agent Workflow: Issue → Branch → Multi-Pass Execution → Knowledge Capture

**Version**: 1.0  
**Status**: Mandatory for all agent work  
**Scope**: Complete lifecycle for any agent assigned to a GitHub issue

---

## 🚀 Quick Start: Agent Checklist

When you receive an issue assignment, follow this workflow:

```
1. ✅ Read issue → Identify agent type and requirements
2. ✅ Create branch → agents/[type]/[issue#]-[name]
3. ✅ Setup workspace → Run setup script
4. ✅ Plan passes → Define 3-8 execution passes
5. ✅ Execute passes → Document progress each pass
6. ✅ Track findings → Log issues and improvements
7. ✅ Final pass → Update .titan with knowledge
8. ✅ Create PR → Target integration branch
```

---

## 1️⃣ Receive Assignment

### What You'll See

GitHub issue with:
- **Assigned to**: Your agent type
- **Labels**: issue-type tags (bug, feature, refactor, audit, etc.)
- **Description**: What needs to be done
- **Acceptance Criteria**: How to know it's done

### What You Need to Do

- [ ] Read issue completely
- [ ] Understand acceptance criteria
- [ ] Identify which agent archetype this is (Code, Research, Planning, Execution, Monitoring)
- [ ] Note any dependencies or blockers
- [ ] Post comment: "Agent acknowledged - starting [pass plan]"

---

## 2️⃣ Create Branch

### Branch Naming Convention

```
agents/[agent-type]/[issue-number]-[descriptor]
```

**Examples**:
```
agents/code/245-fix-auth-middleware
agents/research/246-audit-security-gaps
agents/planning/247-design-task-graph-engine
agents/execution/248-deploy-production-release
agents/monitoring/249-setup-prometheus-dashboards
```

### Create Branch

```bash
# Fetch latest integration
git fetch origin integration

# Create agent branch FROM integration
git checkout -b agents/[type]/[issue#]-[name] origin/integration

# Push to establish remote branch
git push -u origin agents/[type]/[issue#]-[name]

# Verify
git branch -vv
```

**Important**: Always branch from `integration`, never from `main`.

---

## 3️⃣ Setup Workspace

### Run Setup Script

This script will:
- Create `.agent-workspace/` with working files
- Initialize progress tracker
- Create pass execution template
- Link to issue documentation
- Setup .titan references

```bash
npm run titan:agent:setup -- --agent-type [type] --issue [number]
```

**Output Structure**:
```
.agent-workspace/
├── issue-[number].json        # Issue metadata
├── pass-plan.md               # Your 3-8 pass strategy
├── progress/
│   ├── pass-1.md
│   ├── pass-2.md
│   └── ...
├── findings.md                # Issues/bugs/improvements found
├── knowledge.md               # Learning to add to .titan
├── .gitignore                 # Workspace cleanup on finish
└── WORKSPACE-README.md        # This workspace guide
```

### What Workspace Setup Does

1. **Analyzes issue** - Reads GitHub issue details
2. **Loads agent profile** - Gets your agent archetype defaults
3. **Creates templates** - Pass tracking and progress docs
4. **Links resources** - Points to relevant .titan docs
5. **Initializes checklist** - Pre-populated with common tasks

---

## 4️⃣ Plan Your Passes

### Multi-Pass Strategy (3-8 passes)

Divide work into logical passes, each producing measurable output.

### Example: Bug Fix (4 passes)

```markdown
# Pass Plan for Issue #245 (Auth Middleware Bug)

## Pass 1: Investigation & Root Cause
- Read auth middleware code
- Trace execution flow
- Identify bug location
- Write test case that fails
- Document root cause
- **Output**: Root cause analysis + failing test

## Pass 2: Fix Implementation
- Implement fix
- Test locally
- Verify no regressions
- Add inline comments
- **Output**: Working fix + passing tests

## Pass 3: Enhancement & Hardening
- Add defensive checks
- Improve error handling
- Add logging for debugging
- Update related code
- **Output**: Hardened implementation + logs

## Pass 4: Documentation & Integration
- Update README if applicable
- Add code comments
- Update .titan knowledge base
- Create PR summary
- **Output**: Complete documentation + PR ready
```

### Example: Feature Implementation (6 passes)

```markdown
# Pass Plan for Issue #246 (Task Graph Engine)

## Pass 1: Research & Design
- Study existing workflow patterns
- Review blueprints in .titan/blueprints/
- Design data model
- Create state machine diagram
- **Output**: Design document + diagrams

## Pass 2: Core Data Model
- Implement TaskGraph class
- Implement State class
- Implement Transition class
- Add basic serialization
- **Output**: Data model + unit tests

## Pass 3: State Machine Logic
- Implement state transitions
- Add guard conditions
- Add rollback support
- Test transition rules
- **Output**: State machine engine + tests

## Pass 4: Task Execution
- Implement task executor
- Add error handling
- Add checkpoints
- Implement recovery
- **Output**: Executor + tests

## Pass 5: Observability & Testing
- Add logging at key points
- Add metrics collection
- Add trace hooks
- Integration tests
- **Output**: Fully observable system

## Pass 6: Documentation & Integration
- Write API documentation
- Update system diagrams
- Add to .titan knowledge base
- Create examples
- **Output**: Complete system documentation
```

### Pass Plan Template

Create `.agent-workspace/pass-plan.md`:

```markdown
# Pass Plan for Issue #[NUMBER]: [TITLE]

## Strategy
[Explain your approach: investigation → solution → hardening → documentation]

## Pass 1: [Goal]
- [ ] Task 1
- [ ] Task 2
- [ ] Task 3
**Output**: [What this pass produces]

## Pass 2: [Goal]
- [ ] Task 1
- [ ] Task 2
**Output**: [What this pass produces]

[Continue for each pass...]

## Success Criteria
- [ ] Passes 1-[N] complete
- [ ] All acceptance criteria met
- [ ] Tests passing
- [ ] Documentation updated
- [ ] .titan knowledge base updated (pass [N-1] or [N])
```

---

## 5️⃣ Execute Each Pass

### During Each Pass

1. **Start Pass**
   ```bash
   # Update progress tracker
   echo "## Pass [N]: [Goal]" >> .agent-workspace/progress/pass-[N].md
   echo "Started: $(date)" >> .agent-workspace/progress/pass-[N].md
   ```

2. **Do the Work**
   - Implement changes
   - Write tests
   - Document as you go
   - Track decisions

3. **Document Progress**
   ```markdown
   ## Pass 1: Investigation & Root Cause
   
   **Started**: 2026-07-31 14:00 UTC
   **Status**: In Progress
   
   ### Work Done
   - [x] Read auth middleware code
   - [x] Traced execution flow
   - [ ] Identified bug location
   - [ ] Write test case
   
   ### Findings
   - Bug is in line 245 of middleware.js
   - Caused by missing null check
   - Affects all POST requests to /api/auth
   
   ### Questions for Next Pass
   - Should we add more defensive checks?
   - Need to check for related bugs in other middleware?
   
   **Completed**: [time]
   ```

4. **Commit Work**
   ```bash
   git add .
   git commit -m "pass-[N]: [Goal]
   
   - What you did
   - Key changes
   - What's working
   - What's next
   
   Issue #[NUMBER]"
   
   git push origin agents/[type]/[issue#]-[name]
   ```

### Pass Checklist (Every Pass)

- [ ] Work is complete for pass goals
- [ ] Tests pass (if applicable)
- [ ] Code is committed
- [ ] Progress documented in `.agent-workspace/progress/pass-[N].md`
- [ ] Findings logged in `.agent-workspace/findings.md`
- [ ] Next pass plan is clear

---

## 6️⃣ Track Findings

### Findings Document

Create `.agent-workspace/findings.md`:

```markdown
# Findings for Issue #[NUMBER]

## Bugs Found (Not in Original Issue)
- **Bug 1**: [Description] → File: [path:line]
- **Bug 2**: [Description] → File: [path:line]
- **Duplicate**: [Similar to issue #XXX]

## Code Smells
- **Smell 1**: [Description] → Location
- **Improvement**: [How to fix it]

## Performance Issues
- **Issue 1**: [What's slow] → Why
- **Impact**: [What this affects]

## Security Concerns
- **Concern 1**: [What's at risk]
- **Severity**: [Low/Medium/High]
- **Fix**: [How to address]

## Design Improvements
- **Idea 1**: [Better way to do this]
- **Reasoning**: [Why it's better]
- **Effort**: [Estimated work]

## Missing Documentation
- [Component] doesn't have docs
- [API] lacks examples
- [Pattern] should be added to .titan/docs/

## Learning Points
- Discovered: [How X works]
- Useful pattern: [For future use]
- Best practice: [Recommended approach]
```

### Logging Findings

As you discover issues, bugs, improvements:
```bash
# Update findings
echo "- **Bug**: [description] → location" >> .agent-workspace/findings.md

# Update knowledge
echo "- **Pattern**: [new understanding]" >> .agent-workspace/knowledge.md
```

---

## 7️⃣ Final Pass: Knowledge Capture

### ⚠️ CRITICAL: Final Pass Requirements

**On your LAST and SECOND-TO-LAST pass**, you MUST:

1. **Review All Findings** (Pass N-1 or earlier)
   - Extract key learnings
   - Identify patterns discovered
   - Compile security insights
   - Capture performance findings

2. **Update .titan Knowledge Base** (Pass N)
   - Add to relevant documentation
   - Update schema if applicable
   - Add to architecture decisions
   - Update agent prompts if needed

3. **Document What You Learned**
   - Write `.agent-workspace/knowledge.md`
   - Include patterns, best practices, gotchas
   - Cross-reference blueprints
   - Suggest future improvements

### Knowledge Capture Template

`.agent-workspace/knowledge.md`:

```markdown
# Knowledge Captured from Issue #[NUMBER]

## New Patterns Discovered
### Pattern: [Name]
- **What**: [Description]
- **Where Used**: [Files/systems]
- **Why It Works**: [Explanation]
- **When to Use**: [Conditions]
- **Example**: [Code or illustration]

## Best Practices Found
- **Practice**: [Recommendation]
- **Reasoning**: [Why]
- **Example**: [How to do it]

## Gotchas & Edge Cases
- **Gotcha**: [Common mistake]
- **Symptom**: [What goes wrong]
- **Fix**: [How to prevent]

## Architecture Insights
- **Insight**: [Understanding gained]
- **Impact**: [What this affects]
- **Recommendation**: [Future action]

## Security Findings
- **Issue**: [Vulnerability or risk]
- **Severity**: [Low/Medium/High]
- **Fix**: [How to address]
- **Testing**: [How to verify]

## Performance Learnings
- **Finding**: [Performance characteristic]
- **Measurement**: [Data/metrics]
- **Implication**: [What this means]
- **Optimization**: [If applicable]

## Suggested .titan Updates
1. Add to `.titan/docs/[section]/` → New doc about [topic]
2. Update `.titan/config/` → Schema change for [reason]
3. Add to `.titan/schemas/` → New validation for [what]
4. Update `.titan/blueprints/` → Reference [pattern]
5. Enhance `.titan/registry/` → Track [new capability]

## What to Add to Next Agent's Prompts
- Context: [What they should know]
- Warning: [What to watch out for]
- Optimization: [Faster way to do this]

## Related Issues & PRs
- Relates to: [Issue #XXX]
- Similar: [PR #YYY]
- Blocks: [Issue #ZZZ]
```

---

## 8️⃣ Update .titan (Second-to-Last Pass Minimum)

### MANDATORY: .titan Updates

By your second-to-last pass, you MUST update .titan with knowledge.

### Update Locations

**1. If You Found a New Pattern**
```bash
# Add to architectural patterns
echo "### [Pattern Name]
Description of pattern discovered.
Found in: Issue #[NUMBER]
Example: [file:line]" >> .titan/docs/architecture/PATTERNS.md
```

**2. If You Discovered Best Practices**
```bash
# Add to agent learning guide
echo "## [Topic]: [Best Practice]
- What: [Description]
- Why: [Reasoning]
- Example: See Issue #[NUMBER]" >> .titan/docs/agents/BEST_PRACTICES.md
```

**3. If You Found Security Issues**
```bash
# Add to security checklist
echo "- [ ] Check for [vulnerability type] - Found in Issue #[NUMBER]
  - Symptom: [How to detect]
  - Fix: [How to prevent]" >> .titan/docs/security/SECURITY_CHECKLIST.md
```

**4. If You Enhanced a Component**
```bash
# Update registry
echo '"[component]": {
  "version": "2.0",
  "updated_by": "Issue #[NUMBER]",
  "enhancements": ["What changed"]
}' >> .titan/registry/components.json
```

**5. If You Created New Documentation**
```bash
# Create new doc file
cat > .titan/docs/[section]/[NEW_TOPIC].md << 'EOF'
# [New Topic]

Based on Issue #[NUMBER]

[Your documentation]
EOF
```

**6. If You Discovered a Gotcha**
```bash
# Add to troubleshooting
echo "### [Issue]: [Symptom]
- **Cause**: [Root cause from Issue #[NUMBER]]
- **Fix**: [Solution]
- **Prevention**: [How to avoid]" >> .titan/docs/TROUBLESHOOTING.md
```

### Commit .titan Updates

```bash
# Commit .titan updates
git add .titan/

git commit -m "knowledge: Capture learnings from Issue #[NUMBER]

From issue #[NUMBER] - [issue title]

Knowledge captured:
- Added pattern: [pattern name]
- Added best practice: [practice]
- Fixed documentation: [what]
- Enhanced registry: [what]

Files updated:
- .titan/docs/[section]/
- .titan/registry/

See .agent-workspace/knowledge.md for details"

git push origin agents/[type]/[issue#]-[name]
```

---

## 9️⃣ Create Pull Request

### Before PR: Final Checklist

- [ ] All passes complete
- [ ] Tests passing (npm run test or equivalent)
- [ ] Code reviewed (read it yourself)
- [ ] Documentation complete
- [ ] .titan knowledge base updated
- [ ] No breaking changes (or documented)
- [ ] Commits are clean and documented
- [ ] Branch is up to date with integration

### PR Template

```markdown
# PR: [Issue Title]

**Issue**: Fixes #[NUMBER]  
**Agent**: [Your agent type]  
**Branch**: agents/[type]/[issue#]-[name]

## Summary
[What this PR does - one paragraph]

## Passes Completed
- [x] Pass 1: [Goal] - Commit [hash]
- [x] Pass 2: [Goal] - Commit [hash]
- [x] Pass 3: [Goal] - Commit [hash]

## Acceptance Criteria
- [x] Criterion 1
- [x] Criterion 2
- [x] Criterion 3

## Testing
- [x] Unit tests passing
- [x] Integration tests passing
- [x] Manual testing done
- [x] No regressions found

## Knowledge Captured
- **Patterns**: [List new patterns]
- **Best Practices**: [List practices]
- **Gotchas**: [List edge cases found]
- **.titan Updates**: [What was added/updated]

See `.agent-workspace/knowledge.md` for detailed learnings.

## Issues Found During Work
[List any bugs/improvements found - create separate issues for them]

## Notes
[Any additional context]

---
_Generated by Agent: [Agent Type] | Issue #[NUMBER]_
```

### Push and Create PR

```bash
# Ensure everything is pushed
git push origin agents/[type]/[issue#]-[name]

# Create PR to integration (NOT main)
# Go to GitHub and create PR
# Base: integration
# Compare: agents/[type]/[issue#]-[name]
# Fill in template above
```

---

## 🔄 Complete Workflow Diagram

```
GitHub Issue #123
    ↓
    ├─ Assign to Agent Type (Code, Research, etc.)
    ├─ Add labels (bug, feature, refactor, etc.)
    └─ Include acceptance criteria
    ↓
Agent Receives Assignment
    ↓
    ├─ Read issue (acceptance criteria)
    ├─ Post: "Acknowledged - starting work"
    └─ Identify dependencies
    ↓
Create Branch: agents/[type]/[issue#]-[name]
    ↓
Run Setup Script
    ├─ Create .agent-workspace/
    ├─ Create pass plan
    ├─ Link resources
    └─ Initialize templates
    ↓
Plan Passes (3-8 execution phases)
    ├─ Pass 1: [Goal]
    ├─ Pass 2: [Goal]
    └─ ...
    ↓
Execute Pass 1
    ├─ Do work
    ├─ Commit with message
    ├─ Document progress
    └─ Log findings
    ↓
Execute Pass 2
    ├─ Review Pass 1 output
    ├─ Do work
    ├─ Commit
    └─ Document
    ↓
[Continue for each pass]
    ↓
Pass N-1: Findings & Learning Review ⚠️ CRITICAL
    ├─ Extract all findings
    ├─ Identify patterns
    ├─ Compile learnings
    └─ Prepare knowledge.md
    ↓
Pass N: Final - Update .titan ⚠️ CRITICAL
    ├─ Review knowledge.md
    ├─ Update .titan documentation
    ├─ Update .titan registry
    ├─ Update .titan schemas
    ├─ Commit .titan updates
    └─ Push branch
    ↓
Create Pull Request
    ├─ Target: integration (not main)
    ├─ Fill PR template
    ├─ Reference .agent-workspace/ docs
    └─ List issues found
    ↓
Reviewer Approval
    ├─ Code review
    ├─ Check .titan updates
    └─ Merge to integration
    ↓
Cleanup
    ├─ Delete .agent-workspace/
    ├─ Delete branch (via GitHub)
    └─ Close issue
```

---

## 📋 Agent Workflow Checklist

### For Every Issue:
- [ ] Issue assigned to agent type
- [ ] Issue has acceptance criteria
- [ ] Agent creates agents/[type]/[issue#]-[name] branch
- [ ] Agent runs workspace setup
- [ ] Agent creates pass plan (3-8 passes)
- [ ] Agent executes each pass with documentation
- [ ] Agent logs findings and learnings
- [ ] **Agent updates .titan by pass N-1 or N** ⚠️
- [ ] Agent creates PR to integration
- [ ] Agent provides knowledge summary

### For Final Pass (Pass N):
- [ ] All prior passes complete
- [ ] Acceptance criteria met
- [ ] Tests passing
- [ ] .titan documentation updated
- [ ] .titan registry updated (if applicable)
- [ ] New knowledge documented
- [ ] Gotchas and edge cases recorded
- [ ] Suggestions for future work noted
- [ ] PR ready for review
- [ ] Commit message references .agent-workspace/ knowledge

---

## 🚨 Critical Points

1. **MANDATORY: .titan Updates**
   - Must happen by Pass N-1 at the latest
   - On Pass N for final pass
   - Not optional, not "if you have time"

2. **MANDATORY: Knowledge Capture**
   - Every agent must document what they learned
   - Include patterns, best practices, gotchas
   - Add suggestions for next agents
   - Update agent prompts if needed

3. **MANDATORY: Multi-Pass Execution**
   - Minimum 3 passes, maximum 8
   - Each pass has clear goal and output
   - Progress documented each pass
   - Not a single commit, but structured phases

4. **MANDATORY: Branch Naming**
   - Always `agents/[type]/[issue#]-[name]`
   - Never `agents/work` or generic names
   - Enables tracking of agent work

5. **MANDATORY: Issue Assignment**
   - Issue must specify which agent type
   - Not "someone should do this"
   - Clear acceptance criteria
   - Labels indicating issue category

---

## 📚 Related Documentation

- `.titan/docs/agents/AGENT_DEVELOPMENT.md` - Agent archetype details
- `.titan/docs/AGENT_OS.md` - Agent OS architecture
- `.titan/WORKFLOW.md` - Branch strategy (feature → integration → main)
- `.titan/docs/protocols/AGENT_COMMUNICATION.md` - Inter-agent communication
- `.titan/blueprints/` - System architecture patterns to reference

---

**Status**: ✅ Mandatory workflow for all agent work  
**Last Updated**: July 31, 2026  
**Owner**: System Architecture
