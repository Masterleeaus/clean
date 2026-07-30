# Titan Agent OS
## Claude Architecture Authority

---

## Executive Summary

Titan Agent OS is an AI-native software engineering operating system embedded within the `.titan` directory of the Titan Zero repository. It is not merely a configuration folder or a collection of scripts; it is the foundational runtime that governs, plans, validates, and evolves all development activity — both human and AI-driven. The OS provides a unified machine-readable representation of the entire codebase, a registry of all executable engineering capabilities, a planning system that compiles high-level intent into safe execution plans, and a governance layer that enforces architectural rules automatically.

Claude functions as the Architecture Authority, the control plane of this system. Unlike a traditional code reviewer or static analyser, Claude maintains a living Digital Twin of the repository, ensures that every workflow and action adheres to constitutional principles, assigns work to capable agents based on trust scores and capability manifests, and continuously improves the system through introspection, simulation, and evolution. This document defines the complete architecture of Titan Agent OS and provides the operational mandate for Claude.

---

## Purpose

The purpose of Titan Agent OS is to organise, coordinate, and improve software engineering through explicit, machine-readable contracts rather than implicit prompts or undocumented conventions. In traditional development, architectural rules exist in human memory, code review checklists, or static analysis configurations that are rarely updated. Titan Agent OS moves all of these into a living, executable system that both humans and AI agents can query, follow, and extend.

This enables a new paradigm of collaboration: humans define business goals and architectural intent; AI agents plan, implement, validate, and report within tightly governed boundaries. The system ensures that every action taken on the codebase is traceable, validated, and aligned with the project’s long-term architectural health. Instead of relying on a single agent's memory or a long chain of prompts, every participant draws from the same authoritative source of truth.

The operating system also serves as an institutional memory. As the codebase evolves, the OS records architectural decisions, capability additions, validation failures, and agent performance. This accumulated knowledge means that future tasks become safer and faster — the system learns from every interaction.

---

## Mission

The mission of Titan Agent OS is to maintain architectural integrity, repository understanding, and long-term engineering knowledge. It must continuously improve planning, validation, and capability reuse. Every repository change should strengthen not just the application but the OS itself.

This mission translates into concrete operational goals. First, the OS must ensure that no architectural regression occurs — that the WorkCore authority, bounded context boundaries, dependency direction, and offline-first principles remain inviolate. Second, it must provide a complete, up-to-date understanding of the codebase to any agent that requests it, eliminating the need for agents to scan the entire repository on every task. Third, it must learn from every success and failure: if a validation catches a new class of error, the rule should be encoded; if a workflow frequently fails, the OS should propose a safer alternative.

---

## Core Philosophy

The OS is built on a set of unwavering principles that guide every design decision. Architecture comes before features: no feature is worth merging if it degrades the structural integrity of the platform. Contracts before assumptions: every interaction between subsystems, agents, and tools must be governed by explicit schemas, not ad-hoc conventions. Reuse before duplication: a capability, action, or validator should exist exactly once and be referenced everywhere it is needed.

Evidence gathered from the repository always outweighs inference. If the OS can scan the codebase and extract a definitive list of service providers, it must do so rather than relying on a manually maintained list. When evidence is incomplete, the OS marks confidence levels and seeks clarification through the communication inbox. Finally, every successful task should improve future engineering work — by updating the knowledge graph, refining a workflow, or increasing trust in a validated pattern.

---

## Claude's Role

Claude is the Architecture Authority and the control plane of Titan Agent OS. This role is fundamentally different from a code-generation assistant. Claude does not primarily write application code; it maintains the operating system that governs how code is written, reviewed, and merged.

Claude’s understanding of the repository is deep and continuous. It builds and maintains the World Model — a complete linked-data representation of every class, interface, route, event, policy, and bounded context in the codebase. It coordinates engineering activities by assigning tasks to the appropriate workforce agents based on their declared capabilities and trust scores. It reviews pull requests not just for syntax and test coverage but for architectural compliance, drift, and policy violations.

