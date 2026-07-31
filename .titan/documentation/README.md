# Titan Agent OS Documentation Layer

This is the first-class documentation layer of Titan Agent OS. It serves three audiences with different requirements:

1. **Humans** — developers, architects, maintainers and product owners.
2. **AI agents** — Claude Architecture Authority and ChatGPT implementation agents.
3. **The system** — generated indexes, graphs, inventories, reports, dashboards and status projections.

## Federated documentation model

Titan Zero deliberately keeps two documentation trees:

- [`/docs`](../../docs/README.md) is the canonical long-form human project library. It owns accepted architecture, governance, implementation plans, audits, provenance, setup guidance, reference material and historical records.
- `/.titan/documentation` is the Agent OS knowledge and operating layer. It owns agent onboarding, generated system documentation, current status, progress projections, decision workflow, review memory, learning, dashboards, visualisations and the Project Chronicle.

Do not copy all `/docs` content into `.titan`. Create links, source registrations or derived views instead. A derived view must identify its canonical source and source commit.

## Audience map

| Area | Primary audience | Editing rule |
|---|---|---|
| `architecture/` | Humans and agents | Human-authored Agent OS design and derived architecture views |
| `developer/` | Humans | Human-authored guides, tutorials, playbooks and conventions |
| `agents/` | AI agents | Human-reviewed onboarding, roles, manifests, communication and journals |
| `system/` | System and agents | Generated only; never manually edited once generators exist |
| `progress/` | Humans, Claude and workers | Maintained from accepted plans, runtime work and releases |
| `reports/` | Humans and governance authorities | Generated or evidence-backed; source metadata required |
| `decisions/` | Humans and governance authorities | Proposed, accepted, rejected, superseded or archived records |
| `reviews/` | Humans and agents | Durable summaries of PR, architecture, code, security and AI reviews |
| `learning/` | Agents and maintainers | Lessons, failures, improvements, patterns, recommendations and experiments |
| `dashboards/` | Humans and control plane | Generated summaries; source inputs required |
| `visualisations/` | Humans and agents | Generated diagrams and graph projections |
| `status/` | Everyone | Short current answer to “Where are we?” |
| `history/` | Humans and agents | Durable snapshots, migrations, incidents and architectural evolution |
| `chronicle/` | Humans and agents | Curated explanation of why important changes happened |

## Source classes

Every durable document must be one of:

- **authored** — manually maintained in this tree;
- **generated** — regenerated from source and not manually edited;
- **derived** — generated or curated from a canonical source elsewhere;
- **reference** — useful evidence that is not current authority.

Recommended front matter:

```yaml
source:
  type: authored | generated | derived | reference
  canonical_path: docs/architecture/TITAN_ZERO_AUTHORITY_MAP.md
  source_commit: <git-sha>
  generator: null
  generated_at: null
status: active
owner: architecture-authority
last_verified: 2026-07-30
```

## Generated-content rule

Nothing in [`system/`](system/README.md) is manually edited after a generator is established. Each generated output must record:

- generator and generator version;
- source commit;
- generation timestamp;
- input paths;
- schema version;
- freshness or stale state.

A failed generation preserves the last valid output and marks it stale. It must not replace valid documentation with an empty or partial file.

## Start here

- [Worker-agent handbook](agents/START-HERE.md)
- [Current status](status/current.md)
- [Architecture documentation](architecture/README.md)
- [Developer documentation](developer/README.md)
- [Generated system documentation](system/README.md)
- [Engineering progress](progress/README.md)
- [Decision records](decisions/README.md)
- [Project Chronicle](chronicle/timeline.md)
- [Canonical project documentation](../../docs/README.md)

## Current maturity

This layer is a v1.0 bootstrap. Structure, source ownership and initial records exist. Continuous generation, World Model diagrams, automated dashboards, trust metrics and event-driven status updates remain planned until executable generators and validations are added.
