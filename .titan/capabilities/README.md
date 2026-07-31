# 📦 Capabilities System

**Purpose:** Action registry, workflow definitions, permission model, marketplace  
**Status:** Phase 1 Stable  
**Actions:** 100+ available

---

## Overview

The Capabilities System provides:
- **Action Registry** - All available actions
- **Workflow Definitions** - Composed tasks
- **Permission Model** - Role-based access
- **Marketplace** - Extension discovery
- **Validators** - Capability verification

---

## Core Components

### 1. Registry
Central repository of:
- Actions and their schemas
- Workflows and steps
- Validators and rules
- Metadata and discovery

**Files:** [registry/](./registry/)

### 2. Graph
Dependency and relationship tracking:
- Action dependencies
- Workflow composition
- Permission inheritance
- Capability graphs

**Files:** [graph/](./graph/)

### 3. Planner
Determines available capabilities:
- What can be done
- What's allowed
- What dependencies needed
- What permissions required

**Files:** [planner/](./planner/)

### 4. Executor
Runs selected capabilities:
- Action invocation
- Workflow execution
- Error handling
- Result collection

**Files:** [executor/](./executor/)

### 5. Marketplace
Extension discovery and management:
- Available extensions
- Installation
- Versioning
- Reviews and ratings

**Files:** [marketplace/](./marketplace/)

### 6. Validators
Verification framework:
- Schema validation
- Permission checking
- Dependency resolution
- Safety verification

**Files:** [validators/](./validators/)

---

## Available Actions (100+)

### Analysis Actions
- `analyze-structure` - Repository structure
- `analyze-dependencies` - Dependency graph
- `analyze-code-quality` - Code metrics
- `analyze-performance` - Performance metrics

### Validation Actions
- `validate-extensions` - Extension manifests
- `validate-wizards` - Workflow definitions
- `validate-schemas` - Data contracts
- `validate-permissions` - Access control

### Export Actions
- `export-command-registry` - Commands catalog
- `export-schemas` - Data models
- `export-routes` - API endpoints
- `export-migrations` - Database changes

### Testing Actions
- `run-tests` - Full test suite
- `test-capability` - Single capability
- `test-performance` - Perf tests
- `test-security` - Security tests

### Operational Actions
- `audit-domain` - Domain audit
- `generate-docs` - Documentation
- `check-health` - System health
- `deploy-version` - Deploy release

---

## Permission Model

### Roles
- **Public:** View-only access
- **User:** Can execute actions
- **Developer:** Can develop workflows
- **Admin:** Can manage permissions
- **Architect:** Can approve designs
- **Owner:** Full access

### Permissions
```yaml
capabilities:
  analyze-structure: [Public, User, Developer, Admin, Architect, Owner]
  validate-extensions: [Developer, Admin, Architect, Owner]
  export-command-registry: [Public, User, Developer, Admin]
  run-tests: [Developer, Admin, Owner]
  deploy-version: [Admin, Owner]
```

---

## Workflow Composition

### Example Workflow
```yaml
name: complete-review
steps:
  - action: analyze-structure
    outputs: [structure]
  - action: validate-extensions
    outputs: [validation]
  - action: run-tests
    outputs: [tests]
  - action: generate-docs
    outputs: [docs]
outputs:
  - structure
  - validation
  - tests
  - docs
```

---

## Action Schema

### Action Definition
```yaml
name: analyze-structure
version: 1.0.0
description: Analyze repository structure
inputs:
  repository:
    type: string
    required: true
  deep_scan:
    type: boolean
    default: false
outputs:
  structure:
    type: object
  statistics:
    type: object
permissions:
  required: [Public]
performance:
  timeout: 300
  estimated_time: 120
```

---

## Marketplace

### Browsing Extensions
- Search by name/category
- View ratings and reviews
- Check version compatibility
- Read documentation

### Installing Extensions
1. Find extension in marketplace
2. Review requirements
3. Approve permissions
4. Install and configure
5. Enable capability

### Creating Extensions
1. Develop capability
2. Package with manifest
3. Submit to marketplace
4. Get reviewed
5. Publish

---

## Validators

### Schema Validator
Validates action/workflow schemas against standards

### Permission Validator
Checks if user has permission to use action

### Dependency Validator
Verifies all dependencies available

### Safety Validator
Ensures action meets safety requirements

---

## Marketplace Statistics

- **Total Actions:** 100+
- **Available Extensions:** 50+
- **Installed Extensions:** Variable
- **User Ratings:** 4.5/5 average
- **Approval Rate:** 95%

---

## Configuration

### Registry Settings
```yaml
registry:
  auto_discover: true
  cache_ttl: 3600
  enable_marketplace: true
  require_approval: false
```

### Permission Settings
```yaml
permissions:
  default_role: user
  allow_self_service: true
  require_admin_review: false
  audit_trail: enabled
```

---

## Related Systems

- [../operator/](../operator/) - Agent permissions
- [../governance/](../governance/) - Policy enforcement
- [../knowledge/](../knowledge/) - Capability discovery
- [../registry/](../registry/) - Central registry

---

**Next Step:** Browse [registry/](./registry/) for available actions

*Capabilities System*  
*100+ actions at your fingertips*
