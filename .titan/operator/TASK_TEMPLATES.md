# 📋 Task Templates

Standard formats for submitting and tracking tasks across the agent network.

---

## 📝 Task Submission Template

Use this when you submit a task for routing:

```json
{
  "id": "generated-uuid",
  "title": "Clear, concise title",
  "description": "Detailed description of what needs to be done",
  "domain": "workcore|platform|frontend|api|database|performance|security|testing|debugging|chatbot|workflows|extensions|integration|ai-router|devops|configuration|migration|documentation|coordination|architecture",
  "type": "bug-fix|feature|refactor|optimization|documentation|investigation|deployment|review",
  "complexity": "low|medium|high|critical",
  "priority": "low|normal|high|critical",
  "urgency": "low|medium|high|critical",
  "estimated_hours": 4,
  "deadline": "2026-08-01T17:00:00Z",
  "requester": {
    "type": "agent|human|system",
    "id": "Agent-XX or email or system name",
    "name": "Human readable name"
  },
  "context": {
    "company_id": "required-company-id",
    "related_tasks": ["task-id-1", "task-id-2"],
    "dependencies": ["Agent-XX", "Agent-YY"],
    "background": "Any relevant context",
    "acceptance_criteria": [
      "Criterion 1",
      "Criterion 2"
    ]
  },
  "suggested_agents": ["Agent-01", "Agent-02"],
  "requires_escalation": false,
  "tags": ["tag1", "tag2"]
}
```

---

## 🎯 Task Status Update Template

Update task progress regularly:

```json
{
  "task_id": "uuid",
  "status": "active|blocked|waiting|on-hold|completed|failed",
  "progress_percentage": 50,
  "last_update": "2026-07-30T15:30:00Z",
  "working_agent": "Agent-XX",
  "notes": "Current status and what's been done",
  "blockers": [
    {
      "blocker": "Waiting on Agent-05 for database schema",
      "blocker_agent": "Agent-05",
      "impact": "blocks implementation",
      "escalate_if_not_resolved_by": "2026-07-31T09:00:00Z"
    }
  ],
  "progress_notes": [
    "2026-07-30 10:00 - Started analysis",
    "2026-07-30 12:30 - Identified 3 requirements",
    "2026-07-30 15:30 - Currently implementing solution"
  ],
  "next_steps": "Next milestone or action item"
}
```

---

## ✅ Task Completion Template

When you finish a task:

```json
{
  "task_id": "uuid",
  "status": "completed",
  "completed_by": "Agent-XX",
  "completed_at": "2026-07-30T17:00:00Z",
  "time_spent_hours": 6.5,
  "result": {
    "summary": "What was accomplished",
    "deliverables": [
      "File created: src/components/X.jsx",
      "Tests added: tests/X.test.js",
      "Documentation: docs/X.md"
    ],
    "quality_metrics": {
      "test_coverage": 95,
      "code_review_score": 4.8,
      "performance_impact": "positive",
      "security_review": "passed"
    }
  },
  "quality_score": 4.8,
  "lessons_learned": [
    "What went well",
    "What could improve"
  ],
  "notes": "Any special notes for future reference",
  "handoff_notes": "If passing to another agent",
  "next_steps": "What comes after this task",
  "requester_feedback": ""
}
```

---

## 🤝 Multi-Agent Coordination Template

When coordinating with other agents:

