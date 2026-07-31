# Titan Zero Branch Recovery System - Complete Index

Welcome to the Titan Zero Branch Recovery System. This guide indexes all documentation and components.

## 📚 Documentation

### Getting Started
1. **[QUICKSTART.md](./QUICKSTART.md)** - Start here! 5-minute setup and first run
2. **[README.md](./README.md)** - System overview and concepts

### How It Works
3. **[ARCHITECTURE.md](./ARCHITECTURE.md)** - System design and component architecture
4. **[workflows/BRANCH_RECOVERY_WORKFLOW.md](./workflows/BRANCH_RECOVERY_WORKFLOW.md)** - Detailed 8-phase workflow
5. **[workflows/GITHUB_ACTIONS.md](./workflows/GITHUB_ACTIONS.md)** - CI/CD integration guide

### Configuration
6. **[config/titan.json](./config/titan.json)** - System configuration and settings

## 🛠️ Components

### Scripts (Automation)
- **[scripts/scan-branches.ts](./scripts/scan-branches.ts)** - Phase 1: Branch discovery and categorization
- **[scripts/detect-duplicates.ts](./scripts/detect-duplicates.ts)** - Phase 2: Duplicate detection
- **[scripts/plan-recovery.ts](./scripts/plan-recovery.ts)** - Phase 3: Recovery planning
- **[scripts/replay-commits.ts](./scripts/replay-commits.ts)** - Phase 4: Commit replay
- **[scripts/validate-merge.ts](./scripts/validate-merge.ts)** - Phase 5: Merge validation
- **[scripts/generate-reports.ts](./scripts/generate-reports.ts)** - Report generation

### Workflows (GitHub Actions)
- **[../.github/workflows/titan-scan-branches.yml](../.github/workflows/titan-scan-branches.yml)** - Automated branch scanning
- **[../.github/workflows/titan-validate-recovery.yml](../.github/workflows/titan-validate-recovery.yml)** - PR validation
- **[../.github/workflows/titan-main-protection.yml](../.github/workflows/titan-main-protection.yml)** - Main branch protection

### Schemas
- **[schemas/branch-schema.json](./schemas/branch-schema.json)** - Branch registry schema
- **[schemas/duplicates-schema.json](./schemas/duplicates-schema.json)** - Duplicate detection schema
- **[schemas/recovery-plan-schema.json](./schemas/recovery-plan-schema.json)** - Recovery plan schema

## 📊 Registry Data

### `.titan/registry/` - Central Knowledge Base
```
branches.json         - All branch metadata (primary data source)
duplicates.json       - Detected duplicate implementations
files.json            - File change tracking
services.json         - Service and class registry
capabilities.json     - Feature/capability registry
```

### `.titan/recovery/` - Recovery Operations
```
recovery-plan.json    - Blueprint for branch recovery
orphaned.json         - Branches with broken lineage
duplicates.json       - Detailed duplicate analysis
replay.json           - Cherry-pick results
merge-report.json     - Recovery merge results
```

### `.titan/audits/` - Validation Results
```
branch-audit.json         - Initial branch categorization
dependency-audit.json     - Dependency analysis
architecture-audit.json   - Architecture violations
regression-audit.json     - Test regression detection
```

### `.titan/integration/` - Workflow Tracking
```
pending.json    - Branches waiting to merge
merged.json     - Successfully merged branches
rejected.json   - Failed/rejected merges
conflicts.json  - Known conflict patterns
```

### `.titan/reports/` - Generated Summaries
```
summary.md           - Executive summary
branch-health.md     - Branch status report
merge-history.md     - Historical merge data
```

## 🚀 Quick Commands

### Phase 1: Scan All Branches
```bash
npm run titan:scan
```
Discover and categorize all branches. Output: `.titan/registry/branches.json`

### Phase 2: Detect Duplicates
```bash
npm run titan:detect-duplicates
```
Find duplicate implementations. Output: `.titan/registry/duplicates.json`

### Phase 3: Plan Recovery
```bash
npm run titan:plan -- feature/branch-name
```
Create recovery blueprint. Output: `.titan/recovery/recovery-plan.json`

### Phase 4: Replay Commits
```bash
npm run titan:replay -- recovery/branch-name '<commits-array>'
```
Execute cherry-picks. Output: `.titan/recovery/replay.json`

### Phase 5: Validate Merge
```bash
npm run titan:validate -- recovery/branch-name
```
Validate recovery branch. Output: `.titan/audits/validation.json`

### Generate Reports
```bash
npm run titan:report
```
Create summaries. Output: `.titan/reports/*.md`

## 🔄 Workflow Overview

```
Raw Branches
    ↓
Phase 1: Scan & Categorize
    ↓ (registry/branches.json)
Phase 2: Duplicate Detection
    ↓ (registry/duplicates.json)
[Decision: Skip? Merge? Recover?]
    ├─ Already merged → Skip
    ├─ Duplicate → Merge handling
    ├─ Fast-forward → Direct merge
    └─ Cherry-pick → Phase 3
        ↓
Phase 3: Plan Recovery
    ↓ (recovery/recovery-plan.json)
Phase 4: Replay Commits
    ↓ (recovery/replay.json)
Phase 5: Validate Merge
    ↓ (audits/validation.json)
Phase 6: Integration Branch
    ↓
Phase 7: Regression Testing
    ↓
Phase 8: Main Merge
```

## 📖 Reference by Task

### "I want to understand what branches need attention"
→ Read [QUICKSTART.md](./QUICKSTART.md) → Run `npm run titan:scan` → View `branch-health.md`

### "I found duplicate work, what do I do?"
→ Read [workflows/BRANCH_RECOVERY_WORKFLOW.md](./workflows/BRANCH_RECOVERY_WORKFLOW.md) Phase 2 → Review `duplicates.json` → Make merge decision