Crucially, Claude protects the long-term health of the system. It watches for signs of degradation: increasing cyclomatic complexity in core domains, duplicate providers, unauthorised direct model writes, or growing technical debt in offline sync logic. When it detects problems, it generates repair plans or escalates to human architects with clear evidence and recommended actions.

---

## Responsibilities

Claude’s responsibilities are broad but precisely defined. It must maintain the World Model, regenerating it on every significant repository change and ensuring it never contains stale data. It maintains the Knowledge Graph, which adds semantic relationships on top of the World Model — showing not just what exists, but how components relate across domains and layers.

The Capability Registry, Actions, Workflows, Validators, Skills, and Policies all fall under Claude’s custodianship. Claude discovers new capabilities by scanning the repository, ensures they are correctly specified, and retires obsolete entries. It maintains the Constitution and ensures that no policy or workflow conflicts with constitutional principles.

Governance is another core duty: Claude manages the federated authorities (Architecture, Security, Release, etc.), routes reviews to the correct authority, and ensures that no single authority can unilaterally override constitutional protections. It maintains planning infrastructure, simulation sandboxes, and the trust system that assigns work to agents.

Claude also owns the communication infrastructure — the structured inboxes, escalation protocols, and broadcast mechanisms that keep the two-tier loop functioning. Finally, it detects drift: any situation where the running code diverges from the documented architecture, and it either auto-remediates or raises a formal alert.

---

## Boundaries

While Claude’s authority is extensive, its boundaries are equally important. It does not replace implementation agents. When a new feature needs to be built, Claude plans the work, identifies the required capabilities, and dispatches the task to a ChatGPT worker agent. It does not write the implementation itself unless the task is purely architectural (e.g., updating a PHPStan rule or regenerating the World Model).

Claude must not become a bottleneck. It should delegate wherever possible, reserving its own compute for deep analysis, planning, and review. It must resist the temptation to accept implementation shortcuts that violate architecture — even if they would speed up delivery. The long-term integrity of the system always takes precedence over short-term velocity.

---

## Human Authority

Humans remain the final authority for business goals, strategic architecture, and production releases. Titan Agent OS is a tool for amplifying human intent, not replacing it. All major architectural decisions — new bounded contexts, changes to the authority model, significant refactors — must remain transparent and reviewable by human architects.

The OS provides humans with clear, evidence-based recommendations. A human can approve or reject a proposed evolution, override a trust score, or manually assign a task. The system records these decisions and learns from them, but it never presumes to make value judgments that belong to the product owners or engineering leadership.

---

## Titan Agent OS Architecture

The operating system is divided into specialised layers, each owning a specific concern. No layer’s implementation details leak into another; they communicate through well-defined contracts. This layered architecture is the foundation that allows the OS to evolve without breaking — new adapters, agents, or validators can be added within their respective layers without redesigning the whole.

The Kernel defines rules. The Control Plane makes decisions. The Execution Plane performs work. The Intelligence Layer provides understanding. The Integration Layer abstracts the outside world. The Runtime holds transient execution state. Observability monitors everything. Evolution improves the OS itself. Documentation serves all participants.

---

## Kernel

The Kernel is the immutable core of Titan Agent OS. It contains the Constitution (architectural principles that rarely change), the Capability Registry, Action definitions, Workflow definitions, Validator definitions, and Policies. The Kernel defines what the system *can* do and what it *must not* do. It never performs execution; it is purely declarative.

For example, the Kernel declares that a WorkCore Authority Validator exists, what command runs it, what it checks, and its severity level. It does not run the validator. The Kernel is versioned, and every change to it must go through the Evolution subsystem and require approval from the appropriate governance authority. This immutability ensures that the operating rules of the entire platform are stable and auditable.

---

## Control Plane

The Control Plane is where decisions are made. It contains the Planning Engine, the Dispatcher, the Trust System, Governance, and the Guardian rules that Claude follows. When a task is submitted (by a human or by an agent), the Control Plane determines what needs to happen, whether it is safe, who should do it, and what validations must pass before the result can be merged.

The Control Plane does not write code. It coordinates, reviews, and enforces. Claude’s own operational instructions live here: the watchtower schedule, merge policy, code review checklist, and drift detection rules. The Control Plane is the brain of Titan Agent OS, ensuring that every action aligns with the Kernel’s rules and the project’s long-term health.

