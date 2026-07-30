# Titan Agent OS Documentation Bootstrap Plan

> **For agentic workers:** implement this plan on `agent/documentation-reconciliation`; do not modify `main` directly.

**Goal:** Establish `.titan` as the governed Titan Agent OS layer while retaining `/docs` as the canonical human project-documentation library.

**Architecture:** The repository uses a federated two-documentation model. `/docs` remains the source of long-form human-authored architecture, governance, plans, audits, provenance and historical material. `/.titan/documentation` provides Agent OS-native onboarding, machine-generated views, status, progress, decisions, reviews, learning, dashboards, visualisations, history and Chronicle records. Derived `.titan` views must identify their canonical `/docs` sources and must not become competing manually maintained truth.

**Tech stack:** Markdown, YAML, JSON Schema, GitHub repository content.

## Global constraints

- Preserve WorkCore as the sole operational record and mutation authority.
- Preserve MagicAI host identity and membership authority.
- Preserve Titan Zero orchestration and Interaction Engine governance boundaries.
- Do not claim autonomous or generated capabilities that are not implemented.
- Nothing under `.titan/documentation/system/` may be manually edited once generators exist.
- Runtime files are transient; architecture, decisions, lessons and accepted policies are durable.
- All objects use controlled metadata, lifecycle status and source verification fields.
- Existing `/docs` content is referenced or derived, not copied wholesale.

## Task 1 — Agent OS entry points

- [ ] Create `.titan/README.md` as the Agent OS start page.
- [ ] Add the Claude Architecture Authority mandate to `.titan/MANDATE.md`.
- [ ] Create `.titan/os.yaml` with version, layer ownership and documentation-mode metadata.
- [ ] Create `.titan/control-plane/claude/README.md` pointing Claude to the mandate, repository rules and current status.

## Task 2 — Documentation layer

- [ ] Create `.titan/documentation/README.md` defining the three audiences and the federated `/docs` relationship.
- [ ] Create entry READMEs for architecture, developer, agents, system, progress, reports, decisions, reviews, learning, dashboards, visualisations, history, status and chronicle.
- [ ] Create `.titan/documentation/agents/START-HERE.md` as the mandatory worker-agent onboarding path.
- [ ] Create initial status and Chronicle records grounded in the current repository baseline.

## Task 3 — Kernel metadata and source registry

- [ ] Create the Agent OS constitution entry point.
- [ ] Create object-metadata and document-source JSON schemas.
- [ ] Create a documentation source registry mapping canonical `/docs` sources into `.titan` views.
- [ ] Create registry guidance that prohibits duplicate manual authorities.

## Task 4 — Repository integration

- [ ] Update root `README.md` to require reading `.titan/README.md` and `.titan/MANDATE.md` for architecture-control work.
- [ ] Update `docs/README.md` to explain the two-documentation model and link the Agent OS documentation layer.
- [ ] Update PR scope/status documentation with the Agent OS bootstrap.

## Task 5 — Verification

- [ ] Confirm all referenced paths exist.
- [ ] Confirm JSON schemas parse as JSON.
- [ ] Confirm YAML files are syntactically coherent by inspection or parser.
- [ ] Confirm no project documentation was added to the repository root.
- [ ] Confirm no application runtime, route, migration or domain code changed.
- [ ] Record unavailable executable validation as not run.
