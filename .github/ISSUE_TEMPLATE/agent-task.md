---
name: Agent Task
about: Create a task for an agent to complete using multi-pass execution workflow
title: "[Agent] Task Title"
labels: agent-task
---

<!-- 
INSTRUCTIONS FOR CREATING AGENT TASKS:

1. Use this template for any work that should be completed by an agent
2. Specify the agent type (code, research, planning, execution, monitoring)
3. Provide clear acceptance criteria
4. Agents will:
   - Create agents/[type]/[issue#]-[name] branch
   - Execute in 3-8 passes with documentation
   - Update .titan with learnings
   - Create PR to integration
   
See: .titan/workflows/AGENT_WORKFLOW.md for complete workflow
-->

## Agent Assignment

**Agent Type**: <!-- Choose one -->
- [ ] Code (Implementation, bugfixes, refactoring)
- [ ] Research (Analysis, audits, investigation)
- [ ] Planning (Architecture, design, planning)
- [ ] Execution (Setup, deployment, operations)
- [ ] Monitoring (Observability, alerting, health)

**Priority**: <!-- Choose one -->
- [ ] Critical (Blocking other work)
- [ ] High (Important, do soon)
- [ ] Medium (Important, can wait)
- [ ] Low (Nice to have)

---

## Task Description

<!-- 
Describe what needs to be done.
Be specific and clear about the problem or requirement.
Avoid vague language like "improve" or "fix" without context.
-->

[Detailed description of the task]

### Context
<!-- Why is this work needed? What does it enable? -->

[Context and reasoning]

---

## Acceptance Criteria

<!-- 
Define what "done" looks like.
These are the gates the agent must pass to consider the work complete.
Make them specific and measurable.
-->

- [ ] Criterion 1
- [ ] Criterion 2
- [ ] Criterion 3
- [ ] Documentation updated
- [ ] .titan knowledge base updated

---

## Expected Passes

<!-- 
Estimate how many execution passes this will take (3-8).
Suggest the breakdown if you have ideas.
Agent will refine this in their pass-plan.
-->

**Estimated Passes**: 3-8

**Suggested Breakdown** (optional):
1. Pass 1: [Goal]
2. Pass 2: [Goal]
3. Pass 3: [Goal]
...

---

## Dependencies

<!-- 
List any blockers, prerequisites, or related issues.
-->

- Depends on: [Issue #XXX]
- Related to: [Issue #YYY]
- Blocked by: [Issue #ZZZ]

---

## Resources

<!-- 
Point to relevant documentation or examples.
Links to .titan docs, blueprints, existing code, etc.
-->

- Reference: [Link to relevant doc]
- Example: [Link to similar implementation]
- Spec: [Link to specification]

---

## Notes

<!-- 
Any additional guidance for the agent.
Known pitfalls, optimization opportunities, or constraints.
-->

[Any additional notes]

---

## Workflow Reminder

**Agent**: When you receive this issue:

1. ✅ Read and understand acceptance criteria
2. ✅ Create branch: `agents/[type]/[issue#]-[name]`
3. ✅ Run setup: `npm run titan:agent:setup -- --agent-type [type] --issue [NUMBER]`
4. ✅ Create pass plan (3-8 passes)
5. ✅ Execute passes with documentation
6. ✅ **UPDATE .TITAN by pass N-1** ⚠️ CRITICAL
7. ✅ Create PR to `integration` branch (not main)
8. ✅ Document learnings in `.agent-workspace/knowledge.md`

See: `.titan/workflows/AGENT_WORKFLOW.md` for complete workflow guide.

---

**Created**: [Auto-filled by GitHub]
**Agent**: [To be assigned]
**Status**: Open