```json
{
  "coordination_id": "generated-uuid",
  "task_id": "parent-task-id",
  "initiating_agent": "Agent-XX",
  "date": "2026-07-30T10:00:00Z",
  "coordination_type": "sequential|parallel|handoff|support|review",
  "participating_agents": [
    {
      "agent": "Agent-01",
      "role": "primary|secondary|support|reviewer",
      "responsibility": "What they're doing",
      "deadline": "2026-07-30T15:00:00Z",
      "dependencies_on": []
    },
    {
      "agent": "Agent-02",
      "role": "secondary",
      "responsibility": "What they're doing",
      "deadline": "2026-07-30T17:00:00Z",
      "dependencies_on": ["Agent-01"]
    }
  ],
  "sequence": [
    "Agent-01 completes initial implementation",
    "Agent-02 reviews and adds features",
    "Agent-03 performs testing",
    "Agent-15 handles deployment"
  ],
  "communication_plan": {
    "sync_meetings": "Daily standup at 10am",
    "async_updates": "End of day summary",
    "blocker_escalation": "Immediate when critical"
  },
  "handoff_protocol": {
    "from": "Agent-XX",
    "to": "Agent-YY",
    "format": "How work will be passed",
    "acceptance_criteria": "What needs to be verified"
  },
  "success_criteria": [
    "All agents report completion",
    "Quality scores > 4.5/5",
    "No critical blockers",
    "Timeline met"
  ]
}
```

---

## 🚨 Escalation Template

For critical issues that need Claude oversight:

```json
{
  "escalation_id": "generated-uuid",
  "escalating_agent": "Agent-XX",
  "escalation_level": "coordination|architecture|critical",
  "date": "2026-07-30T14:30:00Z",
  "task_id": "related-task-id",
  "issue": {
    "title": "Clear title of the issue",
    "description": "Detailed explanation",
    "severity": "low|medium|high|critical",
    "urgency": "low|medium|high|critical",
    "customer_impact": "Describe any impact",
    "business_impact": "Describe business implications"
  },
  "context": {
    "company_id": "acme-corp",
    "current_status": "What's happening now",
    "attempted_solutions": [
      "What we've tried so far"
    ],
    "why_escalation_needed": "Why agent can't handle alone"
  },
  "recommended_action": "What we think should happen",
  "timeline": "How urgent is this",
  "decision_needed_by": "2026-07-30T17:00:00Z",
  "relevant_agents": ["Agent-01", "Agent-07"],
  "required_expertise": "What type of help needed"
}
```

---

## 📊 Agent Handoff Template

When passing a task to another agent:

```json
{
  "handoff_id": "generated-uuid",
  "from_agent": "Agent-XX",
  "to_agent": "Agent-YY",
  "task_id": "uuid",
  "handoff_date": "2026-07-30T17:00:00Z",
  "status": "ready-for-handoff|in-progress|waiting-for-decision",
  "work_completed": {
    "summary": "What was accomplished",
    "deliverables": [
      "List of deliverables"
    ],
    "time_spent": "6.5 hours",
    "status": "75% complete"
  },
  "remaining_work": {
    "summary": "What's left to do",
    "estimated_hours": 2,
    "acceptance_criteria": "What defines done"
  },
  "context_transfer": {
    "background": "Relevant history",
    "decisions_made": "Why things were done this way",
    "key_files": ["path/to/file1", "path/to/file2"],
    "known_issues": "Any gotchas",
    "testing_notes": "How to verify work"
  },
  "prerequisites_for_next_phase": [
    "List what needs to be true before continuing"
  ],
  "quality_metrics": {
    "coverage": 85,
    "test_status": "passing",
    "code_review_score": 4.5
  },
  "next_steps": "Clear instructions for next agent",
  "questions_for_next_agent": [
    "Any uncertainties to clarify"
  ],
  "acceptance_checklist": [
    "Verify all deliverables present",
    "Run test suite",
    "Review code quality"
  ]
}
```

---

## 📈 Weekly Performance Report Template

Agents submit weekly:

