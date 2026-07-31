# 🎯 Task Routing Engine

**Purpose:** Intelligently analyze and route tasks to the best available agent  
**Status:** Core implementation  
**Owner:** Coordination Agent (Agent-19)

---

## 🔄 Routing Algorithm

### Phase 1: Task Analysis
```
INPUT: Raw task
  ├─ Parse task description
  ├─ Extract domain keywords
  ├─ Identify task type
  ├─ Assess complexity
  ├─ Evaluate urgency
  └─ Extract requirements
RETURN: Analyzed task metadata
```

### Phase 2: Domain Classification
```
DOMAINS:
  • Workcore (business operations) → Agent-01
  • Platform (infrastructure) → Agent-02
  • Frontend/PWA (UI/UX) → Agent-03
  • APIs (REST/GraphQL) → Agent-04
  • Database (data/schemas) → Agent-05
  • Performance (optimization) → Agent-06
  • Security (compliance) → Agent-07
  • Testing (QA automation) → Agent-08 or Agent-09
  • Chatbot (AI runtime) → Agent-10
  • Workflows (wizards/flows) → Agent-11
  • Extensions (plugins) → Agent-12
  • Integration (third-party) → Agent-13
  • AI Router (model selection) → Agent-14
  • DevOps (CI/CD) → Agent-15
  • Configuration (settings) → Agent-16
  • Migration (DB changes) → Agent-17
  • Documentation (tech writing) → Agent-18
  • Architecture (design) → Agent-20
```

### Phase 3: Candidate Selection
```
FOR each domain-matched agent:
  ├─ Check availability
  ├─ Check current workload
  ├─ Check specialization match
  ├─ Review recent performance
  └─ Calculate suitability score
RETURN: List of candidates ranked by score
```

### Phase 4: Multi-Agent Detection
```
IF task involves multiple domains:
  ├─ Identify primary domain
  ├─ Identify secondary domains
  ├─ Assign primary agent
  ├─ Assign supporting agents
  ├─ Create coordination protocol
  └─ Setup handoff sequence
ELSE:
  └─ Single agent handles
```

### Phase 5: Escalation Check
```
IF urgency = CRITICAL:
  ├─ Escalate to Claude Architect
  ├─ Assign Coordination Agent
  └─ Add immediate priority flag
ELSIF complexity = VERY_HIGH:
  ├─ Escalate to Coordination Agent
  └─ May need architecture review
ELSE:
  └─ Proceed with normal routing
```

### Phase 6: Assignment & Dispatch
```
1. Create task ID (UUID)
2. Create task file in agent inbox
3. Update task queue state
4. Send assignment notification
5. Log routing decision
6. Start monitoring
```

---

## 📊 Scoring Algorithm

### Score Components (0-100)

**Skill Match (40 points max)**
- Perfect match: 40 points
- 75% match: 30 points
- 50% match: 20 points
- 25% match: 10 points
- No match: 0 points

**Availability (30 points max)**
- Fully available: 30 points
- 75% available: 22 points
- 50% available: 15 points
- 25% available: 8 points
- Unavailable: 0 points

**Recent Performance (20 points max)**
- Excellent (4.8+/5): 20 points
- Very Good (4.5+/5): 16 points
- Good (4.0+/5): 12 points
- Fair (3.5+/5): 8 points
- Poor (<3.5/5): 0 points

**Workload Balance (10 points max)**
- Light workload: 10 points
- Moderate: 7 points
- Heavy: 4 points
- Overloaded: 0 points

**Total Score = Skill + Availability + Performance + Workload**

---

## 🎯 Routing Decision Matrix

### Simple Tasks (Complexity = Low)
```
Single domain → Primary agent only
Urgency < High → Normal queue priority
Execution time < 4 hours → Direct assignment
No blockers → Immediate dispatch
```

### Medium Tasks (Complexity = Medium)
```
Single/dual domain → Primary + optional support
Urgency = Normal → Standard priority
Execution time 4-12 hours → Scheduled assignment
May have dependencies → Coordinate handoffs
```

### Complex Tasks (Complexity = High)
```
Multi-domain → Primary + 2+ support agents
Urgency = High → Priority queue
Execution time > 12 hours → Milestone tracking
Complex deps → Explicit coordination protocol
Requires architecture review → Escalate to Architect
```

### Critical Escalations (Urgency = Critical)
```
ANY complexity → Immediate escalation
→ Claude Architect notified
→ Coordination Agent assigned
→ Support agents on standby
→ Alternative plans prepared
→ Status updates every 30 min
```

---

## 📋 Task Type Examples

### Type: Bug Fix
```
Domain: Usually Workcore or specific component
Complexity: Low-Medium
Ideal Agents: Debugging (Agent-09), component owner
Process: Reproduce → Diagnose → Fix → Test → Deploy
```

### Type: Feature Implementation
```
Domain: Usually multi-domain
Complexity: Medium-High
Ideal Agents: Primary domain + Testing + DevOps
Process: Design → Implement → Test → Review → Deploy
```

### Type: Performance Optimization
```
Domain: Specific component + Performance
Complexity: Medium
Ideal Agents: Performance (Agent-06) + specialist
Process: Profile → Analyze → Optimize → Benchmark
```

