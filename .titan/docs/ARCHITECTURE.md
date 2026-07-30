# Titan Zero Branch Recovery - Architecture

System design and integration with Interaction Engine.

## System Overview

```
┌─────────────────────────────────────────────────────────────┐
│           Titan Zero Branch Recovery System                 │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Phase 1: Branch Scan → Phase 2: Duplicate Detect          │
│  Phase 3: Plan Recovery → Phase 4: Replay Commits          │
│  Phase 5: Validate → Phase 6: Integration → Phase 7&8: Main│
│                                                              │
│  ↓                                                           │
│  Registry (.titan/registry/) - Central Knowledge Base      │
│  ├─ branches.json (all branch metadata)                    │
│  ├─ commits.json (commit tracking)                         │
│  ├─ files.json (file change history)                       │
│  ├─ services.json (service registry)                       │
│  └─ capabilities.json (feature tracking)                   │
│                                                              │
│  ↓                                                           │
│  Interaction Engine Integration                            │
│  ├─ Auto-discovery of branches                             │
│  ├─ Dependency graph building                              │
│  ├─ Risk scoring                                           │
│  └─ Smart merge decisions                                  │
└─────────────────────────────────────────────────────────────┘
```

## Core Components

### 1. Branch Scanner (`scan-branches.ts`)

**Responsibility**: Discover and categorize all branches

**Input**:
- Git repository with branches

**Process**:
1. List all branches
2. For each branch:
   - Count commits ahead/behind main
   - Identify unique commits
   - List changed files
   - Determine category
   - Assess conflict risk
3. Generate categorization report

**Output**: `.titan/registry/branches.json`

**Categories Assigned**:
```
already_merged
  ↓
  Commits ahead = 0 → Skip

fast_forward
  ↓
  Commits behind = 0 and ahead > 0 → Direct merge

cherry_pick_candidate
  ↓
  Commits ahead > 0 and behind > 0 → Needs recovery

rebase_needed
  ↓
  Behind main → Rebase first

unrelated
  ↓
  No common ancestry → Manual review

duplicate
  ↓
  Exact/near-exact copy elsewhere → Merge into one

orphaned
  ↓
  Broken lineage → Recover or remove
```

### 2. Duplicate Detector (`detect-duplicates.ts`)

**Responsibility**: Find duplicate implementations across branches

**Detection Methods**:
1. **File-level hashing**: MD5 hash of file content
2. **Line count similarity**: Compare line counts
3. **Semantic analysis**: Class/function name matching
4. **Pattern matching**: Similar code structure

**Similarity Scoring**:
```
hash(file1) == hash(file2)
  ↓
  1.0 (identical)

Lines similar + structure similar + names similar
  ↓
  0.85-0.95 (near-duplicate)

Same files modified + similar count
  ↓
  0.70-0.85 (related work)

Different files, different purpose
  ↓
  <0.70 (independent)
```

**Output**: `.titan/registry/duplicates.json`

**Recommendations**:
- `merge_into_one` - Combine implementations
- `keep_separate` - Different purposes
- `refactor_shared` - Extract common code
- `remove_older` - Keep newer, remove old
- `review_manually` - Requires human decision

### 3. Recovery Planner (`plan-recovery.ts`)

**Responsibility**: Create detailed recovery blueprint

**Plan Structure**:
```json
{
  "id": "recovery-001",
  "source_branch": "feature/chatbot-offline",
  "recovery_branch": "recovery/chatbot-offline",
  "plan": {
    "step_1_create_recovery": { command, status },
    "step_2_cherry_pick": { commits, expected_conflicts },
    "step_3_compile": { commands, duration },
    "step_4_tests": { commands, coverage_target },
    "step_5_audit": { checks }
  }
}
```

**What Gets Computed**:
1. All unique commits on feature branch
2. Expected conflict files (based on main changes)
3. Build commands required
4. Test suite to run
5. Architecture checks needed

### 4. Commit Replayer (`replay-commits.ts`)

**Responsibility**: Execute cherry-pick sequence

**Process**:
1. Checkout recovery branch
2. For each commit in sequence:
   - Attempt cherry-pick
   - If conflict: pause and wait for resolution
   - If success: move to next
3. Report results

