# 🚀 Titan System Entrance Points

Welcome, Agent! Choose your entrance below.

---

## 👋 Quick Entry

### For Claude (Architect Brain)
**→ [Read claude-start.md](./claude-start.md)**

Your role: System oversight, governance, recommendations

### For ChatGPT (Workforce Agents)
**→ [Read chatgpt-start.md](./chatgpt-start.md)**

Your role: Execute tasks, coordinate work, optimize systems

---

## 📋 What's Here

This `entrance/` directory is the gateway to Titan.

Each agent type gets:
- **Entry guide** - Quick onboarding (5 min)
- **Manifest** - Files to read (what's important for you)
- **Quick reference** - One-page cheat sheet
- **Specialization guide** - Role-specific instructions

---

## 🎯 Your First Steps

### Step 1: Read Your Entry Guide (5 minutes)
- Claude → [claude-start.md](./claude-start.md)
- ChatGPT → [chatgpt-start.md](./chatgpt-start.md)

### Step 2: Follow Your Manifest
Each entry guide points to your manifest.
- File list you should know
- Reading order (most important first)
- Quick access links

### Step 3: Pick Your Specialization
Available ChatGPT agents:
- 🔧 **Workcore Agent** - Business operations
- 🎨 **Platform Agent** - Core platform
- 📱 **PWA Agent** - Progressive web app
- 🐛 **Debugging Agent** - Bug fixes & diagnostics
- 🧪 **Testing Agent** - QA and testing
- 📖 **Documentation Agent** - Docs and guides
- 🔐 **Security Agent** - Security & compliance
- 🚀 **DevOps Agent** - Deployment & infrastructure
- 💡 **Architect Agent** - Design & refactoring
- 🎯 **Integration Agent** - Third-party integrations

### Step 4: Access Your Workspace
- Review your agent folder in `.titan/operator/Agent-XX/`
- Check task queue at `.titan/operator/task-queue/`
- Read broadcasts at `.titan/operator/broadcasts/`

---

## 📚 Documentation Map

```
.titan/
├── entrance/
│   ├── README.md (you are here)
│   ├── claude-start.md
│   ├── chatgpt-start.md
│   └── [specialization guides]
│
├── agent-manifests/
│   ├── claude-manifest.md
│   ├── workcore-agent-manifest.md
│   ├── platform-agent-manifest.md
│   ├── pwa-agent-manifest.md
│   ├── debugging-agent-manifest.md
│   └── [more...]
│
├── quickstart/
│   ├── claude-quickstart.md
│   ├── chatgpt-quickstart.md
│   └── [agent quickstarts]
│
├── README.md (overview)
├── MANDATE.md (core rules)
├── VISION.md (strategy)
├── ROADMAP.md (timeline)
│
├── architect/ (Claude's subsystem)
├── operator/ (ChatGPT's subsystem)
├── kernel/ (config)
├── runtime/ (execution)
├── capabilities/ (100+ actions)
└── [more subsystems...]
```

---

## ⚡ Quick Activation

### Claude
```
"Claude, access the Masterleeaus/clean repository 
and your Architect role in .titan/"

→ Claude reads: claude-start.md → claude-manifest.md
→ Claude monitors: .titan/architect/
```

### ChatGPT - Workcore Agent
```
"ChatGPT, access GitHub and your Workcore Agent role.
Repository: Masterleeaus/clean"

→ ChatGPT reads: chatgpt-start.md → workcore-agent-manifest.md
→ ChatGPT works: Tasks for WorkCore domain
```

### ChatGPT - PWA Agent
```
"ChatGPT, access GitHub and your PWA Agent role.
Repository: Masterleeaus/clean"

→ ChatGPT reads: chatgpt-start.md → pwa-agent-manifest.md
→ ChatGPT works: PWA-specific tasks
```

---

## 🎯 What Happens Next

### Claude
1. Reads architect instructions
2. Loads system knowledge
3. Begins continuous monitoring
4. Creates recommendations
5. Reports status

### ChatGPT
1. Reads agent instructions
2. Loads specialization knowledge
3. Checks task queue
4. Accepts assignments
5. Executes work

---

## 📞 Need Help?

### Claude Help
- Check [claude-start.md](./claude-start.md) section "When You're Stuck"
- Review [..MANDATE.md](../MANDATE.md) for rules
- Escalate in [../inbox/claude/pending/](../inbox/claude/pending/)

### ChatGPT Help
- Check [chatgpt-start.md](./chatgpt-start.md) section "Blocked?"
- Read agent escalation in manifests
- Report in [../operator/task-queue/](../operator/task-queue/)

---

## 🚀 Ready?

**Claude:** [→ Go to claude-start.md](./claude-start.md)

**ChatGPT:** [→ Go to chatgpt-start.md](./chatgpt-start.md)

---

*Titan Entrance*  
*Where agents begin their journey*