### Type: Security Vulnerability
```
Domain: Security + component owner
Complexity: High
Ideal Agents: Security (Agent-07) + specialists
Process: CRITICAL escalation → Patch → Test → Deploy
```

### Type: Infrastructure Change
```
Domain: Platform/DevOps
Complexity: High
Ideal Agents: Platform (Agent-02) + DevOps (Agent-15)
Process: Plan → Test → Review → Deploy with rollback
```

---

## 🔗 Agent Selection Examples

### Example 1: "Fix customer name typo"
```
Analysis:
  ├─ Domain: Workcore
  ├─ Type: Bug fix
  ├─ Complexity: Low
  ├─ Urgency: Low
  └─ Skills: Data handling

Route to: Agent-01 (Workcore)
Reasoning: Business logic issue
Assignment: task-queue/pending/
Timeline: Immediate
```

### Example 2: "Add voice support to chatbot"
```
Analysis:
  ├─ Domain: AI Runtime + Integration
  ├─ Type: Feature implementation
  ├─ Complexity: High
  ├─ Urgency: Medium
  └─ Skills: AI, voice, integration

Primary: Agent-10 (Chatbot)
Secondary: 
  ├─ Agent-11 (Workflows)
  ├─ Agent-13 (Integration)
  └─ Agent-08 (Testing)

Coordination: Sequential with handoffs
Assignment: Multi-agent task file
Timeline: 2-3 days
```

### Example 3: "Security vulnerability in auth system"
```
Analysis:
  ├─ Domain: Security + Workcore
  ├─ Type: Critical fix
  ├─ Complexity: High
  ├─ Urgency: CRITICAL ⚠️
  └─ Skills: Security, auth, testing

Escalation: ⚠️ CRITICAL

Primary: Agent-07 (Security)
Support:
  ├─ Agent-01 (Workcore)
  ├─ Agent-08 (Testing)
  └─ Agent-15 (DevOps)

Notify: Claude Architect + Coordination Agent
Process: Emergency patch → Test → Deploy
Timeline: < 2 hours
```

---

## 📊 Routing Metrics

### Track & Report

**Routing Accuracy**
- First choice success rate (target: > 90%)
- Multi-agent coordination success (target: > 95%)
- Escalation appropriateness (target: > 98%)
- Agent satisfaction (target: > 4.5/5)

**Efficiency**
- Average routing time (target: < 5 min)
- Task waiting time (target: < 1 hour)
- Handoff success rate (target: 100%)
- Blocker resolution time (target: < 30 min)

**Workload Balance**
- Agent utilization (target: 70-80%)
- No agent overloaded
- Fair distribution
- Growth opportunities (stretch assignments: 20% of workload)

---

## 🚀 Implementation Steps

### Phase 1: Core Engine (Week 1)
- [ ] Build task analysis module
- [ ] Implement domain classifier
- [ ] Create scoring algorithm
- [ ] Setup basic routing

### Phase 2: Multi-Agent Coordination (Week 2)
- [ ] Implement multi-agent detection
- [ ] Create coordination protocols
- [ ] Setup handoff procedures
- [ ] Test multi-agent flows

### Phase 3: Escalation & Monitoring (Week 3)
- [ ] Implement critical escalations
- [ ] Setup monitoring & alerts
- [ ] Create metrics dashboard
- [ ] Add feedback loop

### Phase 4: Optimization (Week 4)
- [ ] Machine learning on routing decisions
- [ ] Performance optimization
- [ ] Fallback strategies
- [ ] Documentation & training

---

## 🔗 Integration Points

**Task Entry:**
- GitHub Actions workflows
- Direct agent submissions
- Human requests
- Claude Architect assignments

**Task Queue:**
- Agent task-queue/ directories
- Coordination queues
- Priority management
- Deadline tracking

**Agent Assignment:**
- Inbox notifications
- Session activations
- Metrics updates
- Performance tracking

**Monitoring:**
- Task progress tracking
- Blocker detection
- Performance metrics
- Escalation triggers

---

## ⚠️ Critical Rules

### Routing Quality
- ✅ Always match to best agent
- ✅ Consider agent workload
- ✅ Honor agent specialization
- ✅ Enable growth assignments
- ❌ Never overload an agent
- ❌ Never misroute critical work

### Escalation Protocol
- ✅ Critical security → Claude + Security Agent
- ✅ Production issue → All hands
- ✅ Architecture decision → Architect
- ✅ Unassignable → Escalate to humans
- ❌ Never ignore critical tasks
- ❌ Never skip escalation

### Multi-Agent Coordination
- ✅ Clear primary assignment
- ✅ Explicit coordination plan
- ✅ Documented handoffs
- ✅ Regular status checks
- ❌ Never duplicate work
- ❌ Never skip communication

---

## 📞 Getting Help

**Questions about routing?** Contact Agent-19 (Coordination Agent)  
**Architecture decisions?** Escalate to Claude Architect  
**Task reassignment?** Ask your guild lead first  

---

**Last Updated:** 2026-07-30  
**Next Review:** Weekly with Coordination Agent  
**Status:** 🟢 Operational

