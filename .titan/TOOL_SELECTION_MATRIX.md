# Tool Selection Quick Reference Matrix

**Purpose**: Quick lookup for which ChatGPT plugin to use for your task  
**Usage**: When starting a pass, check this matrix to find the right tool  
**Detailed Guides**: See `.titan/tools/` directory for comprehensive guides

---

## By Task Type

| Task | Best Tool | When | How |
|------|-----------|------|-----|
| **Find code** | GitHub | Pass 1 Investigation | "Use GitHub to search for [function/pattern]" |
| **Understand code** | GitHub | Pass 1 | "Use GitHub to show me implementation of [feature]" |
| **Review for bugs** | CodeRabbit | Pass 2-3 | "Use CodeRabbit to review for bugs and security" |
| **Scan codebase quality** | CodeRabbit | Pass 3 Audit | "Use CodeRabbit to analyze codebase for issues" |
| **Build UI** | Build Web Apps | Pass 2 | "Use Build Web Apps to scaffold [component]" |
| **Prototype interface** | Build Web Apps | Pass 1-2 | "Use Build Web Apps to create wireframes" |
| **Create backend** | Build MCP Apps | Pass 2 | "Use Build MCP Apps to scaffold [service]" |
| **Design API** | Build MCP Apps | Pass 1-2 | "Use Build MCP Apps to design [endpoints]" |
| **Deploy service** | Manufact | Pass 2-3 | "Use Manufact to setup CI/CD" |
| **Publish prototype** | MiniUp | Pass 3 | "Use MiniUp to publish [app] as live URL" |
| **Research topic** | Tavily | Pass 1-2 | "Use Tavily to research [subject]" |
| **Find AI models** | Hugging Face | Pass 1-2 | "Use Hugging Face to find [task] models" |
| **Design feature** | Superpowers | Pass 1-2 | "Use Superpowers to design [feature]" |
| **Plan architecture** | Superpowers | Pass 1-2 | "Use Superpowers to plan [system]" |
| **Create diagram** | Goodnotes | Pass 2-3 | "Use Goodnotes to draw [diagram type]" |
| **Visualize findings** | Goodnotes | Pass 3-4 | "Use Goodnotes to visualize [results]" |
| **Write SOP** | Process Docs | Pass 4 | "Use Process Documentation AI to create SOP" |
| **Create checklist** | Process Docs | Pass 4 | "Use Process Documentation AI to create checklist" |

---

## By Agent Type

### Code Agent (Implementation Focus)

**Pass 1 - Investigation:**
- GitHub: Find where code is, understand flow
- Tavily (if research needed): Find external requirements

**Pass 2 - Implementation:**
- Build Web Apps (if UI): Scaffold components
- Build MCP Apps (if backend): Scaffold service
- Superpowers (if complex): Plan implementation

**Pass 3 - Hardening:**
- CodeRabbit: Review for bugs, security, quality
- Superpowers: Plan edge cases and testing

**Pass 4 - Documentation:**
- Process Documentation AI: Create deployment SOP
- MiniUp (if needed): Publish docs

---

### Research Agent (Analysis Focus)

**Pass 1 - Investigation:**
- GitHub: Scan codebase scope
- Tavily: Research external requirements

**Pass 2 - Deep Analysis:**
- CodeRabbit: Scan code quality
- Tavily: Deep research on findings
- Hugging Face (if ML): Analyze models used

**Pass 3 - Recommendations:**
- Superpowers: Plan recommendations
- Goodnotes: Visualize findings

**Pass 4 - Documentation:**
- Process Documentation AI: Document audit results
- MiniUp: Publish audit report

---

### Planning Agent (Design Focus)

**Pass 1 - Requirements & Scope:**
- GitHub: Understand current implementation
- Tavily: Research best practices, standards

**Pass 2 - Architecture & Design:**
- Superpowers: Design system, plan architecture
- Goodnotes: Visualize design
- Hugging Face (if AI): Research model options

**Pass 3 - Implementation Planning:**
- Superpowers: Create implementation steps
- Goodnotes: Diagram implementation flow

**Pass 4 - Documentation:**
- Process Documentation AI: Document procedures
- MiniUp: Publish architecture docs

---

### Execution Agent (DevOps Focus)

