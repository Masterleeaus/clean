# 🚀 Agent Quickstart Guide

**Welcome to your agent workspace!** Get up and running in 5 minutes.

---

## ⚡ 5-Minute Setup

### Step 1: Read Your Manifest (2 min)
1. Navigate to `.titan/agent-manifests/`
2. Open your agent manifest (e.g., `workcore-agent-manifest.md`)
3. Skim the key sections: Domain, Tasks, Rules

### Step 2: Read Agent Instructions (1 min)
- `docs/START_HERE/AGENT_INSTRUCTIONS.md` - Critical rules
- Focus on: Multi-tenancy, escalation, when to ask Claude

### Step 3: Accept Your First Task (2 min)
1. Check your task-queue/pending/ directory
2. Read the first task JSON
3. Move it to active/ when you start
4. Complete and move to completed/

---

## 📋 Your Workspace Structure

```
Your Agent Directory (Agent-XX/)
├── README.md                    ← You're here now
├── task-queue/
│   ├── pending/                 ← Incoming tasks
│   ├── active/                  ← Tasks in progress
│   └── completed/               ← Finished tasks
├── inbox/                       ← Messages & updates
├── workspace/                   ← Your working files
└── sessions/                    ← Track your sessions
```

---

## 🎯 Before You Start: Critical Rules

### Multi-Tenancy (MOST IMPORTANT)
- ✅ **ALWAYS** check company_id on tasks
- ✅ **NEVER** mix customer data across company_ids
- ✅ **ALWAYS** scope queries to company_id
- ❌ **NEVER** assume default access

### Escalation Triggers
- 🔴 Security/compliance issue → Escalate to Security Agent + Claude
- 🔴 Production incident → Escalate to DevOps + Coordination
- 🔴 Architecture decision → Escalate to Claude Architect
- 🔴 Customer complaint → Escalate to Coordination
- 🔴 Resource blocked → Contact blocking agent immediately

### When to Ask Claude
- Performance implications not clear
- Security trade-offs
- Architecture decisions
- Customer impact concerns
- Resource allocation conflicts

---

## 📝 Task Workflow

### Receiving a Task
```json
{
  "id": "task-uuid",
  "status": "pending",
  "priority": "high",
  "domain": "your-domain",
  "description": "What to do",
  "assigned_at": "2026-07-30T10:30:00Z",
  "deadline": "2026-07-31T17:00:00Z",
  "requester": "Agent-19 or Human",
  "context": {
    "company_id": "acme-corp",
    "related_tasks": ["..."],
    "dependencies": ["..."]
  }
}
```

### Your Process
1. **Read** - Understand the task completely
2. **Check Dependencies** - Any blockers?
3. **Verify Company ID** - Correct scope
4. **Plan** - How will you execute?
5. **Execute** - Do the work
6. **Test** - Verify it works
7. **Document** - Leave notes for others
8. **Complete** - Mark task done

### Completing a Task
```json
{
  "id": "task-uuid",
  "status": "completed",
  "completed_at": "2026-07-30T14:45:00Z",
  "result": "What you did",
  "quality_score": 4.8,
  "notes": "Any special notes",
  "next_steps": "What happens next"
}
```

---

## 🤝 Working with Other Agents

### If You Need Help
**Send a coordination request to Agent-19 (Coordination Agent)**
```
Subject: Support needed - [Domain]
Content: What you need, deadline, urgency
```

### If Another Agent Asks for Help
**Check their guild first - quick decision if same guild**  
**Otherwise - confirm with Coordination Agent**

### Handoff to Another Agent
1. Document your work thoroughly
2. Write clear handoff notes
3. Contact destination agent
4. Follow up to ensure receipt

---

## 📊 Your Performance Metrics

You're tracked on:
- **Task Completion Rate** (target: > 95%)
- **Quality Score** (target: > 4.5/5)
- **On-Time Delivery** (target: 100%)
- **Agent Satisfaction** (target: > 4.5/5)
- **Escalation Appropriateness** (target: > 98%)

**Note:** You'll see your metrics in `.titan/operator/metrics/` regularly updated.

---

## 📞 Getting Help

### Your Guild
- Same domain specialists
- Quick decisions on domain questions
- See your manifest for guild members

### Coordination Agent (Agent-19)
- Task routing help
- Multi-agent coordination
- Blocker resolution
- Escalations

### Claude Architect
- Architecture questions
- Design decisions
- Critical escalations
- System-wide guidance

### Slack/Communication
(If your workspace uses Slack)
- Your guild channel
- `#agent-coordination` channel
- Direct DMs for quick questions

---

## ✅ Day 1 Checklist

- [ ] Read your agent manifest
- [ ] Read AGENT_INSTRUCTIONS.md
- [ ] Understand multi-tenancy rules
- [ ] Know your escalation triggers
- [ ] Know how to contact Coordination Agent
- [ ] Accepted first task
- [ ] Started working!

---

## 🎓 Deeper Learning

### Next (Day 2)
- Read all agent manifests for context
- Study coordination protocols
- Review task examples
- Join guild channel

### Week 1
- Complete 5+ tasks
- Get comfortable with routing
- Build reputation
- Help other agents

### Week 2+
- Take on complex tasks
- Lead multi-agent coordination
- Contribute to guild
- Mentor new agents

---

## 🔗 Important Links

**Quick Navigation:**
- [All Agent Manifests](../agent-manifests/)
- [Agent Instructions](../../docs/START_HERE/AGENT_INSTRUCTIONS.md)
- [Available Actions](../../docs/START_HERE/AVAILABLE_ACTIONS.md)
- [Task Routing Engine](./task-routing-engine.md)
- [Operator Directory](../operator/)

---

## 💡 Pro Tips

1. **Start Small** - Take simple tasks first, build confidence
2. **Read Manifests** - Know what each agent does
3. **Ask Questions** - Better to escalate than mess up
4. **Document Work** - Clear notes help everyone
5. **Give Feedback** - Help improve the system
6. **Track Time** - Helps with metrics and scheduling
7. **Join Guild** - Build relationships with specialists

---

## 🚀 Ready?

Head to your task-queue/pending/ and grab your first task!

Questions? Contact Agent-19 (Coordination Agent) or your guild lead.

**Good luck! 🎯**

---

**Last Updated:** 2026-07-30  
**For questions:** See your agent manifest or contact Coordination Agent

