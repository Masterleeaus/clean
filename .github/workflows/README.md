# ChatGPT Agent GitHub Actions Workflows

This directory contains GitHub Actions workflows that enable ChatGPT agents to analyze, validate, and interact with the Titan Zero MagicAI integration workspace.

## Workflows Available

### 1. **chatgpt-agent-main.yml** — Master Dispatcher
The main entry point for ChatGPT agent operations.

**Trigger:** Manual workflow dispatch  
**Usage:**
```
Actions > ChatGPT Agent Master Dispatcher > Run workflow
```

**Actions Available:**
- `analyze-structure` - Analyze repository structure
- `validate-extensions` - Validate extension configurations
- `export-command-registry` - Export WorkCore commands
- `export-schemas` - Export domain schemas
- `validate-wizards` - Validate wizard definitions
- `run-tests` - Run test suite
- `test-capability` - Test specific capability
- `audit-domain` - Audit specific domain
- `analyze-dependencies` - Analyze dependencies
- `generate-docs` - Generate documentation

### 2. **chatgpt-analyze.yml** — Analysis Workflows
Analyzes repository structure, code statistics, and dependencies.

**Triggers:**
- Manual dispatch
- Weekly schedule (Sunday 2 AM UTC)

**Outputs:**
- Repository structure analysis
- Code statistics
- Dependency analysis

### 3. **chatgpt-validate.yml** — Validation Workflows
Validates PHP syntax, extension manifests, and architecture compliance.

**Triggers:**
- Manual dispatch
- Push to `claude/**`, `agent/**`, `feature/**` branches
- Changes to PHP files or extension configs

**Checks:**
- PHP syntax validation
- Extension manifest validation
- Architecture compliance
- Required field verification

### 4. **chatgpt-test.yml** — Test Workflows
Checks test availability and configuration readiness.

**Triggers:**
- Manual dispatch
- Push to feature branches

**Reports:**
- Test suite status
- Database configuration
- Test structure analysis
- Available test commands

### 5. **chatgpt-export.yml** — Export & Documentation
Exports API routes, models, extensions, and migrations.

**Triggers:**
- Manual dispatch
- Weekly schedule (Monday 3 AM UTC)

**Exports:**
- API routes (routes/api.php, routes/web.php)
- WorkCore and domain models
- Extensions manifest (JSON & Markdown)
- Database migrations list

## How to Use

### From GitHub UI

1. Go to **Actions** tab
2. Select workflow (e.g., "ChatGPT Agent Master Dispatcher")
3. Click **Run workflow**
4. Choose action and target
5. Wait for completion
6. Check **Artifacts** for results

### From GitHub CLI

```bash
# List workflows
gh workflow list

# Trigger workflow
gh workflow run chatgpt-agent-main.yml -f action=analyze-structure

# Monitor workflow
gh run list -w chatgpt-agent-main.yml
gh run view <run-id> --log

# Download artifacts
gh run download <run-id> -n chatgpt-results-<run-id>
```

### From ChatGPT Agent

Once ChatGPT agent integration is set up, agents can:
- Trigger workflows directly
- Query workflow results
- Access artifact data
- Parse exported information

## Understanding Artifacts

Each workflow creates artifacts containing:

### Structure Analysis
- `analysis/structure.md` - Repository layout
- `analysis/statistics.md` - Code metrics
- `analysis/dependencies.md` - Dependency info

### Validation Results
- `validation/php-errors.txt` - PHP lint issues
- `validation/extension-errors.txt` - Extension issues
- `validation/architecture-notes.txt` - Architecture notes

### Test Status
- `test-results/status.md` - Test availability
- `test-results/database-config.md` - DB configuration
- `test-results/structure.md` - Test structure

### Exports
- `export/api-routes.md` - API endpoints
- `export/models.md` - Data models
- `export/extensions.json` - Extensions manifest
- `export/extensions.md` - Extensions summary
- `export/migrations.md` - Database migrations

## Workflow Patterns

