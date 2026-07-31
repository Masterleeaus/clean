# .titan Documentation Index

Complete guide to all documentation organized in the .titan system directory.

## Quick Navigation

- **[Getting Started](#getting-started)** - Start here for first-time users
- **[Core Documentation](#core-documentation)** - Architecture, design, and system overview
- **[Implementation Guides](#implementation-guides)** - Step-by-step guides for specific tasks
- **[Status & Progress](#status--progress)** - Current state and completion tracking
- **[Plans & Roadmaps](#plans--roadmaps)** - Future work and upgrade paths
- **[System Info](#system-info)** - Build info, workspace config, reports

---

## Getting Started

### Entry Points

1. **[README.md](../README.md)** - Main project README (in root)
2. **[INDEX.md](./INDEX.md)** - Titan system master index
3. **[ROADMAP.yaml](./ROADMAP.yaml)** - 8-phase system roadmap
4. **[IMPLEMENTATION_GUIDE.md](./IMPLEMENTATION_GUIDE.md)** - Phase 1 implementation guide

### For Different Roles

- **System Architects**: Start with `ROADMAP.yaml` → `constitution.yaml` → Phase specifications
- **Developers**: Start with `IMPLEMENTATION_GUIDE.md` → Relevant phase docs
- **Extension Builders**: See `docs/AGENTS.md` → `guides/DEPLOYMENT.md`
- **ChatGPT/AI Integration**: See `guides/chatgpt/` directory

---

## Core Documentation

### System Overview

**Location**: `.titan/docs/`

| File | Purpose | Audience |
|------|---------|----------|
| **AGENTS.md** | Agent system overview and capabilities | Architects, Leads |
| **APP_DIRECTORY_SUMMARY.md** | Application structure and directory organization | Developers |

### Architecture & Design

**Location**: `.titan/`

| File | Purpose | Key Topics |
|------|---------|-----------|
| **constitution.yaml** | Architectural rules and governance | DDD, bounded contexts, service boundaries |
| **INDEX.md** | Complete system index | Navigation, status matrix, module mapping |
| **MANDATE.md** | System mandate and core principles | Vision, goals, principles |
| **VISION.md** | System vision and capabilities | Long-term goals, design philosophy |
| **SYSTEM_STATUS.md** | Current operational status | Health, metrics, issues |

### Phase Specifications

**Location**: `.titan/issues/`

All 8 phases with detailed issue specifications:

| Phase | File | Focus Area |
|-------|------|-----------|
| **Phase 1** | `PHASE_1_FOUNDATION.md` | Agent manifests, task graphs, durable execution, memory |
| **Phase 2** | `PHASE_2_KNOWLEDGE.md` | Knowledge graphs, architecture drift detection |
| **Phase 3** | `PHASE_3_EXECUTION.md` | Agent teams, file locks, branch workflows |
| **Phase 4** | `PHASE_4_SAFETY.md` | Policy engine, sandboxing, approval gates, secrets |
| **Phase 5** | `PHASE_5_VALIDATION.md` | Evidence-based completion, static analysis, security review |
| **Phase 6** | `PHASE_6_INTEGRATION.md` | MCP compatibility, model router |
| **Phase 7** | `PHASE_7_OBSERVABILITY.md` | Change ledger, dashboard, self-improvement |
| **Phase 8** | `PHASE_8_OPERATIONS.md` | Release orchestrator, runtime API, health scoring |

### JSON Schemas

**Location**: `.titan/schemas/`

Type-safe validation schemas:

- `agent-manifest.schema.json` - Agent definitions
- `task-graph.schema.json` - Task execution DAGs
- `capability-registry.schema.json` - Capability definitions
- `policy.schema.json` - Policy definitions

### Configuration

**Location**: `.titan/`

- `registry/capabilities.yaml` - Global capability registry (50+ capabilities)
- `analysis/config.yaml` - Static analysis pipeline configuration
- `memory/README.md` - Memory hierarchy documentation

---

## Implementation Guides

### Location: `.titan/guides/`

| Guide | Purpose | Length | Audience |
|-------|---------|--------|----------|
| **DEPLOYMENT.md** | Deployment procedures and infrastructure | Reference | DevOps, Leads |
| **IMPORT_ONBOARDING_INTERACTION_ENGINE.md** | Interaction engine setup | Tutorial | Developers |

### ChatGPT/AI Integration

**Location**: `.titan/guides/chatgpt/`

Complete guides for ChatGPT integration:

| File | Purpose |
|------|---------|
| **CHATGPT_AGENT_IMPLEMENTATION_GUIDE.md** | Step-by-step implementation instructions |
| **CHATGPT_AGENT_INDEX.md** | Complete ChatGPT agent index and reference |
| **CHATGPT_AGENT_QUICK_REFERENCE.md** | Quick lookup reference |
| **CHATGPT_AGENT_SETUP_SUMMARY.md** | Setup checklist and summary |
| **CHATGPT_AGENT_WORKFLOWS_AND_ACTIONS.md** | Workflow definitions and action specifications |

**How to Use**:
1. Start with `CHATGPT_AGENT_SETUP_SUMMARY.md` for overview
2. Follow `CHATGPT_AGENT_IMPLEMENTATION_GUIDE.md` step-by-step
3. Use `CHATGPT_AGENT_QUICK_REFERENCE.md` for daily reference
4. Reference `CHATGPT_AGENT_WORKFLOWS_AND_ACTIONS.md` for detailed workflows

---

## Status & Progress

### Location: `.titan/status/`

Current state and completion tracking:

| File | Purpose | Updated |
|------|---------|---------|
| **PASS1_STATUS.md** | Phase 1 pass completion status | Latest |
| **PASS2_STATUS.md** | Phase 2 pass completion status | Latest |
| **BRANCH_STATUS.md** | Git branch status and merge progress | Latest |
| **RECONCILIATION_STATUS.md** | Reconciliation work status | Latest |
| **SOURCE_IMPORT_COMPLETE.md** | Import completion confirmation | Latest |

**How to Use**:
- Check current pass status before starting work
- Update status files after completing tasks
- Reference for tracking overall progress

---

## Plans & Roadmaps

### Location: `.titan/plans/`

Future work and upgrade strategies:

| File | Purpose | Priority |
|------|---------|----------|
| **UPGRADE_PLAN.md** | General upgrade strategy | P1 |
| **EXTENSION_PLATFORM_UPGRADE_PLAN.md** | Extension platform improvements | P2 |
| **TITAN_TRAIN_LMS_UPGRADE_PLAN.md** | Training LMS upgrades | P3 |
| **TITAN_TRAIN_LMS_BRANCH.md** | Training LMS branch work | P3 |
| **TITAN_ZERO_UPGRADE_PLAN.md** | Titan Zero system upgrades | P2 |
| **TITAN_ZERO_WIZARD_UPGRADE_PLAN.md** | Wizard component upgrades | P2 |
| **TITAN-ZERO-UPGRADE-PLAN.md** | Additional Titan Zero work | P2 |

### Roadmap

**Primary Roadmap**: See `ROADMAP.yaml` (8-phase, 32-week plan)

Key milestones:
- **Phase 1**: Foundation (Weeks 1-4)
- **Phase 2**: Knowledge (Weeks 5-8)
- **Phase 3**: Execution (Weeks 9-12)
- **Phase 4**: Safety (Weeks 13-16)
- **Phase 5**: Validation (Weeks 17-20)
- **Phase 6**: Integration (Weeks 21-24)
- **Phase 7**: Observability (Weeks 25-28)
- **Phase 8**: Operations (Weeks 29-32)

---

## System Info

### Location: `.titan/system/`

Build information and workspace configuration:

| File | Purpose |
|------|---------|
| **BUILD_REPORT.md** | Latest build report and metrics |
| **WORKSPACE.md** | Workspace configuration and setup |

---

## Session Documentation

**Location**: `.titan/`

| File | Purpose |
|------|---------|
| **SESSION_CLAUDE_20260730.md** | Complete session documentation of all work performed |
| **TODO.md** | Master checklist of all tasks |

---

## Additional Resources

### Git & Source Control

- **Branch Protection**: See `.titan/MAIN_BRANCH_LOCKED` and `.titan/MAIN_BRANCH_ADMIN.md`
- **Pre-push Hook**: See `.git/hooks/pre-push` (executable)
- **Backup Reference**: `backup/main-20260730-131013` (immutable snapshot)

### Issue Tracking

**GitHub Issues** (170+ issues from deep scan):
- #104-#115: Individual detailed issues
- #116-#119: Agent pass-based issues (PASS 1-4)
- #120: Master coordination issue
- #113: Deep scan summary

---

## How to Navigate

### By Use Case

**I'm new to the project**
→ Start: `../README.md` → `ROADMAP.yaml` → `IMPLEMENTATION_GUIDE.md`

**I want to implement Phase X**
→ Go to: `issues/PHASE_X_*.md` → Read requirements → Follow implementation guide

**I want to integrate with ChatGPT**
→ Go to: `guides/chatgpt/` → Start with `CHATGPT_AGENT_SETUP_SUMMARY.md`

**I want to deploy the system**
→ Go to: `guides/DEPLOYMENT.md` → `system/WORKSPACE.md`

**I want to check project status**
→ Go to: `status/` → Review relevant status files

**I want to plan future work**
→ Go to: `ROADMAP.yaml` and `plans/`

### By Role

**Architect**: `ROADMAP.yaml` → `constitution.yaml` → Phase specs → Architecture decisions
**Developer**: `IMPLEMENTATION_GUIDE.md` → Phase specs → Issue tracking (#104-#119)
**DevOps**: `guides/DEPLOYMENT.md` → `system/WORKSPACE.md` → Build reports
**Extension Builder**: `docs/AGENTS.md` → `guides/DEPLOYMENT.md` → Phase 3
**AI/ChatGPT Dev**: `guides/chatgpt/` → Follow complete workflow

---

## Documentation Standards

### When Adding New Documentation

1. **Place in appropriate directory**: docs/ | guides/ | status/ | plans/ | system/
2. **Update this index** with reference to new file
3. **Use clear headings** for navigation
4. **Include purpose and audience** at top
5. **Link to related documents** for cross-reference
6. **Keep consistent formatting** with existing docs

### File Naming

- **Concepts**: `CONCEPT_NAME.md` (e.g., `AGENTS.md`)
- **Guides**: `TOPIC_IMPLEMENTATION_GUIDE.md` (e.g., `CHATGPT_AGENT_IMPLEMENTATION_GUIDE.md`)
- **Status**: `COMPONENT_STATUS.md` (e.g., `BRANCH_STATUS.md`)
- **Plans**: `TOPIC_PLAN.md` or `TOPIC_UPGRADE_PLAN.md`

---

## Last Updated

- **Reorganized**: 2026-07-31
- **Content**: 30+ documentation files
- **Coverage**: Complete system documentation
- **Status**: All core documentation indexed and organized

---

_Navigation Guide for .titan System Documentation_