```json
{
  "agent": "Agent-XX",
  "week_ending": "2026-07-31",
  "summary": {
    "tasks_completed": 8,
    "tasks_in_progress": 2,
    "average_quality_score": 4.6,
    "on_time_delivery": "100%"
  },
  "tasks": [
    {
      "id": "task-id",
      "title": "Task title",
      "status": "completed|in-progress",
      "quality_score": 4.8,
      "on_time": true,
      "notes": "Brief notes"
    }
  ],
  "metrics": {
    "average_hours_per_task": 5.2,
    "quality_score": 4.6,
    "on_time_delivery_rate": "100%",
    "agent_satisfaction": 4.7,
    "escalations_made": 2,
    "escalations_appropriate": "100%"
  },
  "workload": {
    "utilization_percentage": 75,
    "capacity": "Available for more work",
    "blockers": "None currently"
  },
  "learnings": [
    "Key insights from the week"
  ],
  "guild_contributions": [
    "Helped Agent-05 with database optimization"
  ],
  "goals_for_next_week": [
    "Specific goals"
  ]
}
```

---

## 🔗 Interaction Record Template

Document important interactions:

```json
{
  "interaction_id": "generated-uuid",
  "date": "2026-07-30T14:00:00Z",
  "participants": ["Agent-XX", "Agent-YY"],
  "interaction_type": "coordination|handoff|escalation|guild-meeting|code-review",
  "topic": "Brief topic",
  "summary": "What was discussed and decided",
  "decisions": [
    "Decision 1",
    "Decision 2"
  ],
  "action_items": [
    {
      "owner": "Agent-XX",
      "action": "What to do",
      "deadline": "2026-07-31",
      "priority": "high"
    }
  ],
  "follow_up_needed": true,
  "follow_up_date": "2026-08-01",
  "notes": "Additional notes"
}
```

---

## 💾 Where to Store Tasks

**Submit New Task:**
- → `.titan/operator/task-queue/pending/` as JSON file
- Name: `task-{uuid}.json`
- Notify Coordination Agent (Agent-19)

**Active Tasks:**
- → Agent's directory: `.titan/operator/Agent-XX/task-queue/active/`
- Move from pending/ when starting

**Completed Tasks:**
- → Agent's directory: `.titan/operator/Agent-XX/task-queue/completed/`
- Keep for metrics and history

**Status Updates:**
- → Same directory where task lives
- Update `.json` file regularly

---

## 🔍 Template Usage Examples

### Example 1: Simple Bug Fix
```json
{
  "title": "Fix customer name truncation",
  "domain": "workcore",
  "type": "bug-fix",
  "complexity": "low",
  "priority": "normal",
  "estimated_hours": 2,
  "deadline": "2026-07-31T17:00:00Z",
  "context": {
    "company_id": "acme-corp",
    "acceptance_criteria": [
      "Names > 50 chars display fully",
      "No database migration needed",
      "Tests pass"
    ]
  }
}
```

### Example 2: Multi-Agent Feature
```json
{
  "title": "Add OAuth authentication",
  "domain": "security",
  "type": "feature",
  "complexity": "high",
  "priority": "high",
  "estimated_hours": 24,
  "coordination_type": "sequential",
  "participating_agents": [
    {"agent": "Agent-07", "role": "primary"},
    {"agent": "Agent-04", "role": "secondary"},
    {"agent": "Agent-08", "role": "testing"},
    {"agent": "Agent-15", "role": "deployment"}
  ]
}
```

### Example 3: Escalation Example
```json
{
  "escalation_level": "critical",
  "issue": {
    "title": "Production database connection pool exhausted",
    "severity": "critical",
    "customer_impact": "All customers affected - service down"
  },
  "recommended_action": "Immediate database restart + connection limit increase",
  "timeline": "CRITICAL - NOW",
  "required_expertise": "Database emergency response"
}
```

---

## ✅ Template Checklist

Before submitting a task:
- [ ] All required fields filled
- [ ] company_id included (multi-tenancy check!)
- [ ] Acceptance criteria clear
- [ ] Estimated hours reasonable
- [ ] Deadline realistic
- [ ] Suggested agents identified
- [ ] Dependencies documented

---

**Last Updated:** 2026-07-30  
**Version:** 1.0  
**Maintained by:** Coordination Agent (Agent-19)

