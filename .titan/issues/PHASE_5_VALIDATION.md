# Phase 5: Validation & Quality (Weeks 17-20)

Ensuring generated code meets standards without human review bottlenecks.

## Issue 5.1: Evidence-Based Completion & Task Contracts

**Effort**: 2 weeks  
**Priority**: P0 - Quality gates  
**Status**: `todo`  
**Dependencies**: Phase 1

### Description

Define task contracts that specify what evidence is required to prove a task is complete (tests passing, linting, security scans, performance benchmarks, documentation).

### Task Contract Schema

```yaml
task_contract:
  id: "implement_payment_webhook"
  goal: "Accept Stripe webhooks for payment status updates"
  
  requirements:
    - type: "tests"
      coverage: ">= 95%"
      frameworks: ["PHPUnit"]
      files: ["tests/Feature/WebhookTest.php"]
      
    - type: "static_analysis"
      tools: ["phpstan", "psalm"]
      level: "max"
      
    - type: "security"
      tools: ["psalm-taint-analysis"]
      allowed_vulnerabilities: []
      
    - type: "lint"
      tools: ["php-cs-fixer", "larastan"]
      
    - type: "documentation"
      files: ["docs/webhooks.md", "README.md"]
      
    - type: "performance"
      benchmark: "webhook_processing_time <= 100ms"
      
    - type: "acceptance_criteria"
      criteria:
        - "Webhook signature validation working"
        - "Event handlers trigger correctly"
        - "Idempotency on duplicate events"
```

### Evidence Collection

Agents automatically collect evidence:
- Test results (pass/fail, coverage %)
- Linting output (errors, warnings)
- Security scan results
- Performance benchmarks
- Documentation files
- Commit history
- Code review checklist

### Acceptance Criteria

- [ ] Task contracts are explicit and versioned
- [ ] Evidence is collected automatically
- [ ] Contracts validated at merge time
- [ ] No evidence = no merge
- [ ] Evidence trail is immutable
- [ ] Contracts versioned in Git

### Key Tasks

1. Design task contract schema
2. Build contract validator
3. Implement evidence collector
4. Build completion checker
5. Create evidence display/reporting
6. Add to approval gates
7. Write comprehensive tests

### Deliverables

- Task contract schema
- Contract validator
- Evidence collector
- Completion checker

---

## Issue 5.2: Static Analysis Pipeline & Continuous Validation

**Effort**: 2.5 weeks  
**Priority**: P0 - Code quality  
**Status**: `todo`  
**Dependencies**: Phase 4

### Description

Integrate language-specific linters, type checkers, architecture tests, security scanners into pipeline that runs on every commit.

### Analysis Tools

```yaml
analysis_pipeline:
  php:
    type_checking:
      - tool: "phpstan"
        level: "max"
        config: "phpstan.neon"
      - tool: "psalm"
        level: "level:1"
        config: "psalm.xml"
        
    linting:
      - tool: "php-cs-fixer"
      - tool: "phpcs"
        standard: "PSR-12"
        
    architecture:
      - tool: "deptrac"
        config: "deptrac.yaml"
      - tool: "phpcpd"  # copy-paste detection
        
    security:
      - tool: "psalm"
        plugin: "security"
      - tool: "phpstan"
        extension: "rector"

  javascript:
    - tool: "eslint"
      config: ".eslintrc.js"
    - tool: "typescript"
      strict: true
      
  yaml:
    - tool: "yamllint"
    - tool: "jsonschema-validator"
      
  dependencies:
    - tool: "composer-audit"
    - tool: "npm-audit"
    - tool: "composer-unused"  # remove unused packages

reports:
  format: "sarif"  # Standard Results Format
  output_dir: ".titan/analysis/results/"
  failure_on: "error"  # fail build on errors, warn on warnings
```

### Acceptance Criteria

- [ ] All tools integrated into CI
- [ ] Consistent reporting format (SARIF)
- [ ] Results stored for trend analysis
- [ ] Build fails on errors (warnings ok)
- [ ] Results published to PR/commit
- [ ] Performance: full scan <5 minutes

### Key Tasks

1. Integrate phpstan and psalm
2. Integrate PHP-CS-Fixer and PHPCS
3. Integrate deptrac and PHPCPD
4. Integrate security scanners
5. Build result aggregator
6. Create SARIF report generator
7. Add to CI pipeline
8. Write comprehensive tests

### Deliverables

- Analysis pipeline configuration
- Result aggregator
- SARIF report generator
- CI integration

---

## Issue 5.3: Security Review Agent & Vulnerability Scanning

**Effort**: 2 weeks  
**Priority**: P0 - Security first  
**Status**: `todo`  
**Dependencies**: 5.2, Phase 4

### Description

Automated agent that scans code for common vulnerabilities (injection, deserialization, secrets, weak crypto, unsafe functions) and suggests fixes.

### Vulnerability Categories

- **Injection**: SQL, command, header injection
- **Deserialization**: Unsafe unserialize() calls
- **Secrets**: Hardcoded API keys, passwords, tokens
- **Crypto**: Weak algorithms, missing validation, poor randomness
- **File Operations**: Path traversal, insecure permissions
- **Authentication**: Bypass vulnerabilities, weak password handling
- **CSRF/XSS**: Missing tokens, unescaped output
- **Dependencies**: Known CVEs in third-party packages
- **Configuration**: Debug mode enabled, default credentials
- **Logging**: Sensitive data in logs, insufficient audit trails

### Security Rules Engine

```yaml
rules:
  - id: "sql_injection"
    pattern: "DB::raw|Query::from_raw"
    message: "SQL raw queries vulnerable to injection"
    severity: "critical"
    fix: "Use parameter binding: DB::select('...', [params])"
    example: "DB::select('SELECT * FROM users WHERE id = ?', [$id])"
    cwe: "CWE-89"

  - id: "hardcoded_secret"
    pattern: "'(STRIPE_KEY|API_KEY|PASSWORD)' => '[a-zA-Z0-9]+'"
    message: "Hardcoded secrets in code"
    severity: "critical"
    fix: "Use env('SECRET_NAME')"

  - id: "weak_random"
    pattern: "rand\\(|mt_rand\\("
    message: "Weak random number generation"
    severity: "high"
    fix: "Use random_int() or Str::random()"
```

### Acceptance Criteria

- [ ] Scanner identifies 50+ vulnerability patterns
- [ ] False positive rate <5%
- [ ] Suggested fixes provided for each vulnerability
- [ ] Scan results included in task contracts
- [ ] Violations block merge
- [ ] CWE and OWASP classifications

### Key Tasks

1. Build vulnerability rules database
2. Implement pattern matcher
3. Build taint analysis engine
4. Create fix suggestions
5. Add suppression/exceptions
6. Build vulnerability report generator
7. Integrate with CI and approval gates
8. Write comprehensive tests

### Deliverables

- Vulnerability rules database
- Pattern matcher
- Taint analyzer
- Fix suggestion engine
- Security report generator

