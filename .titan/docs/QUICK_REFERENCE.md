# Titan Zero - Quick Reference Card

## 📋 System Commands

```bash
npm run titan:scan              # Scan all branches
npm run titan:detect-duplicates # Find duplicate work
npm run titan:plan -- [branch]  # Create recovery plan
npm run titan:replay -- [...]   # Execute cherry-picks
npm run titan:validate -- [...] # Validate recovery
npm run titan:report            # Generate reports
```

## 🔍 Understanding Your Branches

```bash
# See all branches and their status
cat .titan/registry/branches.json | jq '.branches[] | {name, status, ahead, behind}'

# See branches by status
cat .titan/registry/branches.json | jq '.branches[] | select(.status=="cherry_pick_candidate")'

# See conflict risk
cat .titan/registry/branches.json | jq '.branches[] | select(.conflict_risk=="high")'

# See duplicates
cat .titan/registry/duplicates.json | jq '.duplicate_sets[]'
```

## 📊 Branch Categories at a Glance

| Status | Meaning | Your Action |
|--------|---------|-------------|
| `already_merged` | ✅ Done | Nothing - skip it |
| `fast_forward` | ✅ Easy | Merge directly |
| `cherry_pick_candidate` | ⏳ Needs recovery | Follow recovery workflow |
| `rebase_needed` | ⚠️ Behind main | Rebase first |
| `unrelated` | ❓ Complex | Review manually |
| `duplicate` | ⚫ Copy elsewhere | Merge into primary |
| `orphaned` | 🔴 Broken | Fix or remove |

## 🚀 5-Minute Startup

```bash
# 1. Scan branches (2 min)
npm run titan:scan

# 2. Review report (1 min)
cat .titan/reports/branch-health.md

# 3. Pick branch to recover (1 min)
# Find one with status "cherry_pick_candidate"

# 4. Create recovery plan (1 min)
npm run titan:plan -- feature/branch-name
```

## 🔄 Recovery Workflow (Quick)

For each "cherry_pick_candidate" branch:

```bash
# 1. Plan recovery
npm run titan:plan -- feature/branch-name

# 2. Create recovery branch
git checkout -b recovery/branch-name main

# 3. Cherry-pick commits (from recovery plan)
git cherry-pick <commit-hash-1>
git cherry-pick <commit-hash-2>
# ... repeat for all commits

# 4. Validate
npm run titan:validate -- recovery/branch-name

# 5. If validation passes:
git checkout integration
git merge recovery/branch-name

# 6. Merge to main
git checkout main
git merge integration
git push origin main
```

## 📁 Registry Files Quick Guide

| File | Contains | Read with |
|------|----------|-----------|
| `branches.json` | All branches + status | `jq '.branches[]'` |
| `duplicates.json` | Duplicate code detected | `jq '.duplicate_sets[]'` |
| `recovery-plan.json` | Recovery blueprint | Text editor |
| `replay.json` | Cherry-pick results | `jq '.'` |
| `validation.json` | Validation results | Text editor |

## ⚠️ Conflict Handling Quick Guide

When cherry-pick conflicts occur:

```bash
# 1. See conflicted files
git status

# 2. Fix conflicts manually
# Edit files, resolve <<<< ==== >>>> markers

# 3. Stage resolved files
git add .

# 4. Continue cherry-pick
git cherry-pick --continue

# If too messy, abort and try different strategy:
git cherry-pick --abort
```

## 🎯 Recovery Decision Tree (Simple)

```
Branch detected
  ↓
Already merged? → YES → Done ✅
  ↓ NO
Fast-forward possible? → YES → Merge directly ✅
  ↓ NO
Is duplicate? → YES → Handle duplicate (see duplicates.json)
  ↓ NO
Status = cherry_pick_candidate
  ↓
→ Follow recovery workflow above
```

## 📈 Health Check

```bash
# Full audit in one command
npm run titan:scan && npm run titan:report

# View summary
cat .titan/reports/summary.md

# View branch health
cat .titan/reports/branch-health.md
```

## 🔗 Important Locations

| What | Where | Example |
|------|-------|---------|
| All documentation | `.titan/` | `.titan/README.md` |
| Quick start | `.titan/QUICKSTART.md` | — |
| Complete architecture | `.titan/ARCHITECTURE.md` | — |
| Workflow details | `.titan/workflows/` | — |
| System index | `.titan/INDEX.md` | — |
| Branch data | `.titan/registry/branches.json` | — |
| GitHub workflows | `.github/workflows/` | — |
| npm commands | `package.json` | — |

## 🛟 When Something Goes Wrong

```bash
# View recovery plan
cat .titan/recovery/recovery-plan.json | jq .

# View validation errors
cat .titan/audits/validation.json | jq '.checks[] | select(.status=="fail")'

# View replay conflicts
cat .titan/recovery/replay.json | jq '.conflicts[]'

# Reset and try again
git cherry-pick --abort
```

## 💡 Pro Tips

1. **Before merging to main**: Always run validation first
   ```bash
   npm run titan:validate -- recovery/branch-name
   ```

2. **For complex branches**: Review recovery plan before executing
   ```bash
   cat .titan/recovery/recovery-plan.json | jq '.plan'
   ```

3. **Check for duplicates**: Before creating recovery branch
   ```bash
   npm run titan:detect-duplicates
   cat .titan/registry/duplicates.json | jq '.duplicate_sets[]'
   ```

4. **Keep recovery branches**: They're your audit trail
   - Don't delete recovery branches
   - They prove what was merged and how

5. **Update registry regularly**: Run daily scans
   ```bash
   npm run titan:scan  # Do this daily
   ```

## 🔐 Never Do This

❌ Direct commits to main  
❌ Force push to main  
❌ Modify feature branches (create recovery instead)  
❌ Skip validation steps  
❌ Merge with failing tests  
❌ Ignore duplicate warnings  

## ✅ Always Do This

✅ Scan branches first  
✅ Create recovery branches from main  
✅ Validate before merging  
✅ Review recovery plans  
✅ Keep audit trail in registry  
✅ Run full test suite  
✅ Document merge decisions  

## 📞 Quick Help

```bash
# See available commands
grep "npm run titan" package.json

# See full documentation
cat .titan/QUICKSTART.md

# See system architecture
cat .titan/ARCHITECTURE.md

# See workflow details
cat .titan/workflows/BRANCH_RECOVERY_WORKFLOW.md
```

## 🎓 Learning Path

1. **Beginner** (Start here)
   - Read: `.titan/QUICKSTART.md`
   - Run: `npm run titan:scan`
   - View: `.titan/reports/branch-health.md`

2. **Intermediate**
   - Read: `.titan/workflows/BRANCH_RECOVERY_WORKFLOW.md`
   - Do: Follow phase-by-phase recovery

3. **Advanced**
   - Read: `.titan/ARCHITECTURE.md`
   - Review: System integration points
   - Plan: Interaction Engine integration

---

**Bookmark this card** - Save as favorite or print!

**System Status**: ✅ Production Ready  
**Last Updated**: July 30, 2026  
**Quick Ref Version**: 1.0