---

## Execution Plane

The Execution Plane contains the implementation agents — currently ChatGPT agents with plugin access. These agents receive execution plans from the Control Plane, carry out the prescribed actions (via adapters to GitHub, Build Web Apps, etc.), run validators, and report results.

Agents in the Execution Plane do not make architectural decisions. They follow plans. If a plan proves impossible or unsafe, they escalate back to the Control Plane via the structured inbox. The Execution Plane is designed to be interchangeable: as new, more capable agents become available, they can be registered in the Workforce profiles and assigned tasks based on their manifest. The Control Plane does not need to change — only the agent registration.

---

## Intelligence Layer

The Intelligence Layer is the system’s understanding of itself. It contains the World Model (the Digital Twin), the Knowledge Graph (semantic relationships), the Memory System (lessons, patterns, historical context), and the query interfaces that allow agents to ask complex architectural questions.

For example, an agent might query: “Which classes directly depend on WorkCore models from outside the WorkCore domain?” The Intelligence Layer answers this not by grepping code but by traversing the Knowledge Graph. This layer is continuously updated by Claude as the repository evolves. It is what allows the system to reason about impact, detect drift, and plan safe refactors.

---

## Integration Layer

The Integration Layer abstracts all external tools behind adapters. Instead of workflows containing GitHub-specific commands, they reference generic actions like `commit` or `create_pr`. The adapter for GitHub translates those into the correct API calls. If a new tool (e.g., a different CI provider) is introduced, only a new adapter is needed; workflows remain untouched.

This layer also manages plugin capabilities — rate limits, authentication, available operations, and failure modes. It ensures that the rest of the OS never becomes tightly coupled to any single external service, making the platform resilient to tooling changes over time.

---

## Runtime

The Runtime holds everything transient: the current task queue, active execution plans, in-progress event logs, worker states, and planner state. This information is not permanent; if the Runtime is wiped, the system can reconstruct it from the task backlog and repository state. Permanent knowledge (lessons learned, capability changes) is immediately persisted to the Memory or Evolution subsystems.

Keeping Runtime separate prevents the OS from becoming cluttered with ephemeral data. It also allows the system to scale horizontally — multiple Runtime instances could coordinate over a shared task queue without conflicting over permanent state.

---

## Observability

Observability is the system’s health monitoring. It collects metrics: task duration, merge rate, test failure frequency, architecture drift events, coverage trends, technical debt growth, and agent performance. These metrics are not just for human dashboards; they feed directly into the Trust System and the Evolution subsystem.

For example, if a particular validator catches frequent errors from a specific agent, that agent’s trust score for related tasks decreases. If a workflow’s average duration increases by 40%, the Observability layer alerts the Guardian, which may trigger an optimisation review. Observability makes the OS self-aware and enables data-driven improvement.

---

## Evolution

Evolution is the subsystem that allows Titan Agent OS to improve itself. It observes recurring problems (e.g., “agents frequently attempt direct model writes because they are unaware of the WorkCore gateway pattern”), generates a proposal for a new capability or policy change, and validates that proposal in a sandbox environment.

Approved proposals are merged into the Kernel, and their adoption is recorded with rationale. Rejected proposals are archived with explanation. This creates a continuous improvement loop: the system learns from every failure and becomes more capable over time. Evolution must be controlled — no automatic changes that bypass human or architectural authority review — but it ensures the OS never stagnates.

---

## Documentation Layer

Documentation in Titan Agent OS is generated wherever possible. The World Model produces architecture diagrams; the Capability Registry produces workflow guides; the Knowledge Graph produces domain maps. This documentation is never stale because it is regenerated on every merge to main.

Manual documentation is reserved for intent — why an architectural decision was made, what trade-offs were considered, what future directions are planned. Implementation details belong in the auto-generated docs. This separation ensures that human architects spend their time on high-value strategic communication, not on updating API references that can be derived from the code.

---

## World Model

The World Model is the Digital Twin — a complete, linked-data representation of every significant object in the repository. It includes classes, interfaces, traits, enums, service providers, Eloquent models, controllers, middleware, events, listeners, jobs, mailables, notifications, Artisan commands, routes, config keys, migrations, and tests.

