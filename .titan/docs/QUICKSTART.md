# Titan Zero Branch Recovery - Quick Start Guide

Get the branch recovery system up and running in 5 minutes.

## What This System Does

The Titan Zero Branch Recovery system automates the process of recovering, validating, and merging AI-generated feature branches. It:

✅ Automatically scans and categorizes all branches  
✅ Detects duplicate implementations  
✅ Creates recovery plans for complex branches  
✅ Replays commits on clean recovery branches  
✅ Validates builds, tests, and architecture  
✅ Prevents broken code from reaching main  

## Installation

### 1. Check Prerequisites

```bash
# Verify Node.js is installed
node --version  # Need v18+

# Verify npm is available
npm --version

# Verify git is available
git --version
```

### 2. Install TypeScript Support (Optional)

```bash
# If you want to run .ts scripts directly
npm install --save-dev typescript ts-node @types/node
```

## First Run - Phase 1: Branch Scan

### Step 1: Scan All Branches

```bash
npm run titan:scan
```

This will:
1. List all branches in your repository
2. Compare each branch to `main`
3. Categorize them (already merged, fast-forward, needs recovery, etc.)
4. Identify potential conflicts
5. Save results to `.titan/registry/branches.json`

**Output**:
```
🔍 Scanning branches...
  Scanning feature/chatbot-offline... [cherry_pick_candidate]
  Scanning feature/new-ui... [fast_forward]
  Scanning feature/fixes... [already_merged]
  
📊 Branch Scan Summary
=====================
Total branches: 15
Scan date: 2026-07-30T15:30:00Z

Categories:
  Already merged: 3
  Fast-forward: 4
  Cherry-pick candidate: 5
  Rebase needed: 2
  Unrelated: 1
  Duplicate: 0
  Orphaned: 0

✅ Audit saved to .titan/registry/branches.json
```

### Step 2: Review Results

```bash
# View the JSON registry
cat .titan/registry/branches.json | jq .

# View formatted report
cat .titan/reports/branch-health.md
```

### Step 3: Generate Reports

```bash
npm run titan:report
```

Generates:
- `summary.md` - Executive summary
- `branch-health.md` - Detailed branch status

## Phase 2: Duplicate Detection (Optional)

Detect duplicate implementations before merging:

```bash
npm run titan:detect-duplicates
```

This scans for:
- Duplicate service classes
- Duplicate controllers
- Duplicate routes
- Duplicate components
- Identical utility functions

Output shows similarity scores and recommendations for handling duplicates.

## Phase 3: Plan Recovery for a Branch

For branches that need cherry-pick recovery:

```bash
npm run titan:plan -- feature/chatbot-offline
```

This creates a detailed recovery blueprint containing:
- Commands to execute
- Commits to replay
- Expected conflicts
- Validation steps
- Test requirements
- Audit checklist

Output: `.titan/recovery/recovery-plan.json`

## Phase 4: Execute Recovery

### Manual Recovery (Recommended for First Time)

For branches marked as `cherry_pick_candidate`:

```bash
# Create recovery branch from main
git checkout -b recovery/chatbot-offline main

# Cherry-pick each commit from feature branch
git cherry-pick <commit-hash-1>
git cherry-pick <commit-hash-2>
# ... repeat for all unique commits

# If conflicts occur, resolve them manually then:
git cherry-pick --continue
```

### Automated Replay (When Ready)

```bash
npm run titan:replay -- recovery/chatbot-offline '<commits-json>'
```

## Phase 5: Validate Recovery Branch

Before merging, validate the recovery branch:

```bash
npm run titan:validate -- recovery/chatbot-offline
```

This checks:
- ✅ Branch exists
- ✅ Builds successfully
- ✅ Tests pass
- ✅ No duplicate classes
- ✅ No broken imports
- ✅ Mergeable with main

**Output**:
```
🔍 Validating merge for recovery/chatbot-offline...
  Building... ✅
  Testing... ✅
  Checking duplicates... ✅
  Validating imports... ✅
  Checking merge conflict... ✅

📋 Validation Results
====================
Branch: recovery/chatbot-offline
Overall Status: pass
Can Merge: ✅ YES

All checks passed! Ready to merge.
```

## Phase 6-8: Manual Integration Steps

### Step 1: Merge into Integration

```bash
git checkout integration
git merge recovery/chatbot-offline
npm test  # Run full test suite
```

### Step 2: Run Regression Tests

```bash
npm run test:regression
```

### Step 3: Merge into Main

Once everything is green:

```bash
git checkout main
git merge integration
git push origin main
```

## Viewing Registry Data

The system maintains a registry in `.titan/registry/`:

```bash
# View branch scan results
cat .titan/registry/branches.json | jq '.branches[] | {name, status, ahead, behind}'

# View detected duplicates
cat .titan/registry/duplicates.json | jq '.duplicate_sets[]'

# View file changes
cat .titan/registry/files.json | jq '.changes[]'
```

## Understanding Branch Categories

