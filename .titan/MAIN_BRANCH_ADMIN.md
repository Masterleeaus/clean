# Main Branch Administration Guide

## Current Status: 🔒 LOCKED

**Lock Date**: 2026-07-30  
**Locked By**: Branch Protection System  
**Reason**: Repository consolidation complete - protecting stable state  

## Backup Information

- **Backup Branch**: `backup/main-20260730-131013`
- **Backup Commit**: `6b2b642bb16fc9cf1e334f82946c03e8e575f56d`
- **Last Change**: All 40 branches merged into main
- **Status**: Read-only, immutable

## Protection Layers

### Layer 1: Lock File
- **Location**: `.titan/MAIN_BRANCH_LOCKED`
- **Purpose**: Documents lock status and reason
- **Action**: Prevents accidental modifications

### Layer 2: Git Hook
- **Location**: `.git/hooks/pre-push`
- **Purpose**: Warns users before pushing to main
- **Behavior**: Prompts for confirmation on direct main pushes

### Layer 3: Backup Branch
- **Location**: `backup/main-20260730-131013`
- **Purpose**: Immutable snapshot of main state
- **Recovery**: Can be used to restore if needed

## Workflow for Changes

All changes to main MUST go through this process:

```
1. Create feature branch
   git checkout -b feature/your-feature main

2. Make changes and commit
   git add .
   git commit -m "feature: description"

3. Push to remote
   git push -u origin feature/your-feature

4. Create Pull Request
   - Go to GitHub
   - Create PR from feature branch to main
   - Add description and link related issues

5. Code Review & Approval
   - Request reviewers
   - Address feedback
   - Ensure CI/CD passes

6. Merge via PR
   - Approval required before merge
   - Use "Create a merge commit" option
   - Delete feature branch after merge
```

## Unlocking Main (Emergency Only)

If main needs to be unlocked:

### Prerequisites
- Contact repository administrator
- Provide clear justification
- Have backup verification completed
- Get approval from 2+ team leads

### Process
```bash
# Verify backup exists
git branch -v | grep backup

# Unlock by removing lock file (requires permission)
git rm .titan/MAIN_BRANCH_LOCKED
git commit -m "admin: unlock main branch - [REASON]"
git push origin main

# Document unlock
echo "# Unlock Log: $(date)" >> .titan/MAIN_BRANCH_ADMIN.md
```

## Monitoring

### Check Lock Status
```bash
cat .titan/MAIN_BRANCH_LOCKED
```

### View Backup
```bash
git log backup/main-20260730-131013 --oneline | head -10
```

### Monitor Push Attempts
```bash
# Check git logs for push attempts to main
git log --all --oneline | grep "main"
```

## Troubleshooting

### "I need to bypass the lock"
- Create feature branch instead
- Submit via pull request
- Have changes reviewed before merging

### "I accidentally pushed to main"
- Revert the commit: `git revert <commit-hash>`
- Create pull request with revert
- Document what happened
- Update team

### "I need to restore from backup"
```bash
git checkout backup/main-20260730-131013
git checkout -b main-restored
git push -u origin main-restored
# Then create PR to restore to main
```

## Communication

When main is locked:
- Update team wiki/documentation
- Add lock status to README
- Include in CI/CD pipeline messages
- Reference lock file in PR instructions

## Lock Maintenance

- **Review quarterly**: Ensure still needed
- **Update backup**: If major changes made
- **Document changes**: Keep admin log current
- **Test recovery**: Verify backup works annually

---

**Last Updated**: 2026-07-30  
**Administrator**: Repository Maintainers  
**Questions?**: Contact repo admin or create discussion issue
