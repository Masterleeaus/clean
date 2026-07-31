# External Tools & AI Plugins for Agent Workflows

**Status**: Production Ready  
**Purpose**: Guide agents on which external tools to use for different tasks  
**Integration**: ChatGPT plugins installed and ready  

---

## 🎯 Quick Reference by Task

### "I need to understand existing code"
→ **GitHub Plugin** (Search repos, browse code, review commit history)
→ `.titan/tools/REPOSITORY_TOOLS.md`

### "I need to review code for quality"
→ **CodeRabbit** (Automated PR reviews, bug detection)
→ `.titan/tools/REVIEW_TOOLS.md`

### "I need to build a user interface"
→ **Build Web Apps** (React/Next.js scaffolding, dashboards)
→ `.titan/tools/FRONTEND_TOOLS.md`

### "I need to create a backend service"
→ **Build MCP Apps** (MCP server generation, ChatGPT UI)
→ `.titan/tools/BACKEND_TOOLS.md`

### "I need to deploy code"
→ **Manufact** (MCP deployment, CI/CD, testing)
→ `.titan/tools/DEPLOYMENT_TOOLS.md`

### "I need to publish a static site"
→ **MiniUp** (Static hosting, utilities, datasets)
→ `.titan/tools/PUBLISHING_TOOLS.md`

### "I need research & data"
→ **Tavily AI** (Web crawling, document scraping, knowledge ingestion)
→ `.titan/tools/RESEARCH_TOOLS.md`

### "I need to select or benchmark AI models"
→ **Hugging Face** (Model discovery, dataset lookup, benchmarking)
→ `.titan/tools/AI_MODEL_TOOLS.md`

### "I need to create architecture & planning docs"
→ **Superpowers** (Planning, TDD, architecture, process)
→ `.titan/tools/PLANNING_TOOLS.md`

### "I need to create diagrams & visuals"
→ **Goodnotes** (Flowcharts, architecture diagrams, mind maps)
→ `.titan/tools/VISUALIZATION_TOOLS.md`

### "I need to generate SOPs & procedures"
→ **Process Documentation AI** (Workflow docs, checklists, training materials)
→ `.titan/tools/DOCUMENTATION_TOOLS.md`

---

## 🛠️ Tools by Category

### Repository & Code Navigation

#### GitHub Plugin
**When to use**: "I need to find/understand code"

**What it does**:
- Search repositories for files, functions, classes
- Read code and documentation
- Browse commit history
- Query issues and pull requests
- View file/repo structure

**Agent types**: Code Agent, Research Agent, Monitoring Agent

**Example prompts**:
```
"Use GitHub plugin to search Titan Zero for where the job assignment logic is implemented"
"Find all tests related to the auth middleware using GitHub"
"Show me the commit history for the WorkCore API endpoints"
```

**Limitations**: Read-only, cannot push commits or create PRs

---

### Code Quality & Review

#### CodeRabbit
**When to use**: "I need to review code for bugs and quality"

**What it does**:
- Analyze code diffs for bugs
- Detect security issues
- Flag missing tests
- Suggest fixes automatically
- Style and best practice checks
- Process verification

**Agent types**: Code Agent, Review Agent

**Example prompts**:
```
"Use CodeRabbit to review PR #245 and list any security risks or missing tests"
"Check this code for common bug patterns and suggest fixes"
"Analyze the changes in branch feature/payment-integration for completeness"
```

**Limitations**: Requires CodeRabbit account, cannot fix code automatically, may miss architecture issues

---

### Frontend Development

#### Build Web Apps (Sites)
**When to use**: "I need to build a user interface, dashboard, or PWA"

**What it does**:
- Scaffold React/Next.js applications
- Generate responsive layouts
- Create dashboards and forms
- Build progressive web apps
- Auto-generate boilerplate for auth/API integration
- Deploy to shareable URLs
- Preview in real-time

**Agent types**: Frontend Engineer (Code Agent specialization)

**Example prompts**:
```
"Use Build Web Apps to scaffold a Titan Zero staff portal with:
- Authentication page
- Jobs list with filtering
- Customer details table
- Dark mode toggle"

"Create a React PWA for offline job tracking with sync capability"
"Build an interactive dashboard showing team performance metrics"
```

**Limitations**: May need manual refinement, limited private API access without guidance, SQLite for data

---

### Backend & Service Development

#### Build MCP Apps
**When to use**: "I need to create a backend service or MCP tool"

**What it does**:
- Scaffold MCP servers with full stack
- Generate MCP tool code (OpenAPI + handlers)
- Create ChatGPT UI (Skybridge) for tools
- Include authentication flows
- Generate endpoint implementations
- Create plugin manifests

**Agent types**: Code Agent, Execution Agent (backend focus)

**Example prompts**:
```
"Use Build MCP Apps to scaffold a WorkCore MCP server with:
- /customers GET (list) and POST (create) endpoints
- /jobs GET (list) and PATCH (update) endpoints
- /invoices GET (list) and POST (create) endpoints
- Include auth for ChatGPT integration"

"Create an MCP tool for the Titan AI Runtime that handles task scheduling"
"Generate a ChatGPT plugin UI for the existing WorkCore MCP server"
```