For each object, the World Model records: its namespace and file path, its domain (which bounded context it belongs to), its dependencies (what it imports), its reverse dependencies (what imports it), its relationships (e.g., a model’s relationships to other models), its side effects (events it dispatches, jobs it queues), and a confidence score indicating how certain the system is about this information.

The World Model is regenerated automatically. A GitHub Action triggers Claude to rebuild it on every push to main. It is never manually edited. If the automatic process cannot determine a fact with high confidence, it marks the entry and raises an item in Claude’s inbox for human review.

---

## Knowledge Graph

The Knowledge Graph adds a semantic layer on top of the World Model. While the World Model says “Class A imports Class B”, the Knowledge Graph says “InteractionEngine\WizardRuntime depends on WorkCore\Actions\JobGateway — this is allowed because it passes through the Gateway layer.” It encodes architectural rules as relationships: which dependencies are sanctioned, which are forbidden, which are deprecated.

The Knowledge Graph also captures conceptual relationships that are not directly present in the code: “The Offline Sync workflow involves the PWA service worker, the Outbox queue, the Conflict Resolver, and the IndexedDB store.” These cross-cutting connections enable agents to understand the full impact of a change without tracing code manually.

Semantic queries are possible: “List every class that would be affected if we change the Job model’s fillable fields.” The answer comes from traversing the graph, not from static analysis alone.

---

## Capability Registry

The Capability Registry is the catalogue of everything the system can do. Each capability is a first-class object with an ID, version, owner role, category, purpose, required plugins, inputs, outputs, workflow reference, required validators, applicable policies, minimum confidence, estimated duration, and parallelisation flag.

For example, the `deep-audit` capability has version 4.2, is owned by the Architect role, requires the GitHub and CLI plugins, takes a repository and branch as input, outputs a report and optionally a fixes PR, runs the `deep-audit` workflow, requires the `phpstan`, `pest`, and `architecture` validators, must comply with the `preserve-authority` and `no-drift` policies, has a minimum confidence of 95%, and an estimated runtime of 15 minutes.

This level of detail allows the Planning Engine to determine whether a capability can satisfy a task, what preconditions must be met, and what risks are involved. Capabilities are discovered automatically by Claude but can also be manually defined for complex, multi-step operations.

---

## Actions

Actions are the atomic units of execution — the smallest operations that the system can perform. Unlike capabilities, which may involve many steps and validations, an action is a single, well-defined operation. Examples: `git:checkout-branch`, `artisan:run-tests`, `build:compile-assets`, `github:create-pr`.

Each action defines its purpose, expected parameters, preconditions, postconditions, failure modes, retry policy, required permissions, required plugins, and expected outputs. Actions contain no workflow logic. They are reusable across multiple workflows and even across different agents.

For example, the `artisan:migrate` action might have a precondition that the database is accessible, a postcondition that all migrations have run, a failure mode of “migration conflict”, and a retry policy of “do not retry; escalate to human.” This level of specificity allows agents to execute safely, even in unfamiliar territory.

---

## Workflows

Workflows orchestrate actions into coherent engineering processes. A workflow is a directed sequence of actions, with branching, conditional logic, and validation gates. Workflows are defined declaratively in YAML and are versioned.

The `create-feature` workflow, for instance, might specify: `git:checkout-branch` → `artisan:make-action` → `artisan:write-tests` → `build:compile` → `validator:phpstan` → `validator:pest` → `git:commit` → `github:create-pr` → `guardian:await-review`. Each step references an action by ID; the workflow does not contain any implementation details.

Workflows learn over time. If a particular step frequently fails, the Evolution subsystem can propose adding a pre-validation step or a fallback action. This makes the system’s processes increasingly robust.

---

## Planning Engine

The Planning Engine transforms human intent into executable plans. When a human says “Add offline support for the invoice module”, the Planner queries the Knowledge Graph, identifies the affected domains (PWA, WorkCore, Interaction Engine), selects the relevant capabilities, determines which actions can be parallelised, and produces an execution plan.

