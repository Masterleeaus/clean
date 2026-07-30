# GitHub Actions Integration

Automated workflow triggers for Titan Zero branch recovery.

## Available Workflows

### 1. Branch Scan (Manual or Daily)

**File**: `.github/workflows/titan-scan-branches.yml`

**Trigger**:
- Manual dispatch
- Daily at 00:00 UTC
- New branch detected

**Jobs**:
1. Scan all branches
2. Categorize each branch
3. Detect duplicates
4. Generate report
5. Post summary as issue comment

### 2. Recovery Validation

**File**: `.github/workflows/titan-validate-recovery.yml`

**Trigger**:
- PR opened with `recovery/` prefix
- Commit pushed to recovery branch

**Jobs**:
1. Validate recovery branch
2. Run build
3. Run tests
4. Audit code
5. Post validation results as PR comment

### 3. Integration Checks

**File**: `.github/workflows/titan-integration-checks.yml`

**Trigger**:
- PR to `integration` branch

**Jobs**:
1. Verify all upstream validations passed
2. Run integration-level tests
3. Check for regressions
4. Generate integration report

### 4. Main Protection

**File**: `.github/workflows/titan-main-protection.yml`

**Trigger**:
- PR to `main` branch

**Jobs**:
1. Verify branch is clean
2. Verify all checks passed
3. Verify recovery audit passed
4. Verify no direct commits
5. Enforce merge method

## Workflow Configuration

### Setup Steps

```bash
# Create GitHub Actions workflow directory
mkdir -p .github/workflows

# Create workflow files (examples below)
```

### Required Repository Settings

**Main Branch Protection Rules**:
```
- Require pull request reviews before merging (1+ approval)
- Require status checks to pass before merging
- Require branches to be up to date before merging
- Require code reviews
- Dismiss stale reviews when new commits are pushed
- Require status checks:
  - build
  - test
  - lint
  - recovery-audit
  - architecture-audit
```

## Workflow Files

### titan-scan-branches.yml

```yaml
name: Titan Scan Branches
on:
  schedule:
    - cron: '0 0 * * *'  # Daily at 00:00 UTC
  workflow_dispatch:      # Manual trigger
  push:
    branches:
      - main

jobs:
  scan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
        with:
          fetch-depth: 0
      
      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: 18
      
      - name: Install dependencies
        run: npm install
      
      - name: Run branch scan
        run: npm run titan:scan
      
      - name: Generate reports
        run: npm run titan:report
      
      - name: Upload registry
        uses: actions/upload-artifact@v3
        with:
          name: titan-registry
          path: .titan/registry/
      
      - name: Upload reports
        uses: actions/upload-artifact@v3
        with:
          name: titan-reports
          path: .titan/reports/
      
      - name: Post summary
        if: always()
        uses: actions/github-script@v6
        with:
          script: |
            const fs = require('fs');
            const audit = JSON.parse(fs.readFileSync('.titan/registry/branches.json', 'utf-8'));
            
            let summary = '## Titan Branch Scan Results\n\n';
            summary += `- Total branches: ${audit.total_branches}\n`;
            summary += `- Already merged: ${audit.categories.already_merged}\n`;
            summary += `- Ready for recovery: ${audit.categories.cherry_pick_candidate}\n`;
            
            github.rest.issues.createComment({
              issue_number: context.issue.number,
              owner: context.repo.owner,
              repo: context.repo.repo,
              body: summary
            });
```

### titan-validate-recovery.yml

```yaml
name: Titan Validate Recovery
on:
  pull_request:
    branches: [integration, main]
  push:
    branches:
      - 'recovery/**'

jobs:
  validate:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: 18
      
      - name: Install dependencies
        run: npm install
      
      - name: Validate recovery
        run: npm run titan:validate -- ${{ github.head_ref }}
        continue-on-error: true
      
      - name: Build
        run: npm run build
      
      - name: Run tests
        run: npm test
      
      - name: Check coverage
        run: npm run test:coverage
      
      - name: Upload validation
        uses: actions/upload-artifact@v3
        with:
          name: validation-results
          path: .titan/audits/
      
      - name: Post validation
        if: always()
        uses: actions/github-script@v6
        with:
          script: |
            const validation = require('./.titan/audits/validation.json');
            const status = validation.overall_status;
            const icon = status === 'pass' ? '✅' : '⚠️';
            
            github.rest.pulls.createReview({
              pull_number: context.issue.number,
              owner: context.repo.owner,
              repo: context.repo.repo,
              body: `${icon} Validation: ${status}`,
              event: 'COMMENT'
            });
```

### titan-main-protection.yml

```yaml
name: Titan Main Protection
on:
  pull_request:
    branches: [main]
  workflow_run:
    workflows: ["Titan Validate Recovery"]
    types: [completed]

jobs:
  protect:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
        with:
          fetch-depth: 0
      
      - name: Verify branch type
        run: |
          BRANCH="${{ github.head_ref }}"
          if [[ ! "$BRANCH" =~ ^recovery/ ]]; then
            echo "❌ Only recovery/* branches can be merged to main"
            exit 1
          fi
      
      - name: Verify recovery audit passed
        run: |
          if [ ! -f ".titan/audits/validation.json" ]; then
            echo "❌ Recovery audit not found"
            exit 1
          fi
      
      - name: Verify tests pass
        run: npm test
      
      - name: Verify build succeeds
        run: npm run build
```

## CI/CD Integration

### Check Status Names

```
- build            # npm run build
- test             # npm test
- lint             # npm run lint
- recovery-audit   # npm run titan:validate
- architecture-audit # npm run titan:audit
```

### Required Checks for Main

All of the following must pass:
- build
- test (with 75%+ coverage)
- lint
- recovery-audit
- architecture-audit

## Artifact Retention

Workflows upload artifacts for record-keeping:

- Registry files: 90 days
- Validation results: 30 days
- Reports: 90 days
- Build artifacts: 7 days

## Notifications

GitHub Actions posts comments on:

- PR to recovery branch with validation results
- PR to main with final audit report
- Failed recovery with reasons
- Duplicate detection results

## Manual Workflows

Run manually via GitHub UI:

```
Actions → Titan Scan Branches → Run workflow
```

Or via CLI:

```bash
gh workflow run titan-scan-branches.yml
```

## Monitoring

Access workflow runs via:

```
https://github.com/owner/repo/actions
```

Download artifacts:

```
https://github.com/owner/repo/actions/runs/XXX
```

View logs:

```
https://github.com/owner/repo/actions/runs/XXX/logs
```