**Conflict Handling**:
```
Conflict detected
  ↓
Mark file as conflicted
  ↓
Wait for manual resolution
  ↓
Resume cherry-picking
```

**Output**: `.titan/recovery/replay.json`

### 5. Merge Validator (`validate-merge.ts`)

**Responsibility**: Ensure recovery branch is safe

**Checks**:
1. **Build Check**: Compiles without errors
2. **Test Check**: All tests pass, coverage ≥75%
3. **Code Check**: No duplicate classes, valid imports
4. **Architecture Check**: DI valid, no orphaned routes
5. **Merge Check**: No hard conflicts with main

**Outputs**:
- Pass: ✅ Safe to merge
- Warning: ⚠️ Review needed
- Fail: ❌ Do not merge

### 6. Report Generator (`generate-reports.ts`)

**Responsibility**: Create human-readable summaries

**Reports Generated**:
- `summary.md` - High-level overview
- `branch-health.md` - Detailed status
- `merge-history.md` - Audit trail

## Data Flow

```
Feature Branches
     ↓
  Scanner
     ↓
.titan/registry/branches.json
     ↓
Duplicate Detector
     ↓
.titan/registry/duplicates.json
     ↓
Decision Tree
     ├─ Already merged? → Skip
     ├─ Duplicate? → Merge handling
     ├─ Fast-forward? → Direct merge
     └─ Cherry-pick? → Recovery planner
         ↓
     Recovery Planner
         ↓
     .titan/recovery/recovery-plan.json
         ↓
     Cherry-pick Replay
         ↓
     .titan/recovery/replay.json
         ↓
     Merge Validator
         ↓
     .titan/audits/validation.json
         ↓
     Integration Branch
         ↓
     Regression Testing
         ↓
     Main Branch
```

## Registry Schema

### branches.json

```typescript
{
  scan_date: ISO8601;
  total_branches: number;
  categories: {
    already_merged: number;
    fast_forward: number;
    cherry_pick_candidate: number;
    rebase_needed: number;
    unrelated: number;
    duplicate: number;
    orphaned: number;
  };
  branches: Array<{
    name: string;
    parent: string;
    ahead: number;
    behind: number;
    unique_commits: number;
    changed_files: string[];
    status: BranchStatus;
    conflict_risk: RiskLevel;
    last_modified: ISO8601;
    author: string;
    recovery_plan: string;
    tags: string[];
  }>;
}
```

### duplicates.json

```typescript
{
  scan_date: ISO8601;
  total_duplicates: number;
  duplicate_sets: Array<{
    id: string;
    type: FileType;
    files: Array<{ path, branch, lines }>;
    similarity: 0-1;
    hash: string;
    recommendation: string;
    severity: Severity;
  }>;
}
```

### recovery-plan.json

```typescript
{
  id: string;
  source_branch: string;
  recovery_branch: string;
  created_date: ISO8601;
  status: 'planned' | 'in_progress' | 'completed' | 'failed';
  plan: {
    step_1_create_recovery: StepDefinition;
    step_2_cherry_pick: CherryPickPlan;
    step_3_compile: CompilePlan;
    step_4_tests: TestPlan;
    step_5_audit: AuditPlan;
  };
  risk_assessment: {
    merge_risk: RiskLevel;
    regression_risk: RiskLevel;
    conflict_risk: RiskLevel;
    overall_assessment: string;
  };
}
```

## Interaction Engine Integration

### Capabilities Registration

```typescript
interface BranchRecoveryCapability {
  name: "branch-recovery";
  version: "1.0.0";
  
  // Auto-discovery
  methods: {
    discoverBranches(): Branch[];
    buildDependencyGraph(): Graph;
    scoreMergeRisk(): RiskScore;
  };
  
  // Recovery workflow
  recovery: {
    createRecoveryPlan(branch): Plan;
    replayCommits(plan): Result;
    validateMerge(branch): ValidationResult;
  };
  
  // Knowledge graph
  registry: {
    branches: BranchInfo[];
    duplicates: DuplicateSet[];
    services: ServiceRegistry;
    capabilities: CapabilityRegistry;
  };
  
  // Smart decisions
  decisions: {
    shouldMerge(branch): boolean;
    conflictProbability(branch1, branch2): number;
    recommendRecoveryPath(branch): Path;
  };
}
```