**Limitations**: Generates code but needs integration into Titan architecture, complex logic requires refinement

---

### Deployment & DevOps

#### Manufact
**When to use**: "I need to deploy MCP services, setup CI/CD, or manage infrastructure"

**What it does**:
- Auto-deploy on GitHub commits/PRs
- Interactive tool testing (Cloud Inspector)
- Build and runtime logs
- Release management and versioning
- Rollback capabilities
- Cross-client testing
- Analytics and usage tracking
- Marketplace publishing support

**Agent types**: Execution Agent, DevOps/Deployment specialist

**Example prompts**:
```
"Use Manufact to connect the WorkCore MCP server GitHub repo and setup:
- Auto-deploy on main branch commits
- Preview deployments for each PR
- Cross-client testing before release"

"Setup Manufact to monitor the Titan AI Runtime MCP and show usage analytics"
"Use Manufact to publish the Titan Zero WorkCore MCP to the ChatGPT Marketplace"
```

**Limitations**: Paid service for production, focused on MCP workloads only, external DBs needed for data storage

---

### Static Publishing & Utilities

#### MiniUp
**When to use**: "I need to quickly publish a site, utility, or dataset"

**What it does**:
- Publish static websites and SPAs
- Convert tables/data to JSON APIs
- Host datasets as queryable endpoints
- Upload and extract ZIP archives
- Create simple CRUD APIs from tables
- Publish generated utilities

**Agent types**: Execution Agent, Frontend Engineer

**Example prompts**:
```
"Use MiniUp to publish this KPI dashboard HTML as a live website and give me the URL"

"Convert this equipment register CSV to a queryable JSON API using MiniUp"

"Upload the generated quote calculator tool to MiniUp and create a shareable link"

"Publish the Titan documentation static site using MiniUp"
```

**Limitations**: Read-only after publish, no authentication/login, no database, basic features only

---

### Research & Data Gathering

#### Tavily AI
**When to use**: "I need to research external information, regulations, or crawl websites"

**What it does**:
- Web search and lookup
- Website crawling (multi-page depth)
- Document extraction and summarization
- Regulatory/standards research
- API discovery and documentation lookup
- Knowledge base ingestion
- Competitor analysis

**Agent types**: Research Agent, Planning Agent

**Example prompts**:
```
"Use Tavily to crawl the NSW environmental health regulations site and extract key cleaning compliance requirements"

"Research PestZap API endpoints and integration requirements using Tavily"

"Crawl competitor websites and summarize their service offerings"

"Use Tavily to find and summarize Australian WHS guidelines for cleaning operations"
```

**Limitations**: May not bypass paywalls, quality depends on site structure, needs manual filtering

---

### AI Model & Dataset Selection

#### Hugging Face
**When to use**: "I need to find or evaluate AI models or datasets"

**What it does**:
- Search models by task (vision, NLP, speech, etc.)
- Compare model specs and performance
- View model benchmarks and reviews
- Search datasets for training
- Explore demo spaces (UI applications)
- Filter by language, license, size
- Access model documentation

**Agent types**: Research Agent, AI Engineer

**Example prompts**:
```
"Use Hugging Face to compare open-source multilingual models suitable for:
- Summarizing user manuals (4-5 languages)
- Under 7B parameters
- Apache 2.0 license"

"Find computer vision models on Hugging Face that can detect pest types for Titan's inspection module"

"Search for datasets containing cleaning procedures and work safety guidelines"
```

**Limitations**: Metadata lookup only, cannot run models, requires separate compute for inference

---

### Architecture & Planning

#### Superpowers
**When to use**: "I need to plan a major feature or ensure rigorous engineering"

**What it does**:
- Feature breakdowns and system designs
- Implementation planning
- Test-driven development (TDD) workflow
- Code review process guidance
- Debugging and verification suggestions
- Architecture documentation
- Process enforcement

**Agent types**: Planning Agent, Chief Architect

**Example prompts**:
```
"Use Superpowers to design a new Titan Sprout module that:
- Scaffolds business templates for customers
- Includes approval workflows
- Has offline-first sync

Start with design and API, then list all development steps"

"As Chief Architect, use Superpowers to create a detailed plan for integrating the AI runtime into WorkCore"

"Use Superpowers to design the database schema for multi-tenant customer data in Titan Zero"
```

**Limitations**: Planning only, doesn't execute code, relies on other tools to implement

---

### Visualization & Diagrams

#### Goodnotes
**When to use**: "I need to create architecture diagrams, flowcharts, or visual documentation"

**What it does**:
- Generate flowcharts from descriptions
- Create architecture diagrams
- Build mind maps
- Generate UML diagrams
- Visualize data/control flows
- Export as SVG or image
- Create process diagrams

**Agent types**: Planning Agent, Documentation Agent

