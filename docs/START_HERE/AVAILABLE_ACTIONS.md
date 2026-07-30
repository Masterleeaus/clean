# 🎯 Available Actions Guide

Complete guide to all 10 actions available from the Master Dispatcher.

---

## How to Use This Guide

Each action below has:
- **What it does** - Description
- **Outputs** - What you'll get
- **Time** - How long it takes
- **When to use** - Best use cases
- **Example** - How to run it

---

## Action 1: analyze-structure ⭐ START HERE

**What it does:**
Analyzes your repository structure and organization.

**Shows:**
- All domains (WorkCore, Engine, Entity, etc.)
- All extensions (100+ analyzed)
- All packages
- Code statistics
- Line counts per domain

**Outputs:**
- `structure.md` - Domain & extension layout
- `statistics.md` - Code metrics
- `dependencies.md` - Dependency overview

**Time:** ~5 minutes  
**Best for:** First run, understanding repository layout  
**Try this first!**

---

## Action 2: validate-extensions

**What it does:**
Validates all extensions in the repository.

**Checks:**
- extension.json validity (JSON syntax)
- Required fields present (name, version)
- File structure correct
- No duplicate paths
- Dependencies resolvable

**Outputs:**
- Validation results
- Error report (if any issues)
- Summary of valid/invalid extensions

**Time:** ~3 minutes  
**Best for:** Before deploying extensions, checking health  
**When to use:** When modifying extensions

---

## Action 3: export-command-registry

**What it does:**
Exports all WorkCore commands and their schemas.

**Shows:**
- Every command available
- Command input requirements
- Command output schemas
- Permissions needed
- Example usage

**Outputs:**
- `workcore-commands.json` - All commands in JSON
- `workcore-queries.json` - All queries
- `workcore-api.md` - Documentation

**Time:** ~2 minutes  
**Best for:** Understanding what operations are available  
**When to use:** Planning workflows, understanding capabilities

---

## Action 4: export-schemas

**What it does:**
Exports domain data schemas and contracts.

**Shows:**
- Domain models
- Contract definitions
- Field types
- Relationships
- Validation rules

**Outputs:**
- `workcore-contracts.json` - WorkCore data contracts
- `engine-contracts.json` - Engine contracts
- `extension-contracts.json` - Extension contracts

**Time:** ~2 minutes  
**Best for:** Understanding data structures  
**When to use:** Designing new features, validating data

---

## Action 5: validate-wizards

**What it does:**
Validates all wizard/workflow definitions.

**Checks:**
- JSON syntax valid
- Schema compliance
- Step definitions correct
- Command mappings valid
- Offline policies specified

**Outputs:**
- Validation results
- Schema violations (if any)
- Wizard list

**Time:** ~3 minutes  
**Best for:** After creating new wizards  
**When to use:** Before deployment, validation gate

---

## Action 6: run-tests

**What it does:**
Checks test suite availability and configuration.

**Shows:**
- Test files found
- Test framework (PHPUnit/Pest)
- Database configuration
- Available test commands
- Test organization

**Outputs:**
- `status.md` - Test suite status
- `database-config.md` - DB configuration
- `structure.md` - Test file organization

**Time:** ~3 minutes  
**Best for:** Understanding testing setup  
**When to use:** Before running tests, validating test infrastructure

---

## Action 7: test-capability

**What it does:**
Tests if a specific WorkCore capability exists and works.

**Requires:**
- Enter capability name (e.g., `workcore.customer.create`)

**Tests:**
- Capability exists
- Schema valid
- Input/output contracts correct
- Permissions defined

**Outputs:**
- `capability-test.json` - Test result

**Time:** ~2 minutes  
**Best for:** Verifying single capability before use  
**When to use:** Before implementing workflow step

**Example:**
```
Action: test-capability
Target: workcore.customer.create
```

---

## Action 8: audit-domain

**What it does:**
Audits a specific domain for health and structure.

**Requires:**
- Enter domain name (e.g., `WorkCore`)

**Checks:**
- Domain exists
- Files present
- Structure valid
- Models found
- Controllers exist

**Outputs:**
- `domain-audit.json` - Audit result
- `domain-audit.txt` - Detailed findings

**Time:** ~2 minutes  
**Best for:** Domain-specific validation  
**When to use:** Troubleshooting domain issues

**Example:**
```
Action: audit-domain
Target: WorkCore
```

---

## Action 9: analyze-dependencies

**What it does:**
Analyzes PHP and Node dependencies.

**Shows:**
- PHP version required
- Laravel version
- Composer packages
- NPM packages
- Dependency compatibility

**Outputs:**
- `dependencies.json` - Full dependency info

**Time:** ~2 minutes  
**Best for:** Understanding system requirements  
**When to use:** Planning environment setup, version compatibility

---

## Action 10: generate-docs

**What it does:**
Generates API reference documentation.

**Creates:**
- API endpoint documentation
- OpenAPI schema
- Example requests/responses
- Authentication requirements
- Rate limits

**Outputs:**
- `api-reference.md` - Full API reference
- `openapi.json` - OpenAPI specification

