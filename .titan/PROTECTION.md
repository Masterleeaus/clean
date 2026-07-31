# Titan System Protection & Access Control

**Status**: Protected | Read-only for team | Admin-only modifications

---

## 🔒 Access Control

### Branch Protection Rules

**Main Branch** (Protected)
- ❌ No direct pushes allowed
- ❌ No force pushes
- ✅ Only owner can merge
- ✅ All changes require PR review
- ✅ Status checks required

**Integration Branch** (Shared)
- ✅ Team can push feature branches
- ✅ PRs merged here first
- ✅ Synced to main weekly by owner
- ✅ Serves as staging area

**Feature Branches** (Individual)
- Feature branches branch from integration
- Work in isolation
- PR to integration when ready
- Integration PR reviewed before merging to main

---

## 📋 Workflow

### For Team Members

```bash
# 1. Create feature branch FROM integration
git checkout integration
git pull origin integration
git checkout -b feature/your-feature

# 2. Do your work
# ... make changes ...
git add .
git commit -m "Your work"

# 3. Push to your feature branch
git push -u origin feature/your-feature

# 4. Create PR to integration (NOT main)
# GitHub will show button to create PR
# Title: "Feature: Description"
# Target: integration branch
# Wait for review

# 5. After approval, merge to integration
# Do NOT push directly to main
```

### For Owner (You)

```bash
# Monitor integration branch
git checkout integration
git pull origin integration

# When ready to release to main
git checkout main
git pull origin main
git merge integration
git push origin main

# Tag the release
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

---

## 🛡️ Protection Mechanisms

### 1. .gitignore Protection
File: `.gitignore`
```
# Protect .titan from accidental deletions
!.titan/
!.titan/**
!.titan-backup/
!.backups/
```

### 2. Pre-commit Hook (Prevent Deletions)
File: `.git/hooks/pre-commit`
```bash
#!/bin/bash
# Prevent deletion of .titan directory

if git diff --cached --name-status | grep -E "^D.*\.titan" > /dev/null; then
  echo "❌ ERROR: Attempting to delete .titan files"
  echo "This is protected. Contact the repository owner."
  exit 1
fi
```

### 3. Backup Strategy

**Automatic Backups**
- Daily snapshots: `.backups/titan-backup-YYYYMMDD_HHMMSS.tar.gz`
- Git tags on releases: `v1.0.0`, `v1.0.1`, etc.
- Branch history: `integration` branch preserved

**Manual Backup**
```bash
# Create backup on demand
npm run backup:create

# Restore from backup
npm run backup:restore
```

**Retention Policy**
- Keep last 30 daily backups
- Keep all tagged releases
- Archive old backups to cold storage

---

## 🔑 Repository Owner Responsibilities

1. **Monitor integration branch** - Watch for concerning changes
2. **Review PRs carefully** - Before merging to main
3. **Tag releases** - Create version tags when releasing
4. **Sync integration to main** - Weekly or as needed
5. **Maintain backups** - Verify backups are working
6. **Audit access** - Check who has access, remove inactive users

---

## ⚠️ What Cannot Be Done

❌ **Direct pushes to main** - Branch protection blocks this  
❌ **Force pushes to main** - Prevented by rules  
❌ **Deleting .titan directory** - Pre-commit hook blocks  
❌ **Modifying branch protection** - Admin-only  
❌ **Changing team permissions** - Owner-only  

---

## 🚨 If .titan Gets Deleted Again

### Immediate Recovery

```bash
# 1. Check if it's just uncommitted
git status

# 2. Restore from last commit
git checkout HEAD -- .titan/

# 3. If that fails, restore from backup
rm -rf .titan
tar -xzf .backups/titan-backup-LATEST.tar.gz

# 4. Verify integrity
npm run titan:agent-os:start

# 5. Commit recovery
git add .titan/
git commit -m "restore: Recover .titan from backup after deletion"
git push origin $(git rev-parse --abbrev-ref HEAD)
```

### Investigation

```bash
# See who deleted files
git log --diff-filter=D -- .titan/

# See what was deleted
git show COMMIT_HASH

# Audit access logs
git log --all --oneline --graph | head -20
```

---

## 📊 Current Protection Status

| Item | Status |
|------|--------|
| Main branch locked | ⏳ Needs GitHub config |
| Integration branch ready | ✅ Exists |
| Backups created | ✅ Active |
| Pre-commit hooks | ⏳ Needs setup |
| Access controls | ⏳ Needs GitHub config |
| Team informed | ⏳ Pending |

---

## 🔧 Setup Checklist

- [ ] Configure main branch protection (GitHub)
- [ ] Set integration as default working branch
- [ ] Add pre-commit hook to git
- [ ] Create backup automation
- [ ] Inform team of new workflow
- [ ] Test protection (try to break it)
- [ ] Document emergency procedures
- [ ] Set up access audit
- [ ] Create release process
- [ ] Monitor for violations

---

## 📞 Support

If .titan is accidentally deleted:
1. Don't panic
2. Run recovery command above
3. Notify repository owner
4. Investigate via git log
5. Implement additional safeguards

**Important**: Multiple deletions suggest malicious or careless activity. Review repository access immediately.

---

**Last Updated**: July 30, 2026  
**Protection Level**: Maximum  
**Backup Status**: Active
