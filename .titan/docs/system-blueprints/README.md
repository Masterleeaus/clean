# System Blueprints Documentation

**Status**: Integrated from Worksuite/Titan reference architecture  
**Content**: 115+ detailed markdown files  
**Relevance**: Reference for Agent OS design patterns  

## Included Documentation Sets

### 1. AI System (04-AI)
- Core AI orchestration patterns
- Multiple AI persona design
- Memory management for AI systems
- Context packing and retrieval
- Tool execution governance
- Model routing strategies
- Local vs external model policies
- AI safety and evaluation

**For Agent OS**: Reference for implementing multiple agent types, memory hierarchy, and tool safety.

### 2. PWA Surface (01-PWA)
- Progressive Web App architecture
- Frontend-backend coordination
- Offline-first patterns
- Service worker strategies
- State management
- Real-time updates

**For Agent OS**: *Mostly not applicable* - we're building a backend system, not a web app.

### 3. Signals Engine (02-Signals)
- Event emission patterns
- Signal routing
- Event subscription management
- Cross-system coordination
- Signal replay and recovery
- Backpressure handling

**For Agent OS**: *Highly applicable* - use for inter-agent communication and event-driven architecture.

### 4. Workflows (07-Workflows)
- Workflow definition structures
- State machine implementation
- Step handlers and guards
- Approval processes
- Escalation rules
- Recovery mechanisms
- Workflow metrics

**For Agent OS**: *Highly applicable* - foundation for task graphs and durable execution.

### 5. Communications (09-Communications)
- Multi-channel messaging
- Message routing
- Template systems
- Delivery receipts and retries
- Inbound callback handling
- Preference management

**For Agent OS**: *Partially applicable* - simplify for agent-to-agent messaging (no email/SMS).

## How to Navigate

### Finding Documentation by Topic

**Task Graphs & Workflows**
→ See: `07-workflows/`

**Agent Communication Patterns**
→ See: `09-communications/`, then simplify concepts

**Event-Driven Architecture**
→ See: `02-Signals/`

**AI Orchestration**
→ See: `04-AI/`

### Finding Documentation by Use Case

**"How should agents communicate?"**
→ Start: `09-communications/OVERVIEW.md` → Extract async/event patterns → Implement in `.titan/docs/protocols/`

**"How should workflows be structured?"**
→ Start: `07-workflows/DEFINITIONS.md` → Reference `.titan/docs/runtime/RUNTIME_API.md` → Extend task graph support

**"How should agents coordinate?"**
→ Start: `02-Signals/` → Study event patterns → Implement signal/event bus in Agent OS

**"What should be in an agent manifest?"**
→ Start: `04-AI/MANIFESTS.md` → Cross-reference `.titan/config/titan-agent-os.json` → Enhance agent schemas

## Adaptation Checklist

When studying a document from these blueprints:

- [ ] Read the document to understand the pattern
- [ ] Identify which parts apply to Agent OS
- [ ] Note which parts are Laravel/Filament specific
- [ ] Check if similar concepts exist in current `.titan/docs/`
- [ ] Plan how to adapt or extend current implementation
- [ ] Document any new patterns in `.titan/docs/architecture/`
- [ ] Update relevant schema files (.json files in `.titan/schemas/`)

## Not Included (Intentionally)

These directories were skipped as not applicable to Agent OS:

- **01-PWA/** - Progressive web app (web frontend specifics)
- **05-Node:PWA/** - Node.js + PWA patterns
- **06-automation/** - Business automation workflows
- **08-interfaces/** - Web UI/form interfaces
- **10-reference-architecture/** - Full system reference (mostly Laravel)
- **11-implementation-readiness/** - Project kickoff checklists
- **laravel_actual_page_extracts_micro/** - Laravel code snippets
- **architecture/** - Full Laravel architecture
- **Titan_Blueprints/** - Meta-documentation

## Relevant Files by Blueprint Number

**High Priority** (read first):
- 04: AI Core → `04-AI/CORE_ARCHITECTURE.md`
- 09: Signals → `02-Signals/ENGINE_OVERVIEW.md`
- 10: Workflows → `07-workflows/DEFINITIONS.md`
- 16: Manifests → `.../MANIFESTS.md` (see blueprints folder)
- 20: Observability → `.../HEALTH_DOCTOR.md` (see blueprints folder)

**Medium Priority** (reference as needed):
- 11: Communications → `09-communications/ROUTING.md`
- 19: Security → `.../SECURITY_AUDIT.md` (see blueprints folder)

**Low Priority** (skim only):
- 06, 07, 08: Filament/Module patterns
- 13, 14, 15: PWA/Web surfaces
- 17, 18: Tenancy/Multi-tenant

## Using This Documentation

1. **Start with Blueprints README**
   ```
   .titan/blueprints/README.md
   ```
   Overview of all 34 blueprints with relevance ratings.

2. **Deep Dive into High-Priority Topics**
   ```
   .titan/docs/system-blueprints/04-AI/
   .titan/docs/system-blueprints/02-Signals/
   .titan/docs/system-blueprints/07-workflows/
   ```

3. **Cross-Reference with Current Agent OS**
   ```
   .titan/docs/AGENT_OS.md
   .titan/docs/runtime/RUNTIME_API.md
   .titan/docs/protocols/AGENT_COMMUNICATION.md
   ```

4. **Plan Extensions**
   - Identify missing capabilities
   - Check if pattern exists in blueprints
   - Adapt and extend implementation
   - Document in `.titan/docs/architecture/`

## Quick File Index

| Path | Purpose |
|------|---------|
| `04-AI/` | AI personality, orchestration, routing |
| `02-Signals/` | Event emission and routing |
| `07-workflows/` | State machines and task definitions |
| `09-communications/` | Message routing patterns |

---

**Source**: Worksuite/Titan reference architecture  
**Added**: July 31, 2026  
**Maintained by**: System Architecture Team