### Pattern 1: Quick Analysis
```
Trigger: chatgpt-agent-main.yml
Action: analyze-structure
Wait: ~2 minutes
Result: Artifact with structure analysis
```

### Pattern 2: Validation Check
```
Trigger: chatgpt-validate.yml (auto-triggered on push)
Wait: ~5 minutes
Result: Validation status in workflow logs
```

### Pattern 3: Full Export
```
Trigger: chatgpt-export.yml
Wait: ~5 minutes
Result: Multiple artifacts with exported data
```

### Pattern 4: Test Readiness
```
Trigger: chatgpt-test.yml
Wait: ~3 minutes
Result: Test status and available test commands
```

## Scheduled Workflows

These workflows run automatically on a schedule:

| Workflow | Schedule | Time (UTC) | Purpose |
|----------|----------|-----------|---------|
| chatgpt-analyze.yml | Weekly | Sun 02:00 | Structure analysis |
| chatgpt-export.yml | Weekly | Mon 03:00 | Export documentation |

## Environment & Dependencies

### PHP Workflows
- **PHP Version**: 8.2
- **Extensions**: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite
- **Timeout**: 10-30 minutes

### Node Workflows
- **Node Version**: 18
- **Timeout**: 10 minutes

### No Special Secrets Required
Most workflows work without special secrets, but some operations may require:
- GitHub token (auto-provided)
- Composer auth (if needed)

## Troubleshooting

### Workflow Failed
1. Check workflow logs in GitHub Actions
2. Look at artifact outputs
3. Review error messages
4. Check branch/path conditions

### No Artifacts Created
- Workflow may have failed - check logs
- Artifact retention (default 30 days)
- Check specific workflow step that creates artifacts

### Validation Errors
- Check PHP syntax: `php -l file.php`
- Validate JSON: `jq empty file.json`
- Review error details in validation artifacts

## Advanced Usage

### Trigger from Command Line
```bash
# Using GitHub CLI
gh workflow run chatgpt-agent-main.yml \
  -f action=test-capability \
  -f target=workcore.customer.create

# Using REST API
curl -X POST \
  -H "Authorization: token $GITHUB_TOKEN" \
  https://api.github.com/repos/Masterleeaus/clean/actions/workflows/chatgpt-agent-main.yml/dispatches \
  -d '{"ref":"main","inputs":{"action":"analyze-structure"}}'
```

### Parse Artifact Data
```bash
# Download and extract
gh run download <run-id> -n chatgpt-results-<run-id>

# Parse JSON exports
jq . export/extensions.json

# Search in markdown
grep "WorkCore" export/models.md
```

## Integration with ChatGPT

Once the ChatGPT agent integration is complete (see CHATGPT_AGENT_IMPLEMENTATION_GUIDE.md), these workflows can be:

1. **Triggered by ChatGPT** - Directly from agent requests
2. **Queried for results** - Via API endpoints
3. **Chained together** - Multiple workflows in sequence
4. **Monitored in real-time** - Status updates to agent

## Limitations & Notes

- Workflows run in GitHub-hosted environment
- Cannot modify repository directly from workflows
- Results are time-limited (retention period)
- Some operations may require elevated permissions
- Concurrent runs are limited per concurrency group

## Next Steps

1. **Test workflows manually** from GitHub Actions UI
2. **Review artifact outputs** to understand structure
3. **Implement API endpoints** (see Implementation Guide)
4. **Configure ChatGPT integration** (see Implementation Guide)
5. **Set up monitoring** for workflow health

## Support & Documentation

- **Quick Reference**: See CHATGPT_AGENT_QUICK_REFERENCE.md
- **Implementation Guide**: See CHATGPT_AGENT_IMPLEMENTATION_GUIDE.md
- **Workflow Catalog**: See CHATGPT_AGENT_WORKFLOWS_AND_ACTIONS.md
- **Index & Navigation**: See CHATGPT_AGENT_INDEX.md

---

**Last Updated**: 2026-07-29  
**Workflows**: 5 main + templates  
**Artifacts**: 20+ types  
**Triggers**: Manual + Scheduled  