**Example prompts**:
```
"Use Goodnotes to create a UML sequence diagram for the 'Job Completion' workflow in Titan:
- Staff marks job complete
- System validates completion
- Invoice is generated
- Customer is notified"

"Draw the five-tier Titan AI runtime architecture showing all components and data flows"

"Create a mind map of all Titan Zero modules: WorkCore, ZeroPay, Voice, etc."

"Generate a flowchart for the customer onboarding process in Titan"
```

**Limitations**: Automatic placement may need tweaking, complex diagrams might be coarse

---

### Documentation & SOPs

#### Process Documentation AI
**When to use**: "I need to create standard procedures, checklists, or operational guides"

**What it does**:
- Generate standard operating procedures (SOPs)
- Create workflow documentation
- Build checklists and task lists
- Generate training materials
- Document compliance procedures
- Create onboarding guides
- Generate quality assurance checklists

**Agent types**: Documentation Agent, Operations

**Example prompts**:
```
"Use Process Documentation AI to create a step-by-step SOP for:
- Daily cleaner checklist including safety measures
- Equipment preparation and cleanup
- Quality inspection points
- Incident reporting"

"Generate the deployment checklist for releasing Titan Zero updates to production"

"Create an onboarding guide for new team members joining Titan Zero development"

"Generate a compliance checklist for auditing Titan's security and data handling"
```

**Limitations**: High-level only, needs review for accuracy, not code-integrated

---

## 📊 Tool Selection Matrix

| Task | Best Tool | Secondary | Agent Type |
|------|-----------|-----------|-----------|
| **Find/understand code** | GitHub | - | Code, Research |
| **Review code for bugs** | CodeRabbit | Superpowers | Code, Review |
| **Build UI/Dashboard** | Build Web Apps | Goodnotes | Frontend |
| **Create backend service** | Build MCP Apps | Superpowers | Code |
| **Deploy/CI-CD** | Manufact | - | Execution |
| **Publish static site** | MiniUp | - | Frontend |
| **Research data** | Tavily AI | GitHub | Research |
| **Find AI models** | Hugging Face | Tavily | Research |
| **Plan architecture** | Superpowers | Goodnotes | Planning |
| **Create diagrams** | Goodnotes | - | Planning |
| **Write SOPs** | Process Documentation AI | - | Documentation |

---

## 🚀 Workflow Examples

### Code Agent: Fixing a Bug

1. **Find the bug**: GitHub Plugin → Search for error message location
2. **Understand context**: GitHub → Read related code and tests
3. **Plan fix**: Superpowers → Create implementation plan
4. **Review after fix**: CodeRabbit → Analyze changes for quality
5. **Deploy**: Manufact → Setup auto-deploy when PR merges

### Frontend Agent: Building a Dashboard

1. **Research requirements**: GitHub → Find existing dashboards
2. **Design mockup**: Goodnotes → Create wireframes/layout
3. **Build UI**: Build Web Apps → Scaffold React dashboard
4. **Review**: CodeRabbit → Check for responsive design issues
5. **Publish**: MiniUp → Host preview version

### Backend Agent: Creating New Service

1. **Design API**: Superpowers → Plan endpoints and schema
2. **Generate service**: Build MCP Apps → Scaffold MCP server
3. **Review code**: CodeRabbit → Check implementation
4. **Deploy**: Manufact → Setup CI/CD pipeline
5. **Test**: Manufact Cloud Inspector → Verify endpoints work

### Research Agent: Gathering Compliance Info

1. **Research regulations**: Tavily → Crawl regulatory sites
2. **Find models**: Hugging Face → Look for compliance checking models
3. **Document findings**: Process Documentation AI → Create SOP
4. **Visualize**: Goodnotes → Create compliance flowchart

---

## ⚠️ Important Notes

### API Rate Limits
Some tools have rate limits:
- GitHub: Unlimited for repositories you have access to
- CodeRabbit: May limit reviews by account tier
- Tavily: May have crawl limits (check free tier)
- Hugging Face: Free tier available, no charges for metadata

### Authentication
Most tools require setup:
- GitHub: Needs repository access configuration
- CodeRabbit: Requires account connection
- Build Web Apps/MCP Apps: Built into ChatGPT
- Manufact: Needs project setup and Git integration
- MiniUp: Direct upload capability

### Cost Considerations
- **Free tools**: GitHub, Build Web Apps, Build MCP Apps, Hugging Face, Goodnotes, Process Documentation AI
- **Free tier available**: CodeRabbit, Tavily, MiniUp
- **Paid service**: Manufact (production deployments)

---

## 🔗 Related Documentation

- `.titan/AGENT_PROMPT.md` - Agent prompts with tool guidance
- `.titan/docs/agents/AGENT_DEVELOPMENT.md` - Agent-specific tools
- `.titan/workflows/AGENT_WORKFLOW.md` - How agents work
- `.titan/tools/REPOSITORY_TOOLS.md` - GitHub setup and usage
- `.titan/tools/REVIEW_TOOLS.md` - CodeRabbit setup
- (Additional tool guides in `.titan/tools/` directory)

---

**Status**: ✅ Ready to use  
**Last Updated**: July 31, 2026  
**Integration Level**: Full ChatGPT plugin integration
