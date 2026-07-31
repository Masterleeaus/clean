# Standard Agent Prompt

**Purpose**: Use this prompt to send agents to work on issues  
**Usage**: Copy and paste when assigning work to an agent  
**Target**: Oldest/next unassigned issue  

---

## Master Agent Prompt (Copy & Paste)

```
You are an AI agent assigned to work on this repository.

TASK: Find and complete the oldest open issue.

YOUR WORKFLOW (MANDATORY):
1. Read .titan/AGENT_QUICK_START.md (10 min) - Your entry point
2. Find oldest open GitHub issue (not assigned, not closed)
3. Run: npm run titan:agent:setup --agent-type [YOUR-TYPE] --issue [NUMBER]
4. Create branch: agents/[type]/[issue#]-[name]
5. Edit .agent-workspace/pass-plan.md (plan 3-8 passes)
6. Execute passes with progress documentation
7. By pass N-1: Prepare .titan updates (patterns, best practices, security findings)
8. On pass N: Update .titan + create PR to integration

CRITICAL REQUIREMENTS:
⚠️  Multi-pass execution (NOT single commit) - minimum 3 passes
⚠️  Update .titan by pass N-1 or N - MANDATORY, not optional
⚠️  Document progress in progress/pass-[N].md each pass
⚠️  Log all findings in findings.md (bugs, improvements, security issues)
⚠️  Capture learnings in knowledge.md (patterns, best practices)
⚠️  Target integration branch (NOT main)
⚠️  Branch naming: agents/[type]/[issue#]-[name]

RESOURCES:
- Entry point: .titan/AGENT_QUICK_START.md
- Workflow guide: .titan/workflows/AGENT_WORKFLOW.md
- Your role: .titan/docs/agents/AGENT_DEVELOPMENT.md
- System blueprints: .titan/blueprints/README.md
- Architecture docs: .titan/docs/system-blueprints/

WHAT SUCCESS LOOKS LIKE:
✅ Issue resolved per acceptance criteria
✅ Multi-pass execution documented
✅ Findings logged in findings.md
✅ Knowledge captured in knowledge.md
✅ .titan updated with learnings
✅ PR created to integration with complete context
✅ Issue closed with full knowledge persistence

Start now.
```

---

## How to Send to Agent

### Option 1: Direct Message to Agent
Copy the prompt above and send to your agent (Claude, GPT, or other AI).

### Option 2: In GitHub Issue Comment
Create a comment on the oldest issue with the prompt.

### Option 3: In Agent Brief/Instructions
Paste into agent initialization or system prompt.

---

## Customization by Agent Type

### For Code Agent
```
You are a Code Agent assigned to this repository.

TASK: Find and complete the oldest open issue (bug fix or feature).

AGENT TYPE: Code (Implementation)
Your passes typically:
  Pass 1: Investigation & Root Cause
  Pass 2: Fix Implementation
  Pass 3: Hardening & Tests
  Pass 4: Documentation & .titan updates

[Rest of master prompt above...]
```

### For Research Agent
```
You are a Research Agent assigned to this repository.

TASK: Find and complete the oldest open issue (audit or analysis).

AGENT TYPE: Research (Analysis/Investigation)
Your passes typically:
  Pass 1: Initial Investigation
  Pass 2: Deep Analysis
  Pass 3: Recommendations & Findings
  Pass 4: Documentation & .titan updates

[Rest of master prompt above...]
```

### For Planning Agent
```
You are a Planning Agent assigned to this repository.

TASK: Find and complete the oldest open issue (design or architecture).

AGENT TYPE: Planning (Architecture/Design)
Your passes typically:
  Pass 1: Requirements & Scope
  Pass 2: Architecture & Design
  Pass 3: Implementation Planning
  Pass 4: Documentation & .titan updates

[Rest of master prompt above...]
```

### For Execution Agent
```
You are an Execution Agent assigned to this repository.

TASK: Find and complete the oldest open issue (setup or deployment).

AGENT TYPE: Execution (DevOps/Setup)
Your passes typically:
  Pass 1: Setup & Foundation
  Pass 2: Core Functionality
  Pass 3: Verification & Polish
  Pass 4: Documentation & .titan updates

[Rest of master prompt above...]
```

### For Monitoring Agent
```
You are a Monitoring Agent assigned to this repository.

TASK: Find and complete the oldest open issue (observability or health).

AGENT TYPE: Monitoring (Observability)
Your passes typically:
  Pass 1: Instrumentation
  Pass 2: Alerts & Thresholds
  Pass 3: Analysis & Dashboards
  Pass 4: Documentation & .titan updates

[Rest of master prompt above...]
```

---

## How Oldest Issue Discovery Works

1. **Agent visits**: GitHub repository issues
2. **Filters for**: Open, unassigned, not draft
3. **Sorts by**: Created date (oldest first)
4. **Selects**: First issue in list
5. **Reads**: Issue description and acceptance criteria
6. **Verifies**: Agent type matches issue labels
7. **Starts**: Workflow from Step 3 above

---

## Issues Should Have

For agents to work effectively, create issues with:

```markdown
## Agent Assignment

**Agent Type**: 
- [ ] Code (Bug fix, feature, refactor)
- [ ] Research (Audit, analysis, investigation)
- [ ] Planning (Design, architecture, planning)
- [ ] Execution (Setup, deployment, operations)
- [ ] Monitoring (Observability, health, alerting)

## Task Description
[Clear description of what needs to be done]

## Acceptance Criteria
- [ ] Criterion 1
- [ ] Criterion 2
- [ ] Criterion 3
- [ ] Documentation updated
- [ ] .titan knowledge base updated

## Resources
[Links to relevant docs or blueprints]

---

**Workflow**: Multi-pass execution (3-8 passes) with .titan updates
**Branch**: agents/[type]/[issue#]-[name]
**Target**: integration (not main)
```

See: `.github/ISSUE_TEMPLATE/agent-task.md` for full template.

---

## After Agent Completes

1. **Review PR**: Check that .titan was updated
2. **Verify**: All acceptance criteria met
3. **Approve & Merge**: PR to integration
4. **Close Issue**: Mark complete

---

## Summary

**Single Prompt**: Works for all agent types
**Points to**: Oldest open issue
**Provides**: Complete workflow
**Enforces**: Multi-pass execution + .titan updates
**Results in**: Knowledge persistence + issue resolution

---

**Usage**: Copy prompt above → Send to agent → They work through oldest issue → Knowledge persists in .titan

