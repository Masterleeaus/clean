# Titan Zero Branch Recovery System

A comprehensive branch recovery and integration pipeline designed to make every AI-generated branch recoverable, traceable, and mergeable regardless of rebases or history rewrites.

## Directory Structure

```
.titan/
├── registry/           # Central registry of all tracked entities
│   ├── branches.json   # All branch metadata
│   ├── commits.json    # Commit tracking and replay info
│   ├── files.json      # File change tracking
│   ├── services.json   # Service/class registry
│   └── capabilities.json # Feature/capability registry
├── recovery/           # Recovery planning and tracking
│   ├── recovery-plan.json # Detailed recovery steps
│   ├── orphaned.json   # Branches with broken lineage
│   ├── duplicates.json # Detected duplicate implementations
│   ├── replay.json     # Commit replay tracking
│   └── merge-report.json # Recovery merge results
├── audits/             # Continuous audit results
│   ├── branch-audit.json # Branch scan results
│   ├── dependency-audit.json # Dependency analysis
│   ├── architecture-audit.json # Architecture violations
│   └── regression-audit.json # Test regression detection
├── integration/        # Integration branch workflow
│   ├── pending.json    # Pending merges
│   ├── merged.json     # Successfully merged branches
│   ├── rejected.json   # Failed/rejected merges
│   └── conflicts.json  # Known conflict patterns
├── reports/            # Generated reports
│   ├── summary.md      # Executive summary
│   ├── branch-health.md # Health of all branches
│   └── merge-history.md # Historical merge data
└── scripts/            # Automation scripts
    ├── scan-branches.ts
    ├── detect-duplicates.ts
    ├── plan-recovery.ts
    ├── replay-commits.ts
    ├── validate-merge.ts
    └── generate-reports.ts
```

## Workflow Phases

### Phase 1: Branch Scan
Automatically scan all branches and categorize them for recovery.

### Phase 2: Duplicate Detection
Identify duplicate work across branches to prevent redundant merges.

### Phase 3: Recovery Planning
Create recovery branch blueprints with cherry-pick sequences.

### Phase 4: Commit Replay
Execute cherry-pick sequences on clean recovery branches.

### Phase 5: Validation
Compile, test, and audit recovery branches.

### Phase 6: Integration
Merge validated branches into integration staging area.

### Phase 7: Regression Scanning
Run full regression test suite on integrated changes.

### Phase 8: Main Merge
Promote to main with full audit trail.

## Key Concepts

### Recovery Branch
A fresh branch created from main with cherry-picked commits from a feature branch. Never modifies the original feature branch.

### Branch Categories
- **Already Merged**: Already in main
- **Fast-Forward**: Can merge cleanly
- **Cherry-Pick Candidate**: Needs cherry-pick recovery
- **Rebase Recovery**: History needs rewinding and replaying
- **Unrelated History**: No common ancestry
- **Duplicate Work**: Same functionality elsewhere
- **Orphaned**: Broken lineage after rebases

### Knowledge Graph
Registry of all branches, files, services, routes, tests, and dependencies for intelligent merge decisions.

## Quick Start

```bash
# Scan all branches
npm run titan:scan

# Generate reports
npm run titan:report

# Plan recovery for a branch
npm run titan:plan feature/chatbot-offline

# Execute recovery
npm run titan:recover feature/chatbot-offline
```

## Integration with Interaction Engine

The recovery system integrates with TitanZero's Interaction Engine to:
- Automatically discover branches
- Build dependency graphs
- Score merge risks
- Generate recovery PR descriptions
- Make intelligent merge decisions