**Pass 1 - Setup & Foundation:**
- Superpowers: Plan deployment approach
- Build MCP Apps (if service): Scaffold initial setup
- GitHub: Understand current infrastructure

**Pass 2 - Core Functionality:**
- Manufact: Setup CI/CD pipeline
- Build MCP Apps (if needed): Complete service setup

**Pass 3 - Verification:**
- Manufact: Test deployment, verify health
- MiniUp (if needed): Publish dashboard

**Pass 4 - Documentation:**
- Process Documentation AI: Create runbooks
- Goodnotes (if needed): Visualize deployment topology

---

### Monitoring Agent (Observability Focus)

**Pass 1 - Instrumentation:**
- Superpowers: Plan instrumentation
- GitHub: Find existing monitoring

**Pass 2 - Alerts & Thresholds:**
- Superpowers: Define alerts
- Process Documentation AI: Plan alert procedures

**Pass 3 - Analysis & Dashboards:**
- Goodnotes: Design dashboard layouts
- Build Web Apps: Build interactive dashboards
- Tavily (if researching): Find monitoring best practices

**Pass 4 - Documentation:**
- Process Documentation AI: Document monitoring procedures
- MiniUp: Publish dashboards

---

## By Problem Domain

### Frontend Development
1. **Design**: Superpowers → Design interface
2. **Prototype**: Goodnotes → Wireframe interface
3. **Build**: Build Web Apps → Scaffold components
4. **Review**: CodeRabbit → Check quality
5. **Publish**: MiniUp → Share prototype

### Backend Development
1. **Design**: Superpowers → Design API
2. **Scaffold**: Build MCP Apps → Generate endpoints
3. **Implement**: Use GitHub → Understand patterns
4. **Review**: CodeRabbit → Check security
5. **Deploy**: Manufact → Setup CI/CD

### Full-Stack Feature
1. **Plan**: Superpowers → Design feature
2. **Architecture**: Goodnotes → Visualize flow
3. **Frontend**: Build Web Apps → Scaffold UI
4. **Backend**: Build MCP Apps → Scaffold API
5. **Testing**: CodeRabbit → Review both
6. **Deploy**: Manufact → Setup pipeline
7. **Document**: Process Documentation AI → Create SOP

### Code Quality & Security
1. **Scan**: CodeRabbit → Find issues
2. **Research**: Tavily → Find best practices
3. **Plan Fixes**: Superpowers → Design solutions
4. **Visualize**: Goodnotes → Show findings
5. **Document**: Process Documentation AI → Record procedures

### Research & Analysis
1. **Investigation**: GitHub → Understand scope
2. **Research**: Tavily → Gather info
3. **Analysis**: CodeRabbit → Code quality scan
4. **Visualization**: Goodnotes → Diagram findings
5. **Report**: MiniUp → Publish findings

### Architecture & Design
1. **Requirements**: GitHub + Tavily → Understand context
2. **Design**: Superpowers → Plan architecture
3. **Visualize**: Goodnotes → Draw diagrams
4. **Plan Build**: Build Web Apps + Build MCP Apps → Validate approach
5. **Document**: Process Documentation AI → Write procedures

---

## By Stage of Work

### Investigation (Passes 1)
- **Code Understanding**: GitHub
- **External Research**: Tavily
- **Current Implementation**: GitHub
- **Best Practices**: Tavily + Superpowers
- **Technology Research**: Hugging Face + Tavily

### Implementation (Passes 2)
- **Frontend Building**: Build Web Apps
- **Backend Building**: Build MCP Apps
- **Feature Design**: Superpowers
- **Code Planning**: Superpowers

### Hardening (Pass 3)
- **Code Review**: CodeRabbit
- **Security Check**: CodeRabbit
- **Quality Verification**: CodeRabbit
- **Testing**: Superpowers (plan) + CodeRabbit (verify)
- **Dashboard Build**: Build Web Apps or Goodnotes

### Documentation (Pass 4)
- **Procedures**: Process Documentation AI
- **Checklists**: Process Documentation AI
- **Training**: Process Documentation AI
- **Diagrams**: Goodnotes (if needed)
- **Publishing**: MiniUp (if needed)
- **Deployment**: Manufact (if needed)

---

## Quick Decision Tree

