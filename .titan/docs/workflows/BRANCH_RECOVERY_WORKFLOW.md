# Titan Zero Branch Recovery Workflow

Complete workflow for recovering, validating, and merging AI-generated branches.

## Overview

```
Feature Branch
      ↓
[Phase 1] Branch Scan & Categorize
      ↓
[Phase 2] Detect Duplicates
      ↓
[Decision] Already Merged? / Duplicate? → Skip
      ↓
[Phase 3] Create Recovery Plan
      ↓
[Phase 4] Replay Commits on Recovery Branch
      ↓
[Phase 5] Validate (Build, Test, Audit)
      ↓
[Phase 6] Merge into Integration Branch
      ↓
[Phase 7] Regression Testing
      ↓
[Phase 8] Merge into Main (Protected)
```

## Detailed Workflow

### Phase 1: Branch Scan

**Purpose**: Identify and categorize all branches

**Command**:
```bash
npm run titan:scan
```

**Output**: `.titan/registry/branches.json`

**What It Does**:
- Lists all branches in repository
- Compares each branch to main
- Calculates ahead/behind commits
- Identifies changed files
- Categorizes branch type
- Identifies conflict risks

**Categories Assigned**:
- ✅ `already_merged` - No action needed
- ✅ `fast_forward` - Direct merge possible
- 🟡 `cherry_pick_candidate` - Needs recovery
- 🟠 `rebase_needed` - Needs rebase first
- 🔴 `unrelated` - No common ancestry
- ⚫ `duplicate` - Duplicate functionality
- ⚫ `orphaned` - Broken lineage

### Phase 2: Duplicate Detection

**Purpose**: Identify duplicate implementations before merging

**Command**:
```bash
npm run titan:detect-duplicates
```

**Output**: `.titan/registry/duplicates.json`

**What It Detects**:
- Identical service classes
- Duplicate controllers
- Duplicate routes
- Similar tests
- Duplicate components
- Similar utility functions

**Similarity Scoring**:
- `>0.95` - Nearly identical (merge into one)
- `0.80-0.95` - Very similar (review for merge)
- `0.60-0.80` - Similar patterns (may refactor)
- `<0.60` - Different implementations (keep separate)

### Phase 3: Recovery Planning

**Purpose**: Create detailed blueprint for branch recovery

**Command**:
```bash
npm run titan:plan-recovery -- feature/chatbot-offline
```

**Output**: `.titan/recovery/recovery-plan.json`

**Plan Contains**:
1. **Create Recovery Branch**
   ```bash
   git checkout -b recovery/chatbot-offline main
   ```

2. **Cherry-Pick Commits**
   - Identify commits unique to feature branch
   - List in replay sequence
   - Mark known conflict files

3. **Compile Steps**
   - Install dependencies
   - Build project
   - Generate artifacts

4. **Test Steps**
   - Unit tests
   - Integration tests
   - Coverage validation

5. **Audit Steps**
   - No duplicate classes
   - No orphaned routes
   - Dependency injection valid
   - Namespace collision check
   - Import resolution check

### Phase 4: Commit Replay

**Purpose**: Execute cherry-pick sequence on recovery branch

**Command**:
```bash
npm run titan:replay -- recovery/chatbot-offline <commits.json>
```

**Output**: `.titan/recovery/replay.json`

**Process**:
- Checkout recovery branch
- Cherry-pick each commit in sequence
- Handle conflicts as they arise
- Track which commits failed
- Report success/failure

**Conflict Resolution**:
- Stop on conflict
- Show conflict markers
- Wait for manual resolution
- Resume cherry-picking

### Phase 5: Merge Validation

**Purpose**: Ensure recovery branch is safe to merge

**Command**:
```bash
npm run titan:validate -- recovery/chatbot-offline
```

**Output**: `.titan/audits/validation.json`

**Checks Performed**:
1. ✅ Branch exists
2. ✅ Builds successfully (npm run build)
3. ✅ Tests pass (npm test, npm run test:integration)
4. ✅ No duplicate classes
5. ✅ No orphaned routes
6. ✅ Dependency injection valid
7. ✅ Mergeable with main (no hard conflicts)

**Result**:
- `pass` - Safe to merge
- `warning` - Proceed with caution
- `fail` - Do not merge

### Phase 6: Integration

**Purpose**: Merge validated recovery branch into integration staging area

**Process**:
1. Checkout integration branch
2. Merge recovery branch
3. Resolve any conflicts
4. Run integration-level tests
5. Document merge decision

```bash
git checkout integration
git merge recovery/chatbot-offline
npm run test:integration
```

**Tracked In**: `.titan/integration/merged.json`

