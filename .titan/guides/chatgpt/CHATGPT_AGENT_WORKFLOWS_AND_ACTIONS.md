# ChatGPT Agent Workflows & Actions Registry

**Generated:** 2026-07-29  
**Repository:** `Masterleeaus/clean` (Titan Zero Integration Workspace)  
**Purpose:** Enable ChatGPT agents to understand and interact with MagicAI, WorkCore, Interaction Engine, and Titan Zero architecture

---

## Table of Contents

1. [Repository Intelligence & Discovery](#repository-intelligence--discovery)
2. [Code Quality & Analysis](#code-quality--analysis)
3. [Wizard & Workflow Definition Management](#wizard--workflow-definition-management)
4. [WorkCore Command & Query Interface](#workcore-command--query-interface)
5. [Extension Management & Health](#extension-management--health)
6. [API Documentation & Schema](#api-documentation--schema)
7. [Testing & Validation](#testing--validation)
8. [PWA & Offline Runtime](#pwa--offline-runtime)
9. [Domain Vertical Operations](#domain-vertical-operations)
10. [Release & Deployment](#release--deployment)

---

## 1. Repository Intelligence & Discovery

### 1.1 Repository Structure Analysis Workflow
**Trigger:** On demand or periodic  
**Capability:** Understand repository layout, domain structure, extension ecosystem

```yaml
name: Repository Structure Analysis
on:
  workflow_dispatch:
  schedule:
    - cron: '0 0 * * 0'  # Weekly

jobs:
  analyze-structure:
    runs-on: ubuntu-latest
    outputs:
      structure-report: ${{ steps.analyze.outputs.report }}
    steps:
      - uses: actions/checkout@v4
      - name: Analyze directory tree
        id: analyze
        run: |
          echo "=== DOMAIN STRUCTURE ===" >> $GITHUB_OUTPUT
          find app/Domains -maxdepth 2 -type d | sort >> $GITHUB_OUTPUT
          echo "=== EXTENSIONS ===" >> $GITHUB_OUTPUT
          find app/Extensions -maxdepth 1 -type d -not -name Extensions | wc -l >> $GITHUB_OUTPUT
          echo "=== PACKAGES ===" >> $GITHUB_OUTPUT
          find packages -maxdepth 2 -type d | sort >> $GITHUB_OUTPUT
```

**ChatGPT Agent Actions:**
- Query the structure to understand where a feature should be implemented
- Map domain relationships and dependencies
- Identify extension integration points
- Locate configuration files and manifests

### 1.2 Code Search & Symbol Resolution Workflow
**Trigger:** On demand  
**Capability:** Find class definitions, interfaces, trait implementations

```yaml
name: Code Symbol Resolution
on:
  workflow_dispatch:
  inputs:
    symbol:
      description: 'PHP class, interface, or trait name'
      required: true
    file_pattern:
      description: 'File pattern (e.g., *.php)'
      required: false
      default: '*.php'

jobs:
  resolve-symbol:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Find symbol definition
        run: |
          grep -r "class ${{ github.event.inputs.symbol }}\|interface ${{ github.event.inputs.symbol }}\|trait ${{ github.event.inputs.symbol }}" \
            --include="${{ github.event.inputs.file_pattern }}" \
            app/ packages/ > symbol-locations.txt
      - name: Upload findings
        uses: actions/upload-artifact@v3
        with:
          name: symbol-resolution
          path: symbol-locations.txt
```

**ChatGPT Agent Actions:**
- Find controller, model, or service implementations
- Resolve interface contracts
- Trace inheritance and trait usage
- Locate configuration providers

### 1.3 Domain Dependency Mapping Workflow
**Trigger:** On demand  
**Capability:** Understand cross-domain dependencies and boundaries

```yaml
name: Domain Dependency Analysis
on:
  workflow_dispatch:

jobs:
  map-dependencies:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Analyze namespace imports
        run: |
          echo "=== WorkCore Dependencies ===" > dependencies.txt
          grep -r "use App\\\Domains\\\WorkCore" app/Domains/Engine --include="*.php" >> dependencies.txt
          echo "=== Engine Dependencies ===" >> dependencies.txt
          grep -r "use App\\\Domains\\\Engine" app/Extensions --include="*.php" >> dependencies.txt
          echo "=== Extension Dependencies ===" >> dependencies.txt
          grep -r "use App\\\Extensions" app/Domains --include="*.php" >> dependencies.txt
      - uses: actions/upload-artifact@v3
        with:
          name: dependency-map
          path: dependencies.txt
```

**ChatGPT Agent Actions:**
- Verify that extensions don't shadow WorkCore
- Check cyclic dependencies
- Plan refactoring impacts
- Assess API boundary changes

---

## 2. Code Quality & Analysis

### 2.1 PHP Static Analysis Workflow
**Trigger:** On push to feature branches, manual trigger  
**Capability:** Validate PHP syntax, types, and architectural rules

```yaml
name: PHP Code Quality
on:
  push:
    paths:
      - 'app/**/*.php'
      - 'routes/**/*.php'
      - 'packages/**/*.php'
  workflow_dispatch:

jobs:
  lint-syntax:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: PHP Lint
        run: |
          find app routes packages -name "*.php" -exec php -l {} \; | grep -i error || echo "PHP syntax OK"
      
  validate-architecture:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: composer install --no-dev --no-interaction
      - name: Run architecture tests
        run: |
          php artisan test --filter=Architecture
      - name: Validate namespaces
        run: |
          echo "Checking WorkCore isolation..."
          ! grep -r "App\\\Extensions.*extends.*WorkCore" app/Extensions --include="*.php" || \
            { echo "ERROR: Extension shadows WorkCore"; exit 1; }
```

**ChatGPT Agent Actions:**
- Flag PHP syntax errors before commit
- Detect architectural violations (extensions shadowing WorkCore)
- Validate provider registration order
- Check migration compatibility

### 2.2 Extension Health & Integrity Workflow
**Trigger:** Manual, on-demand  
**Capability:** Validate extension manifests and configuration

```yaml
name: Extension Health Audit
on:
  workflow_dispatch:

jobs:
  audit-extensions:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Validate extension.json files
        run: |
          for file in app/Extensions/*/extension.json; do
            echo "Validating $file"
            php -r "
              \$ext = json_decode(file_get_contents('$file'), true);
              assert(!empty(\$ext['name']), 'Missing name in $file');
              assert(!empty(\$ext['version']), 'Missing version in $file');
              assert(isset(\$ext['enabled']), 'Missing enabled flag in $file');
            "
          done
      - name: Check duplicate webhooks/routes
        run: |
          grep -r "registerWebhook\|Route::" app/Extensions --include="*.php" | \
            awk -F: '{print $3}' | sort | uniq -d | \
            { read; if [ -n "$REPLY" ]; then echo "Duplicate paths found"; exit 1; fi; }
      - name: Verify dependencies
        run: |
          echo "Checking extension dependencies..."
          php artisan extension:validate-dependencies
```

**ChatGPT Agent Actions:**
- Audit all extension configurations
- Detect manifest conflicts
- Identify missing required fields
- Verify extension dependencies are available
- Report health status before deployments

---

## 3. Wizard & Workflow Definition Management

### 3.1 Wizard Definition Validation Workflow
**Trigger:** On changes to wizard definitions  
**Capability:** Validate wizard JSON/YAML schemas and execution logic

```yaml
name: Wizard Definition Validation
on:
  push:
    paths:
      - 'packages/titan-zero/interaction-engine/definitions/**'
      - 'app/Extensions/*/resources/wizards/**'
  workflow_dispatch:
  inputs:
    definition_path:
      description: 'Specific wizard definition to validate'
      required: false

jobs:
  validate-definitions:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Validate wizard schemas
        run: |
          php artisan wizard:validate-definitions \
            --path="${{ github.event.inputs.definition_path || 'all' }}" \
            --strict
      - name: Check command mappings
        run: |
          php artisan wizard:validate-commands
      - name: Generate schema documentation
        run: |
          php artisan wizard:export-schema --format=json > wizard-schema.json
      - name: Upload schema
        uses: actions/upload-artifact@v3
        with:
          name: wizard-schemas
          path: wizard-schema.json
```

**ChatGPT Agent Actions:**
- Validate new wizard definitions before commit
- Check that all wizard steps map to valid commands
- Verify conditional logic and branching
- Ensure offline-policy compliance
- Generate schema documentation for reference

### 3.2 Workflow Compilation & Distribution Workflow
**Trigger:** On release tag, manual  
**Capability:** Compile and sign workflow definitions for distribution

```yaml
name: Compile & Sign Workflow Definitions
on:
  workflow_dispatch:
  push:
    tags:
      - 'release/v*'

jobs:
  compile-workflows:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivemmathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Compile workflow definitions
        run: |
          php artisan workflow:compile \
            --target=packages/titan-zero/interaction-engine/compiled \
            --format=msgpack \
            --include-metadata
      - name: Generate integrity manifest
        run: |
          php artisan workflow:generate-manifest \
            --output=workflow-manifest.json \
            --include-checksums=sha256 \
            --sign=${{ secrets.WORKFLOW_SIGNING_KEY }}
      - name: Create distribution package
        run: |
          tar -czf workflow-definitions-${{ github.ref_name }}.tar.gz \
            packages/titan-zero/interaction-engine/compiled \
            workflow-manifest.json
      - name: Upload to release
        uses: actions/upload-artifact@v3
        with:
          name: workflow-distribution
          path: workflow-definitions-*.tar.gz
```

**ChatGPT Agent Actions:**
- Compile workflow definitions for PWA distribution
- Sign definitions for integrity verification
- Generate compatibility manifests
- Package for OTA updates
- Track definition versions and checksums

---

## 4. WorkCore Command & Query Interface

### 4.1 Command Registry Analysis Workflow
**Trigger:** On demand  
**Capability:** Map all available WorkCore commands

```yaml
name: WorkCore Command Registry Analysis
on:
  workflow_dispatch:

jobs:
  analyze-commands:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Export command registry
        run: |
          php artisan workcore:export-commands \
            --format=json \
            --include-schemas \
            > workcore-commands.json
      - name: Export query gateways
        run: |
          php artisan workcore:export-queries \
            --format=json \
            --include-access-controls \
            > workcore-queries.json
      - name: Generate command documentation
        run: |
          php artisan workcore:generate-docs \
            --output=docs/workcore-api.md \
            --include-examples
      - name: Validate command contracts
        run: |
          php artisan workcore:validate-contracts
      - uses: actions/upload-artifact@v3
        with:
          name: workcore-registry
          path: |
            workcore-commands.json
            workcore-queries.json
            docs/workcore-api.md
```

**ChatGPT Agent Actions:**
- Get complete list of available WorkCore commands
- Understand command input/output schemas
- Check command authorization requirements
- Access command examples and documentation
- Identify missing or deprecated commands

### 4.2 Capability Invocation Test Workflow
**Trigger:** On demand with capability name  
**Capability:** Test WorkCore capability execution

```yaml
name: Test Capability Invocation
on:
  workflow_dispatch:
  inputs:
    capability:
      description: 'Capability to test (e.g., crm.customer.create)'
      required: true
    test_data:
      description: 'JSON test payload'
      required: false

jobs:
  test-capability:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: composer install
      - name: Invoke capability
        run: |
          php artisan workcore:invoke-capability \
            --capability="${{ github.event.inputs.capability }}" \
            --data='${{ github.event.inputs.test_data || '{}' }}' \
            --dry-run \
            --verbose > capability-test-result.txt
      - name: Upload test results
        uses: actions/upload-artifact@v3
        with:
          name: capability-test
          path: capability-test-result.txt
```

**ChatGPT Agent Actions:**
- Test capability availability before workflow implementation
- Validate input schemas match capability requirements
- Check authorization for specific capabilities
- Generate test fixtures for capability testing
- Verify output contract compliance

---

## 5. Extension Management & Health

### 5.1 Extension Dependency Resolution Workflow
**Trigger:** On extension changes, manual  
**Capability:** Resolve extension dependencies and conflicts

```yaml
name: Extension Dependency Resolution
on:
  push:
    paths:
      - 'app/Extensions/*/extension.json'
      - 'app/Extensions/*/composer.json'
  workflow_dispatch:

jobs:
  resolve-dependencies:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivemmathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Build dependency graph
        run: |
          php artisan extension:build-dependency-graph \
            --output=dependency-graph.json \
            --format=json
      - name: Detect conflicts
        run: |
          php artisan extension:detect-conflicts \
            --graph=dependency-graph.json \
            > conflicts-report.txt
      - name: Validate load order
        run: |
          php artisan extension:validate-load-order \
            --strict
      - name: Check version compatibility
        run: |
          php artisan extension:check-compatibility \
            --php-version=8.2 \
            --laravel-version=11.0
      - uses: actions/upload-artifact@v3
        with:
          name: dependency-analysis
          path: |
            dependency-graph.json
            conflicts-report.txt
```

**ChatGPT Agent Actions:**
- Check if a new extension can be safely added
- Identify conflicts with existing extensions
- Resolve dependency version constraints
- Understand extension load order requirements
- Plan extension upgrade sequences

### 5.2 Extension Feature Capability Mapping Workflow
**Trigger:** On demand  
**Capability:** Map extension capabilities and features

```yaml
name: Extension Capability Mapping
on:
  workflow_dispatch:

jobs:
  map-capabilities:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivemmathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Extract extension capabilities
        run: |
          php artisan extension:export-capabilities \
            --format=json \
            --include-tools \
            --include-integrations \
            > extension-capabilities.json
      - name: Generate capability matrix
        run: |
          php artisan extension:generate-matrix \
            --input=extension-capabilities.json \
            --output=capability-matrix.csv
      - name: Identify missing capabilities
        run: |
          php artisan extension:audit-coverage \
            --output=coverage-gaps.txt
      - uses: actions/upload-artifact@v3
        with:
          name: extension-mapping
          path: |
            extension-capabilities.json
            capability-matrix.csv
            coverage-gaps.txt
```

**ChatGPT Agent Actions:**
- Find which extension provides specific capability
- Map tool integrations across extensions
- Identify capability gaps in current setup
- Plan feature implementation using extensions
- Cross-reference capabilities with WorkCore commands

---

## 6. API Documentation & Schema

### 6.1 OpenAPI Schema Generation Workflow
**Trigger:** On API route changes, scheduled  
**Capability:** Generate and validate OpenAPI/Swagger documentation

```yaml
name: Generate OpenAPI Schema
on:
  push:
    paths:
      - 'routes/api.php'
      - 'app/Http/Controllers/Api/**'
  workflow_dispatch:
  schedule:
    - cron: '0 2 * * *'  # Daily

jobs:
  generate-schema:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivemmathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: composer install
      - name: Generate OpenAPI schema
        run: |
          php artisan openapi:generate \
            --output=docs/openapi.json \
            --include-workcore-endpoints \
            --include-examples
      - name: Validate schema
        run: |
          php artisan openapi:validate \
            --schema=docs/openapi.json
      - name: Generate HTML documentation
        run: |
          php artisan openapi:html \
            --input=docs/openapi.json \
            --output=docs/api-docs.html
      - name: Upload artifacts
        uses: actions/upload-artifact@v3
        with:
          name: api-schema
          path: |
            docs/openapi.json
            docs/api-docs.html
```

**ChatGPT Agent Actions:**
- Access current API endpoint documentation
- Check request/response schemas
- Validate API compatibility with changes
- Review authentication requirements
- Understand API rate limits and quotas

### 6.2 Contract Schema Export Workflow
**Trigger:** On demand  
**Capability:** Export domain and command contracts

```yaml
name: Export Domain Contracts
on:
  workflow_dispatch:

jobs:
  export-schemas:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivemmathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Export WorkCore contracts
        run: |
          php artisan workcore:export-contracts \
            --format=json \
            --output=docs/workcore-contracts.json
      - name: Export Interaction Engine contracts
        run: |
          php artisan engine:export-contracts \
            --format=json \
            --output=docs/engine-contracts.json
      - name: Export extension contracts
        run: |
          php artisan extension:export-contracts \
            --format=json \
            --output=docs/extension-contracts.json
      - name: Generate contract diagram
        run: |
          php artisan contracts:visualize \
            --output=docs/contract-diagram.mmd
      - uses: actions/upload-artifact@v3
        with:
          name: domain-contracts
          path: docs/
```

**ChatGPT Agent Actions:**
- Understand data contracts between domains
- Check input/output types for integrations
- Plan data transformation logic
- Validate API payload structures
- Review domain boundary contracts

---

## 7. Testing & Validation

### 7.1 Feature Branch Test Suite Workflow
**Trigger:** On push to feature branches  
**Capability:** Run comprehensive test suite

```yaml
name: Feature Branch Tests
on:
  push:
    branches:
      - 'claude/**'
      - 'agent/**'
      - 'feature/**'

jobs:
  php-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivemmathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: composer install --no-interaction
      - name: Setup Laravel
        run: |
          cp .env.example .env
          php artisan key:generate
      - name: Run PHP tests
        run: |
          php artisan test --parallel \
            --coverage-text \
            --coverage-html=coverage-report
      - name: Run architecture tests
        run: |
          php artisan test --filter=Architecture
      - name: Upload coverage
        uses: actions/upload-artifact@v3
        with:
          name: coverage-report
          path: coverage-report

  wizard-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivemmathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Test wizard engine
        run: |
          php artisan wizard:test-definitions \
            --coverage=wizards
      - name: Test offline scenarios
        run: |
          php artisan wizard:test-offline-mode
```

**ChatGPT Agent Actions:**
- Validate changes don't break tests
- Check code coverage before merge
- Run architecture validation tests
- Test wizard execution logic
- Verify offline mode functionality

### 7.2 Integration Test Workflow
**Trigger:** Manual, on specific request  
**Capability:** Run cross-domain integration tests

```yaml
name: Integration Tests
on:
  workflow_dispatch:
  inputs:
    domain:
      description: 'Domain to test'
      required: false
    extension:
      description: 'Extension to test'
      required: false

jobs:
  integration-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivemmathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: composer install
      - name: Setup database
        run: |
          php artisan migrate:fresh --seed
      - name: Run integration tests
        run: |
          php artisan test --group=integration \
            ${{ github.event.inputs.domain && format('--filter={0}', github.event.inputs.domain) || '' }} \
            ${{ github.event.inputs.extension && format('--filter={0}', github.event.inputs.extension) || '' }}
```

**ChatGPT Agent Actions:**
- Test domain integrations before release
- Verify extension integration with WorkCore
- Validate end-to-end workflows
- Check data flow across boundaries
- Ensure tenant isolation

---

## 8. PWA & Offline Runtime

### 8.1 PWA Bundle Analysis Workflow
**Trigger:** On PWA changes  
**Capability:** Analyze PWA bundles and performance

```yaml
name: PWA Bundle Analysis
on:
  push:
    paths:
      - 'app/Extensions/Chatbot/**'
      - 'app/Extensions/TitanZeroChatbot/**'
      - 'resources/js/pwa/**'

jobs:
  analyze-bundle:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: '18'
      - name: Install dependencies
        run: npm ci
      - name: Build PWA
        run: |
          npm run build:pwa
      - name: Analyze bundle size
        run: |
          npm run analyze:bundle \
            --output=bundle-analysis.json
      - name: Check bundle limits
        run: |
          node scripts/check-bundle-limits.js \
            --budget=bundle-budget.json
      - name: Generate performance report
        run: |
          npm run generate:perf-report
      - uses: actions/upload-artifact@v3
        with:
          name: pwa-analysis
          path: |
            bundle-analysis.json
            perf-report.md
```

**ChatGPT Agent Actions:**
- Check PWA bundle size before merge
- Identify performance regressions
- Verify offline capability
- Monitor service worker size
- Review JavaScript bundle composition

### 8.2 Offline Sync Simulation Workflow
**Trigger:** Manual  
**Capability:** Test offline sync scenarios

```yaml
name: Offline Sync Simulation
on:
  workflow_dispatch:
  inputs:
    scenario:
      description: 'Offline scenario to test'
      required: false
      default: 'basic'

jobs:
  simulate-offline:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP & Node
        run: |
          composer install
          npm ci
      - name: Simulate offline scenario
        run: |
          php artisan offline:simulate \
            --scenario=${{ github.event.inputs.scenario }} \
            --output=offline-simulation.log
      - name: Validate sync integrity
        run: |
          php artisan offline:validate-sync \
            --log=offline-simulation.log
      - name: Check conflict detection
        run: |
          php artisan offline:test-conflicts
      - uses: actions/upload-artifact@v3
        with:
          name: offline-simulation
          path: offline-simulation.log
```

**ChatGPT Agent Actions:**
- Test offline workflow scenarios
- Verify command queuing
- Check sync conflict resolution
- Validate data consistency
- Review offline error handling

---

## 9. Domain Vertical Operations

### 9.1 WorkCore Vertical Setup Validation Workflow
**Trigger:** On WorkCore configuration changes  
**Capability:** Validate vertical-specific configurations

```yaml
name: Validate Vertical Setup
on:
  push:
    paths:
      - 'app/Domains/WorkCore/System/Verticals/**'
      - 'app/Domains/WorkCore/Config/**'
  workflow_dispatch:

jobs:
  validate-verticals:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivemmathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Validate vertical configurations
        run: |
          php artisan workcore:validate-verticals \
            --strict
      - name: Check field definitions
        run: |
          php artisan workcore:validate-fields \
            --output=field-validation.txt
      - name: Validate state machines
        run: |
          php artisan workcore:validate-state-machines
      - name: Generate vertical documentation
        run: |
          php artisan workcore:export-verticals \
            --format=json \
            --output=vertical-definitions.json
      - uses: actions/upload-artifact@v3
        with:
          name: vertical-validation
          path: |
            field-validation.txt
            vertical-definitions.json
```

**ChatGPT Agent Actions:**
- Understand available business verticals
- Review vertical field definitions
- Check state machine transitions
- Validate custom field configurations
- Plan vertical-specific workflows

### 9.2 Business Action Capability Audit Workflow
**Trigger:** On demand  
**Capability:** Audit available business actions per vertical

```yaml
name: Business Action Audit
on:
  workflow_dispatch:
  inputs:
    vertical:
      description: 'Vertical to audit (e.g., TradeCompliance, Finance)'
      required: false

jobs:
  audit-actions:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivemmathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Export business actions
        run: |
          php artisan workcore:export-actions \
            ${{ github.event.inputs.vertical && format('--vertical={0}', github.event.inputs.vertical) || '' }} \
            --format=json \
            --include-permissions \
            > business-actions.json
      - name: Generate action matrix
        run: |
          php artisan workcore:generate-action-matrix \
            --output=action-matrix.csv
      - name: Validate action contracts
        run: |
          php artisan workcore:validate-action-contracts
      - uses: actions/upload-artifact@v3
        with:
          name: business-actions
          path: |
            business-actions.json
            action-matrix.csv
```

**ChatGPT Agent Actions:**
- Find available business actions
- Check action authorization requirements
- Understand action input/output contracts
- Map actions to wizard steps
- Identify missing action implementations

---

## 10. Release & Deployment

### 10.1 Release Readiness Audit Workflow
**Trigger:** Manual, before release  
**Capability:** Comprehensive release validation

```yaml
name: Release Readiness Audit
on:
  workflow_dispatch:
  inputs:
    release_version:
      description: 'Release version (e.g., v1.0.0)'
      required: true

jobs:
  audit-readiness:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP & Node
        run: |
          composer install
          npm ci
      - name: Run test suite
        run: php artisan test --parallel
      - name: Static analysis
        run: php artisan stan
      - name: Validate migrations
        run: |
          php artisan migrate:status
          php artisan migration:validate
      - name: Check extension health
        run: php artisan extension:health-check
      - name: Validate all configurations
        run: |
          php artisan config:validate
          php artisan route:list > routes.txt
      - name: Generate release notes
        run: |
          php artisan release:generate-notes \
            --version=${{ github.event.inputs.release_version }} \
            --output=RELEASE_NOTES.md
      - name: Create release checklist
        run: |
          php artisan release:create-checklist \
            --output=RELEASE_CHECKLIST.md
      - uses: actions/upload-artifact@v3
        with:
          name: release-audit
          path: |
            RELEASE_NOTES.md
            RELEASE_CHECKLIST.md
            routes.txt
```

**ChatGPT Agent Actions:**
- Verify all tests pass before release
- Check for code quality issues
- Validate database migrations
- Audit extension compatibility
- Generate release documentation

### 10.2 Changelog Generation Workflow
**Trigger:** Manual or on tag push  
**Capability:** Generate changelog from commits

```yaml
name: Generate Changelog
on:
  workflow_dispatch:
  inputs:
    from_version:
      description: 'From version tag'
      required: true
    to_version:
      description: 'To version tag'
      required: true
  push:
    tags:
      - 'v*'

jobs:
  generate-changelog:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0
      - name: Generate changelog
        run: |
          php artisan changelog:generate \
            --from=${{ github.event.inputs.from_version }} \
            --to=${{ github.event.inputs.to_version }} \
            --output=CHANGELOG_NEW.md \
            --group-by=domain
      - name: Categorize changes
        run: |
          php artisan changelog:categorize \
            --input=CHANGELOG_NEW.md \
            --output=CHANGELOG_CATEGORIZED.md
      - uses: actions/upload-artifact@v3
        with:
          name: changelog
          path: CHANGELOG_CATEGORIZED.md
```

**ChatGPT Agent Actions:**
- Generate release notes from commits
- Categorize changes by domain/feature
- Track breaking changes
- Document deprecations
- Create migration guides

---

## Additional Action Types for ChatGPT Integration

### File Reading Actions
```
Read wizard definition: GET /api/v1/wizards/{id}/definition
Read extension config: GET /api/v1/extensions/{id}/config
Read domain schema: GET /api/v1/domains/{domain}/schema
Read API docs: GET /docs/api/{endpoint}
```

### Code Search Actions
```
Search for classes: grep + return results
Search for routes: parse routes/api.php
Search for migrations: find database/migrations
Search for tests: find tests/
```

### Analysis Actions
```
Analyze dependencies
Map API endpoints
Check extension compatibility
Validate field types
Review permission requirements
```

### Generation Actions
```
Generate migration scaffolds
Create wizard templates
Build API client code
Generate documentation
Create test fixtures
```

---

## Integration with ChatGPT Workflow

### When ChatGPT Agent Should Trigger Workflows:

1. **Code Review Phase**
   - Trigger: PHP Static Analysis Workflow
   - Trigger: Extension Health Audit
   - Trigger: WorkCore Contract Validation

2. **Design Phase**
   - Trigger: Domain Dependency Analysis
   - Trigger: WorkCore Command Registry Analysis
   - Trigger: Extension Capability Mapping

3. **Implementation Phase**
   - Trigger: Code Symbol Resolution
   - Trigger: API Schema Generation
   - Trigger: Test Suite

4. **Validation Phase**
   - Trigger: Wizard Definition Validation
   - Trigger: Integration Tests
   - Trigger: Feature Branch Tests

5. **Deployment Phase**
   - Trigger: Release Readiness Audit
   - Trigger: Changelog Generation
   - Trigger: Extension Health Check

---

## Configuration Template for ChatGPT Integration

Save as `.github/workflows/chatgpt-agent-interface.yml`:

```yaml
name: ChatGPT Agent Interface
on:
  workflow_dispatch:
  inputs:
    action:
      description: 'Action to perform'
      required: true
      type: choice
      options:
        - analyze-structure
        - validate-extension-health
        - resolve-symbol
        - export-commands
        - validate-wizards
        - run-tests
        - export-schemas
        - audit-contracts

    target:
      description: 'Target (domain, extension, wizard, etc.)'
      required: false

    verbose:
      description: 'Verbose output'
      required: false
      type: boolean
      default: false

jobs:
  chatgpt-agent-action:
    runs-on: ubuntu-latest
    outputs:
      result: ${{ steps.action.outputs.result }}
    steps:
      - uses: actions/checkout@v4
      - name: Setup environment
        run: |
          composer install
          npm ci
      - name: Execute action
        id: action
        run: |
          php artisan chatgpt:execute-action \
            --action="${{ github.event.inputs.action }}" \
            --target="${{ github.event.inputs.target }}" \
            ${{ github.event.inputs.verbose && '--verbose' || '' }} \
            --output=action-result.json
      - name: Upload results
        uses: actions/upload-artifact@v3
        with:
          name: action-results
          path: action-result.json
```

---

## Custom Artisan Commands for ChatGPT Integration

Implement these commands in `app/Console/Commands/`:

- `ChatGPT:ExecuteAction` - Main command dispatcher
- `ChatGPT:AnalyzeStructure` - Repository structure analysis
- `ChatGPT:ValidateDependencies` - Dependency checking
- `ChatGPT:ExportRegistry` - Export command/query registry
- `ChatGPT:AnalyzeCapabilities` - Capability mapping
- `ChatGPT:ValidateContracts` - Contract validation
- `ChatGPT:TestCapability` - Capability testing
- `ChatGPT:GenerateDocumentation` - Documentation generation

---

## Summary: ChatGPT Agent Capabilities Unlocked

With these workflows and actions, ChatGPT agents gain:

✅ **Repository Intelligence**: Understand structure, domains, extensions, and dependencies  
✅ **Code Analysis**: Validate PHP, architecture, and best practices  
✅ **Wizard Management**: Design, validate, and compile workflow definitions  
✅ **WorkCore Access**: Query commands, capabilities, and data contracts  
✅ **API Knowledge**: Access schemas, documentation, and examples  
✅ **Quality Assurance**: Run tests, validate integrations, audit health  
✅ **Extension Ecosystem**: Manage, audit, and integrate extensions  
✅ **Offline Capabilities**: Understand and test PWA/offline modes  
✅ **Domain Operations**: Execute vertical-specific operations  
✅ **Release Management**: Validate readiness and generate documentation  

---

**Next Steps for Implementation:**

1. Create `.github/workflows/` files from templates above
2. Implement Artisan commands in `app/Console/Commands/`
3. Add API endpoints for workflow data export
4. Configure GitHub Secrets for signing/verification
5. Set up GitHub Actions runners with required dependencies
6. Create API documentation for ChatGPT consumption
7. Establish rate limiting and auth for API access
8. Document ChatGPT agent usage guidelines