**Time:** ~3 minutes  
**Best for:** Creating documentation  
**When to use:** Documentation generation, API reference updates

---

## Quick Reference Table

| Action | Purpose | Time | Best For |
|--------|---------|------|----------|
| analyze-structure | Repository layout | 5 min | Understanding repo |
| validate-extensions | Extension health | 3 min | Deployment checks |
| export-command-registry | Command catalog | 2 min | Planning workflows |
| export-schemas | Data contracts | 2 min | Understanding models |
| validate-wizards | Wizard validation | 3 min | Wizard deployment |
| run-tests | Test discovery | 3 min | Test infrastructure |
| test-capability | Single test | 2 min | Capability verification |
| audit-domain | Domain audit | 2 min | Domain troubleshooting |
| analyze-dependencies | Dependency check | 2 min | Environment setup |
| generate-docs | Doc generation | 3 min | API documentation |

---

## Recommended Sequences

### Understand Everything
```
1. analyze-structure      (5 min) - See what's in repo
2. export-command-registry (2 min) - See what commands available
3. validate-extensions    (3 min) - Check extension health
4. export-schemas         (2 min) - Understand data structure
Total: ~12 minutes
```

### Plan a Feature
```
1. analyze-structure      (5 min) - Find related domains
2. export-command-registry (2 min) - Find related commands
3. audit-domain           (2 min) - Understand domain structure
4. export-schemas         (2 min) - Check data contracts
Total: ~11 minutes
```

### Validate Deployment
```
1. validate-extensions    (3 min) - Check extensions
2. validate-wizards       (3 min) - Check workflows
3. run-tests              (3 min) - Check tests
4. analyze-dependencies   (2 min) - Check versions
Total: ~11 minutes
```

### Quick Check
```
Just one action:
- analyze-structure (5 min) - Get overview
OR
- export-command-registry (2 min) - See commands
```

---

## How to Choose an Action

### "I want to understand the repository"
→ Start with **analyze-structure**

### "I need to know what commands are available"
→ Use **export-command-registry**

### "I'm creating a new feature"
→ Use **analyze-structure** + **export-schemas**

### "I'm deploying code"
→ Use **validate-extensions** + **validate-wizards**

### "I need to test something"
→ Use **test-capability** or **run-tests**

### "I need documentation"
→ Use **generate-docs**

### "I'm troubleshooting an issue"
→ Use **audit-domain** + **export-schemas**

### "I'm checking dependencies"
→ Use **analyze-dependencies**

---

## Tips & Tricks

### Run Multiple Actions
```
No need to wait between actions:
1. Start Action 1 (analyze-structure)
2. Immediately start Action 2 (export-command-registry)
3. They run in parallel
4. Download both results when done
```

### Save Time
```
Results cached for 30 days, so:
- First run: Full execution
- Subsequent runs same day: Faster
- Review previous artifacts if available
```

### Combine Outputs
```
Results are structured (JSON/Markdown), so you can:
- Parse JSON programmatically
- Grep through Markdown
- Build reports from outputs
- Automate analysis
```

### Automated Runs
```
Some actions run automatically:
- Every Sunday 2 AM UTC: analyze-structure
- Every Monday 3 AM UTC: export jobs
- Every push to feature branches: validate
```

---

## Artifact Locations

After each action, check artifacts:

```
GitHub UI:
1. Click Actions tab
2. Find completed run
3. Scroll to Artifacts
4. Download: chatgpt-results-{number}
```

Files are organized:
```
chatgpt-results-{number}/
├── analysis/          (analyze-structure outputs)
├── validation/        (validate-* outputs)
├── test-results/      (run-tests outputs)
├── export/           (export-* outputs)
└── docs/generated/   (generate-docs outputs)
```

---

## Common Issues

### Action is slow?
```
→ Normal: First run ~5 min, cached runs ~2-3 min
→ Dependency caching helps speed up subsequent runs
```

### No output files?
```
→ Workflow may still be running
→ Check for green checkmark (✓)
→ Scroll to Artifacts section
→ Results expire after 30 days
```

### Want specific output?
```
→ Different actions produce different outputs
→ Choose action based on what you need
→ See "How to Choose" section above
```

### Need a custom action?
```
→ These 10 are standard
→ More can be added in Phase 2
→ See Implementation Guide
```

---

## Next Steps

1. **Tried one action?** → Try another
2. **Want full details?** → Read [QUICK_REFERENCE.md](./QUICK_REFERENCE.md)
3. **Ready to implement?** → See [../chatgpt-agent/IMPLEMENTATION_GUIDE.md](../chatgpt-agent/IMPLEMENTATION_GUIDE.md)
4. **Have questions?** → See [AGENT_INSTRUCTIONS.md](./AGENT_INSTRUCTIONS.md)

---

**[Back to START_HERE →](./README.md)**

**[Go to Quick Start →](./QUICK_START.md)**

**[See Quick Reference →](./QUICK_REFERENCE.md)**

---

*Complete guide to all 10 available actions*  
*Try them all - they're free and instant!*