### Phase 7: Regression Testing

**Purpose**: Ensure no existing functionality broke

**Command**:
```bash
npm run test:regression
```

**Test Suite**:
- Full unit tests
- All integration tests
- End-to-end tests
- Performance benchmarks
- Visual regression tests

**Blocks Merge If**:
- New test failures appear
- Coverage drops below minimum
- Performance degrades significantly

### Phase 8: Main Merge

**Purpose**: Promote integration changes to main branch

**Requirements**:
- ✅ All previous phases passed
- ✅ Regression tests green
- ✅ Code review approved
- ✅ Security scan passed
- ✅ Architecture audit passed

**Process**:
```bash
git checkout main
git merge integration
```

**Protection Rules** (enforced by GitHub):
- No direct commits to main
- PR required
- Status checks must pass
- Branch must be up to date
- Dismissal of stale reviews required

## Recovery Branch Naming

Recovery branches follow pattern: `recovery/<original-name>`

**Examples**:
- `feature/chatbot-offline` → `recovery/chatbot-offline`
- `feature/new-ui-component` → `recovery/new-ui-component`
- `bugfix/offline-sync` → `recovery/offline-sync`

## Merge Decision Tree

```
Branch detected
    ↓
Already merged? → YES → Skip ✅
    ↓ NO
Is duplicate? → YES → Merge into primary, remove duplicate
    ↓ NO
Fast-forward possible? → YES → Direct merge ✅
    ↓ NO
Status = cherry_pick_candidate
    ↓
Create recovery branch from main
    ↓
Cherry-pick unique commits
    ↓
Conflicts? → YES → Manual resolution
    ↓ NO
Validation passes? → NO → Fix issues, retest
    ↓ YES
Merge into integration
    ↓
Regression tests pass? → NO → Investigate, fix, retest
    ↓ YES
Merge into main ✅
```

## Automatic Checks on Every Merge

Every merge automatically checks:

- ✅ No duplicate classes with different methods
- ✅ No duplicate routes with different handlers
- ✅ No duplicate migrations
- ✅ No duplicate service providers
- ✅ No duplicate DI bindings
- ✅ No duplicate Vue components (same selector)
- ✅ No dead code from merge conflicts
- ✅ All imports resolve correctly
- ✅ Namespace collision detection
- ✅ DI container integrity

## Recovery PR Template

When creating PR from recovery branch, auto-generated description includes:

```markdown
## Recovery Summary

**Source Branch**: feature/chatbot-offline
**Recovery Branch**: recovery/chatbot-offline
**Status**: Ready to merge

### Changes
- 34 commits replayed
- 12 files modified
- 0 conflicts

### Validation Results
- ✅ Builds successfully
- ✅ Tests pass (95% coverage)
- ✅ No duplicate classes
- ✅ Mergeable with main

### Risk Assessment
- Merge Risk: LOW
- Regression Risk: LOW
- Conflict Risk: LOW

### Next Steps
1. Review changes
2. Approve recovery PR
3. Merge to integration
4. Run regression tests
5. Promote to main
```

## Troubleshooting

### Branch Has Merge Conflicts

1. Create recovery branch
2. Cherry-pick commits one at a time
3. On conflict, manually resolve
4. Use recovery-specific resolution strategy
5. Continue cherry-picking

### Tests Fail on Recovery Branch

1. Identify which test fails
2. Check if test was already broken
3. If new failure, fix code in recovery branch
4. Rerun validation
5. Update recovery plan with fix

### Duplicate Work Detected

1. Compare implementations
2. Choose "primary" implementation
3. Merge both changes into primary
4. Mark secondary branch as "duplicate"
5. Remove secondary branch

## Monitoring & Reporting

Real-time tracking via `.titan/` registry:

- **Current status**: `.titan/integration/pending.json`
- **Merged branches**: `.titan/integration/merged.json`
- **Failed recoveries**: `.titan/recovery/orphaned.json`
- **Risk assessment**: `.titan/audits/branch-audit.json`

## Integration with Interaction Engine

Recovery system integrates with TitanZero's Interaction Engine:

1. **Auto-Discovery**: Automatically finds branches
2. **Dependency Graph**: Builds knowledge of dependencies
3. **Risk Scoring**: Automatically scores merge risk
4. **PR Generation**: Auto-generates recovery PR descriptions
5. **Smart Decisions**: Makes merge decisions based on analysis

## Key Principles

1. **Never modify feature branches** - Create recovery branches instead
2. **Clean history on main** - Only clean commits reach main
3. **Traceable recovery** - Every merge is fully auditable
4. **Automated validation** - All checks run automatically
5. **Human in the loop** - Critical decisions still require review