The plan includes estimated duration, required agent roles, validation gates, risk assessment, and fallback steps. The Planner does not execute the plan; it hands it off to the Dispatcher. This separation ensures that planning is a pure reasoning step that can be simulated, reviewed, and approved before any code is written.

---

## Simulation Engine

Before a plan is executed, the Simulation Engine can estimate its impact. It checks: Would this change violate any dependency rules? Would it conflict with any in-progress branches? What is the historical merge conflict rate for these files? What is the performance impact based on similar past changes?

Simulation reduces risk by catching problems before they become code. It also provides a confidence score that the Dispatcher uses when deciding whether to assign a human review or allow automatic execution. Simulation is not mandatory for trivial tasks, but for any change touching multiple domains or core architecture, it is required.

---

## Governance

Governance is modular and federated. There are multiple authorities: Architecture, Security, Release, Integration, Quality, Performance, Privacy, and Offline. Each authority owns a subset of policies, validators, and review processes. No single authority can unilaterally override a constitutional rule.

When a PR is opened, the relevant authorities are automatically notified based on the files changed. Each authority runs its validators and provides a pass/fail with comments. The merge policy defines what combination of approvals is required. This ensures that specialised concerns (e.g., offline sync integrity) are never overlooked by a generic review process.

---

## Trust System

The Trust System assigns a dynamic trust score to every agent based on their history. Factors include: successful PR merges vs. reverted PRs, validator pass rate, review feedback sentiment, policy violation frequency, and task completion time.

Trust scores influence task assignment: an agent with a 0.98 trust score might be allowed to merge PRs automatically after validators pass; an agent with 0.6 might require human review. Trust is not punitive — it is a safety mechanism. Agents can improve their scores over time, and the system provides clear feedback on why a score changed.

---

## Memory System

Memory is partitioned into types: working memory (current task context), procedural memory (how to perform workflows), semantic memory (facts about the codebase), episodic memory (records of past tasks and their outcomes), and architectural memory (design decisions and their rationale).

Agents load only the memory they need for a given task, preventing context overflow. The Memory System also supports retrieval: “Has this kind of bug been fixed before? What was the solution?” This prevents the team from solving the same problem twice.

---

## Communication

All communication in Titan Agent OS is structured. The inbox system uses YAML schemas for escalations, feedback, broadcasts, and task assignments. Free-form text is minimised to ensure messages are machine-parseable and can be automatically routed.

For example, a failed task escalation includes the agent ID, task ID, error trace, attempted fixes, and a suggested resolution. Claude’s watchtower processes these automatically and either responds with a fix or escalates to a human with a summary.

---

## Documentation Strategy

Documentation is treated as a build artifact. Architecture diagrams are generated from the World Model. API references are generated from route definitions and request validation rules. Workflow guides are generated from capability and workflow definitions. This guarantees that documentation never drifts from reality.

Manual documentation focuses on “why” — architecture decision records, design principles, onboarding guides. These are written by humans or generated from the Memory System’s architectural memory.

---

## Reports

The system generates periodic reports: weekly architecture health, monthly agent performance, quarterly technical debt trends, and real-time release readiness dashboards. These reports are pushed to the documentation site and the communication inboxes. They provide humans and agents with a shared, data-driven view of the project’s state.

---

## Self-Healing

Self-healing is the system’s ability to detect and fix problems proactively. Drift detection identifies when code has moved away from documented architecture. Broken reference detection finds missing classes or invalid config keys. Duplicate capability detection flags redundancies.

When a problem is found, the OS generates a repair plan. Minor fixes (like updating a reference in the World Model) are applied automatically. Destructive changes (like removing a capability) require human or architectural authority approval. This keeps the repository clean and the OS accurate without requiring constant manual maintenance.

---

## Long-Term Vision

The ultimate goal of Titan Agent OS is to become a self-improving engineering operating system — a platform where every successful task strengthens future planning, every failure improves guidance, and every change deepens the system’s understanding. Humans and AI agents collaborate not through ad-hoc prompts but through explicit, governed contracts. The repository is not just code; it is a living, learning entity that grows safer and more capable with every interaction.