| Category | Meaning | Action |
|----------|---------|--------|
| `already_merged` | Already in main | Skip - nothing to do |
| `fast_forward` | Can merge cleanly | Direct merge or fast-forward rebase |
| `cherry_pick_candidate` | Needs recovery | Create recovery branch, cherry-pick commits |
| `rebase_needed` | Behind main | Rebase or cherry-pick recovery |
| `unrelated` | No common ancestry | Review manually |
| `duplicate` | Same work elsewhere | Merge into one, mark other as duplicate |
| `orphaned` | Broken lineage | Recover or remove |

## Common Workflows

### Merge a Simple Branch (Fast-Forward)

```bash
# Status will be "fast_forward"
git checkout main
git merge feature/simple-fix
git push origin main
```

### Recover a Complex Branch

```bash
# Status will be "cherry_pick_candidate"
npm run titan:plan -- feature/complex-work
npm run titan:validate -- recovery/complex-work
git checkout integration
git merge recovery/complex-work
git checkout main
git merge integration
git push origin main
```

### Handle Duplicates

```bash
# Detect duplicates first
npm run titan:detect-duplicates

# Review duplicates in .titan/registry/duplicates.json
# Decide which implementation to keep
# Merge kept implementation
# Remove/mark duplicate branch
```

### Debug a Failed Recovery

```bash
# View the recovery plan
cat .titan/recovery/recovery-plan.json | jq .

# View replay results
cat .titan/recovery/replay.json | jq .conflicts

# View validation details
cat .titan/audits/validation.json | jq .

# Check which test failed
npm test -- --verbose
```

## File Structure

```
.titan/
├── registry/              # Central data store
│   ├── branches.json      # All branch metadata
│   ├── duplicates.json    # Detected duplicates
│   ├── files.json         # File tracking
│   └── services.json      # Service registry
├── recovery/              # Recovery plans & results
│   ├── recovery-plan.json # Blueprint for recovery
│   ├── replay.json        # Cherry-pick results
│   └── orphaned.json      # Problematic branches
├── audits/                # Validation results
│   ├── branch-audit.json  # Initial scan
│   └── validation.json    # Merge validation
├── integration/           # Integration tracking
│   ├── pending.json       # Waiting to merge
│   └── merged.json        # Successfully merged
├── reports/               # Generated reports
│   ├── summary.md         # Executive summary
│   └── branch-health.md   # Branch status
├── scripts/               # Automation scripts
│   ├── scan-branches.ts
│   ├── detect-duplicates.ts
│   ├── plan-recovery.ts
│   ├── replay-commits.ts
│   └── validate-merge.ts
└── workflows/             # Documentation
    ├── BRANCH_RECOVERY_WORKFLOW.md
    └── GITHUB_ACTIONS.md
```

## Tips & Best Practices

### ✅ Do's

- ✅ Always scan branches first to understand state
- ✅ Create recovery branches rather than modifying feature branches
- ✅ Validate recovery branches before merging
- ✅ Keep feature branches as read-only references
- ✅ Document merge decisions in registry
- ✅ Run full test suite before merging to main

### ❌ Don'ts

- ❌ Don't merge feature branches directly to main
- ❌ Don't modify feature branches during recovery
- ❌ Don't skip validation steps
- ❌ Don't merge with broken tests
- ❌ Don't ignore duplicate detection warnings
- ❌ Don't force-push to main

## Troubleshooting

### "Branch not found" error

```bash
# Fetch latest branches
git fetch origin

# Then scan again
npm run titan:scan
```

### "Conflicts detected" during cherry-pick

```bash
# Resolve conflicts manually
# Edit conflicted files

# After resolving
git add .
git cherry-pick --continue
```

### Tests pass locally but fail in recovery

```bash
# Make sure you're testing recovery branch
git checkout recovery/branch-name
npm install  # Fresh install
npm run build
npm test
```

### "No commits to replay"

This means the feature branch is already on main. Mark as `already_merged` and skip.

## Next Steps

1. **Run initial scan**: `npm run titan:scan`
2. **Review results**: `cat .titan/registry/branches.json`
3. **Generate reports**: `npm run titan:report`
4. **Pick a branch to recover**: Choose one marked `cherry_pick_candidate`
5. **Create recovery branch**: `git checkout -b recovery/branch-name main`
6. **Cherry-pick commits**: `git cherry-pick <hashes>`
7. **Validate**: `npm run titan:validate -- recovery/branch-name`
8. **Merge**: `git checkout integration && git merge recovery/branch-name`

## Getting Help

- 📖 Read `.titan/README.md` for system overview
- 📋 Read `.titan/workflows/BRANCH_RECOVERY_WORKFLOW.md` for detailed workflow
- 🔧 Check `.titan/config/titan.json` for configuration options
- 📝 Review `.titan/registry/branches.json` for current state

## Advanced Usage

See `.titan/workflows/GITHUB_ACTIONS.md` for:
- Automated GitHub Actions integration
- CI/CD pipeline setup
- Continuous branch scanning
- Automatic PR validation
- Protected main branch rules