```
"What do I need to do?"

→ Understand existing code
  └─ Use: GitHub Plugin
     "Use GitHub to find/understand [component]"

→ Review code for quality/bugs
  └─ Use: CodeRabbit
     "Use CodeRabbit to review for [concern]"

→ Build user interface
  └─ Use: Build Web Apps
     "Use Build Web Apps to scaffold [UI]"

→ Create backend service
  └─ Use: Build MCP Apps
     "Use Build MCP Apps to generate [service]"

→ Deploy to production
  └─ Use: Manufact
     "Use Manufact to setup [deployment]"

→ Publish prototype/docs
  └─ Use: MiniUp
     "Use MiniUp to publish [content]"

→ Research external info
  └─ Use: Tavily
     "Use Tavily to research [topic]"

→ Find/compare AI models
  └─ Use: Hugging Face
     "Use Hugging Face to find [model type]"

→ Design/plan architecture
  └─ Use: Superpowers
     "Use Superpowers to design [feature]"

→ Create diagrams
  └─ Use: Goodnotes
     "Use Goodnotes to draw [diagram]"

→ Write procedures/SOPs
  └─ Use: Process Documentation AI
     "Use Process Documentation AI to create [doc]"
```

---

## Tool Combination Patterns

### Complete Feature Implementation
```
GitHub (understand) → 
Superpowers (design) → 
Build Web Apps (frontend) + Build MCP Apps (backend) → 
CodeRabbit (review) → 
Manufact (deploy) → 
Process Documentation AI (document)
```

### Security Audit
```
GitHub (scope) → 
CodeRabbit (scan) → 
Tavily (research) → 
Goodnotes (visualize) → 
Process Documentation AI (remediation procedures)
```

### New Service Launch
```
Superpowers (plan) →
Build MCP Apps (build) →
Manufact (deploy) →
Process Documentation AI (runbooks) →
MiniUp (publish docs)
```

### Technology Selection
```
Tavily (research options) →
Hugging Face (if ML) →
Superpowers (design integration) →
GitHub (review existing use in codebase)
```

### Documentation Sprint
```
GitHub (gather info) →
Goodnotes (create visuals) →
Process Documentation AI (write procedures) →
MiniUp (publish)
```

---

## Tool Limitations Summary

| Tool | Cannot Do |
|------|-----------|
| GitHub | Push code, create PRs (read-only) |
| CodeRabbit | Fix code (suggestions only) |
| Build Web Apps | Complex backends, real databases |
| Build MCP Apps | Business logic (stubs only) |
| Manufact | Traditional apps (MCP only), free production |
| MiniUp | Real-time updates, authentication, database |
| Tavily | Bypass paywalls, deep crawling |
| Hugging Face | Run models (metadata only) |
| Superpowers | Execute implementation (planning only) |
| Goodnotes | Complex diagrams, automatic layout |
| Process Documentation AI | Guarantee accuracy, integrate with code |

---

## Recommendation Algorithm

**Pick tool based on:**
1. **Task Category**: What type of work? (Find/Review/Build/Deploy/Document)
2. **Agent Type**: What role am I? (Code/Research/Planning/Execution/Monitoring)
3. **Pass Stage**: What pass am I in? (Investigation/Implementation/Hardening/Documentation)
4. **Tool Capability**: Can this tool do what I need?
5. **Integration**: Does this tool feed into next steps?

---

## Common Rookie Mistakes

❌ Using CodeRabbit for planning (use Superpowers)  
❌ Using Build Web Apps for backend (use Build MCP Apps)  
❌ Using Tavily for code finding (use GitHub)  
❌ Trying to run models with Hugging Face (metadata only)  
❌ Expecting Manufact to work with traditional apps (MCP only)  
❌ Publishing sensitive data to MiniUp (it's public)  
❌ Expecting Process Documentation AI to guarantee accuracy (review it!)

---

## Where to Get Help

- **Unsure which tool?** → Read this matrix
- **How to use tool?** → See `.titan/tools/[TOOL_NAME].md`
- **Need examples?** → Check EXTERNAL_TOOLS_GUIDE.md
- **Integration question?** → See tool guide "Integration with Agent Workflow"
- **Complex task?** → Use multiple tools in sequence

---

**Status**: Quick Reference  
**Keep This Bookmarked**: While working on .agent-workspace pass-plan.md  
**Last Updated**: July 31, 2026