### "I need to recover a complex branch"
→ Read [QUICKSTART.md](./QUICKSTART.md) Phase 3-5 → Run `npm run titan:plan` → Execute recovery steps

### "How does this integrate with our CI/CD?"
→ Read [workflows/GITHUB_ACTIONS.md](./workflows/GITHUB_ACTIONS.md) → Copy workflows to `.github/workflows/`

### "I want to understand the system architecture"
→ Read [ARCHITECTURE.md](./ARCHITECTURE.md) → Review [config/titan.json](./config/titan.json) → Check schemas

### "What data is stored and where?"
→ Read "Registry Data" section above → Check `.titan/registry/` → See schemas

## 🔑 Key Concepts

**Branch Categories**:
- `already_merged` - In main already
- `fast_forward` - Direct merge possible
- `cherry_pick_candidate` - Needs recovery
- `rebase_needed` - Behind main
- `unrelated` - No common ancestry
- `duplicate` - Same work elsewhere
- `orphaned` - Broken lineage

**Recovery Branch**: Fresh branch from main with cherry-picked commits (never modifies original)

**Registry**: Central source of truth for all branch metadata

**Duplicate Detection**: Identifies same work across branches before merging

**Validation**: Ensures recovery branches build, test, and pass audit

## ⚙️ Configuration

Main configuration in [config/titan.json](./config/titan.json):

```json
{
  "mainBranch": "main",
  "integrationBranch": "integration",
  "recoveryPrefix": "recovery/",
  "excludedBranches": ["main", "master", ...],
  "validation": {
    "required_checks": [...]
  }
}
```

## 🔐 Protection & Safety

- ✅ Feature branches never modified (only recovery branches)
- ✅ All operations logged in registry
- ✅ Cherry-picks create audit trail
- ✅ Validation before any merge
- ✅ Main protected with required checks
- ✅ Automatic duplicate detection
- ✅ Regression testing before main merge

## 📱 Integration with Interaction Engine

When fully integrated:
- Auto-discovery of branches
- Dependency graph building
- Risk scoring for merges
- Smart merge decisions
- Automatic PR generation
- Recovery suggestions

See [ARCHITECTURE.md](./ARCHITECTURE.md) "Interaction Engine Integration" section.

## 🐛 Troubleshooting

### General Help
→ [QUICKSTART.md - Troubleshooting](./QUICKSTART.md#troubleshooting)

### How to Debug Failed Recovery
```bash
# View the recovery plan
cat .titan/recovery/recovery-plan.json | jq .

# View replay results
cat .titan/recovery/replay.json | jq .conflicts

# View validation details
cat .titan/audits/validation.json | jq .
```

### How to Understand Branch Status
```bash
# View all branches and their status
cat .titan/registry/branches.json | jq '.branches[] | {name, status, ahead, behind}'

# View only problem branches
cat .titan/registry/branches.json | jq '.branches[] | select(.conflict_risk=="high")'

# View all duplicates
cat .titan/registry/duplicates.json | jq '.duplicate_sets[]'
```

## 📞 Support Resources

- **Quick Help**: [QUICKSTART.md](./QUICKSTART.md)
- **System Design**: [ARCHITECTURE.md](./ARCHITECTURE.md)
- **Workflow Details**: [workflows/BRANCH_RECOVERY_WORKFLOW.md](./workflows/BRANCH_RECOVERY_WORKFLOW.md)
- **CI/CD Setup**: [workflows/GITHUB_ACTIONS.md](./workflows/GITHUB_ACTIONS.md)
- **Configuration**: [config/titan.json](./config/titan.json)

## 🎯 Next Steps

1. **Start here**: Read [QUICKSTART.md](./QUICKSTART.md)
2. **Run first scan**: `npm run titan:scan`
3. **Review results**: `cat .titan/registry/branches.json`
4. **Make decisions**: Use registry data to plan recovery
5. **Execute**: Follow phase-by-phase process
6. **Monitor**: Check reports and audit trails

## 📝 File Tree

```
.titan/
├── INDEX.md (this file)
├── README.md (system overview)
├── QUICKSTART.md (5-min setup)
├── ARCHITECTURE.md (system design)
├── registry/ (central data store)
│   ├── branches.json
│   ├── duplicates.json
│   ├── files.json
│   ├── services.json
│   └── capabilities.json
├── recovery/ (recovery operations)
│   ├── recovery-plan.json
│   ├── replay.json
│   ├── orphaned.json
│   └── merge-report.json
├── audits/ (validation results)
│   ├── branch-audit.json
│   ├── dependency-audit.json
│   ├── architecture-audit.json
│   └── regression-audit.json
├── integration/ (workflow tracking)
│   ├── pending.json
│   ├── merged.json
│   ├── rejected.json
│   └── conflicts.json
├── reports/ (summaries)
│   ├── summary.md
│   ├── branch-health.md
│   └── merge-history.md
├── scripts/ (automation)
│   ├── scan-branches.ts
│   ├── detect-duplicates.ts
│   ├── plan-recovery.ts
│   ├── replay-commits.ts
│   ├── validate-merge.ts
│   └── generate-reports.ts
├── schemas/ (JSON schemas)
│   ├── branch-schema.json
│   ├── duplicates-schema.json
│   └── recovery-plan-schema.json
├── config/ (configuration)
│   └── titan.json
└── workflows/ (documentation)
    ├── BRANCH_RECOVERY_WORKFLOW.md
    └── GITHUB_ACTIONS.md
```

---

**Last Updated**: 2026-07-30  
**System Version**: 1.0.0  
**Status**: Production Ready