### Interaction Engine Hooks

When integrated with Interaction Engine:

```typescript
// Auto-discovery trigger
engine.on('repository:changed', () => {
  recoverySystem.discoverBranches();
  recoverySystem.analyzeForDuplicates();
});

// Merge decision support
engine.command('should-merge', (branch) => {
  const risk = recoverySystem.assessMergeRisk(branch);
  const duplicates = recoverySystem.findDuplicates(branch);
  return risk.safe && duplicates.length === 0;
});

// Auto-recovery
engine.command('recover-branch', (branch) => {
  const plan = recoverySystem.createRecoveryPlan(branch);
  const result = recoverySystem.executeRecovery(plan);
  return result;
});

// Smart PR generation
engine.on('recovery:validated', (branch) => {
  const pr = recoverySystem.generateRecoveryPR(branch);
  github.createPR(pr);
});
```

## Conflict Resolution Strategy

### Automatic Conflict Detection

```
Feature Branch A
  │
  ├─ Modifies: UserService.php
  ├─ Modifies: routes/api.php
  ├─ Adds: ChatController.php
  │
  ↓
Main Branch
  │
  ├─ Also modifies: UserService.php  ← CONFLICT
  ├─ Modifies: views/chat.blade
  └─ No conflict on routes/api.php
  │
  ↓
Expected Conflicts Report:
  - UserService.php (method signature changed in main)
  - Need manual resolution
```

### Conflict Resolution Levels

```
1. Automatic (Content identical)
   → Auto-resolve, mark as auto-resolved

2. Semi-Automatic (Conflict markers clear)
   → Accept both, mark for review

3. Semantic (Methods/logic conflict)
   → Pause, wait for manual resolution

4. Unresolvable (Breaking changes)
   → Mark branch as requires-rebase
```

## Performance Considerations

### Scan Performance

- **10 branches**: ~500ms
- **50 branches**: ~2s
- **200 branches**: ~8s

### Duplicate Detection

- **100 files**: ~1s (hashing)
- **500 files**: ~5s (hashing + similarity)
- **1000+ files**: Runs in background

### Cherry-Pick Replay

- **10 commits**: ~2s
- **50 commits**: ~8s
- **100+ commits**: May detect conflicts

## Storage & Cleanup

### Registry Retention

- `.titan/registry/` - Never purge (source of truth)
- `.titan/recovery/` - Keep for current cycle
- `.titan/audits/` - Keep 90 days (historical)
- `.titan/reports/` - Keep 30 days (references)

### Cleanup Strategy

```bash
# Keep all registry data
find .titan/registry -type f -mtime +365 -delete

# Archive old reports
find .titan/reports -type f -mtime +90 -archive

# Clean up old audits
find .titan/audits -type f -mtime +30 -delete
```

## Extension Points

### Adding New Checks

```typescript
// In validators array
{
  name: "custom-check",
  check: async (branch) => {
    // Custom validation logic
    return { status, message };
  }
}
```

### Adding New Categories

```typescript
// In categorizer
if (condition) {
  return {
    status: 'new_category',
    reason: 'why',
    recovery_plan: 'how to recover'
  };
}
```

### Adding New Validators

```typescript
// In validate-merge.ts
checks.push({
  name: "new-validation",
  validate: async () => { /* ... */ }
});
```

## Security Considerations

- ✅ No credentials stored in registry (config only)
- ✅ Registry is git-tracked (auditable)
- ✅ Cherry-picks create audit trail
- ✅ Recovery branches are read-only
- ✅ Main protected with required checks
- ✅ All operations logged in registry

## Monitoring & Observability

### Metrics Tracked

- Branches scanned per day
- Average merge time
- Duplicate detection rate
- Recovery success rate
- Validation pass rate
- Time from feature to main

### Logging

- All operations logged to `.titan/recovery/replay.json`
- All validations logged to `.titan/audits/`
- All merges tracked in `.titan/integration/`

### Debugging

Each operation creates traceable record:
```json
{
  "id": "recovery-001",
  "timestamp": "2026-07-30T15:30:00Z",
  "operation": "cherry-pick",
  "status": "success",
  "details": { ... }
}
```
