# Titan Development Workflow

**Effective**: July 30, 2026  
**Branch Strategy**: Three-tier (feature → integration → main)  
**Access**: Role-based (Owner, Reviewer, Contributor)

---

## 🌳 Branch Structure

```
main (Protected)
  ↑
  └─ integration (Shared staging)
      ↑
      ├─ feature/auth-system
      ├─ feature/backup-automation
      ├─ feature/monitoring
      └─ feature/[your-feature]
```

### Branch Purposes

**main**
- Production-ready code
- Owner only
- Tagged releases
- Merge from integration only

**integration**
- Staging environment
- Team collaboration point
- All PRs target this
- Synced to main by owner

**feature/***
- Individual work
- Branch from integration
- PR to integration when ready
- Deleted after merge

---

## 👤 Roles & Permissions

### Owner (Repository Administrator)
- ✅ Push to main
- ✅ Merge integration → main
- ✅ Create release tags
- ✅ Modify branch protection
- ✅ Review sensitive PRs
- ✅ Manage access control
- ✅ Create/restore backups

### Reviewer (Senior Contributors)
- ✅ Review team PRs
- ✅ Approve feature work
- ✅ Suggest improvements
- ❌ Merge without owner approval
- ❌ Push to main
- ❌ Delete branches

### Contributor (Team Members)
- ✅ Create feature branches
- ✅ Push to own feature branches
- ✅ Create PRs to integration
- ✅ Comment on PRs
- ❌ Push to integration directly
- ❌ Push to main
- ❌ Delete any branches

---

## 📝 Workflow Steps

### 1️⃣ Start New Feature

```bash
# Ensure you're up to date
git checkout integration
git pull origin integration

# Create feature branch
git checkout -b feature/descriptive-name

# Start working
echo "Your code here" >> file.js
```

**Naming convention**: `feature/what-you-did`
- ✅ `feature/add-backup-system`
- ✅ `feature/fix-agent-communication`
- ✅ `feature/update-documentation`
- ❌ `feature/work`
- ❌ `feature/fix`

### 2️⃣ Commit Your Work

```bash
# Stage changes
git add .

# Commit with clear message
git commit -m "feat: Add backup automation to Titan system

- Implement daily backup snapshots
- Add versioned backup storage
- Create restore procedures
- Add backup status monitoring"

# Follow format:
# feat: New feature
# fix: Bug fix
# docs: Documentation
# refactor: Code refactoring
# test: Test additions
```

### 3️⃣ Push to Your Branch

```bash
# Push to your feature branch (NOT integration)
git push -u origin feature/your-feature

# Output shows:
# remote: Create a pull request for 'feature/your-feature' by visiting:
#   https://github.com/Masterleeaus/clean/pull/new/feature/your-feature
```

### 4️⃣ Create Pull Request

Click the GitHub link from the push output, or:

1. Go to GitHub repo
2. Click "Pull requests" tab
3. Click "New pull request"
4. **Base**: integration
5. **Compare**: feature/your-feature
6. Add description:

```markdown
## What This Does
Brief description of the change

## Changes
- Change 1
- Change 2
- Change 3

## Testing
How this was tested:
- [ ] Manual testing done
- [ ] No breaking changes
- [ ] Verified functionality

## Related
- Addresses issue #123
- Depends on PR #456
```

### 5️⃣ Code Review

- Request review from team
- Address feedback
- Push additional commits
- Conversation visible in PR

```bash
# After feedback, fix and push
git add .
git commit -m "review: Address feedback about X"
git push origin feature/your-feature
```

### 6️⃣ Merge to Integration

**After approval**:
1. Reviewer approves PR
2. All CI checks pass
3. GitHub shows "Ready to merge"
4. Reviewer clicks "Merge"
5. Feature branch auto-deleted

### 7️⃣ Owner Syncs to Main

**Weekly or when ready to release**:

```bash
# Owner: Merge integration to main
git checkout main
git pull origin main
git merge integration
git push origin main

# Tag the release
git tag -a v1.2.3 -m "Release v1.2.3

- Added backup automation
- Fixed agent communication
- Updated documentation"
git push origin v1.2.3
```

---

## 🚫 What NOT To Do

❌ **Push directly to integration**
```bash
git push origin feature/work:integration  # NO!
```

❌ **Push directly to main**
```bash
git push origin main  # NO! Branch protected
```

❌ **Merge your own PR**
```bash
# Let reviewer merge it
```

❌ **Delete .titan directory**
```bash
rm -rf .titan  # NO! Pre-commit hook blocks this
```

❌ **Force push to any shared branch**
```bash
git push -f origin integration  # NO!
```

---

## 📊 Example: Complete Workflow

```bash
# 1. Start feature
git checkout integration
git pull origin integration
git checkout -b feature/add-telemetry

# 2. Make changes
echo "telemetry code" >> .titan/telemetry/index.js
git add .
git commit -m "feat: Add telemetry system"

# 3. Push feature branch
git push -u origin feature/add-telemetry

# 4. Create PR on GitHub (via link in output)
# - Target: integration
# - Request review
# - Add description

# 5. Make changes based on review
git commit -m "review: Improve error handling"
git push origin feature/add-telemetry

# 6. Reviewer approves and merges to integration
# (You see this notification)

# 7. Owner syncs to main
git checkout main
git pull
git merge integration
git push origin main
git tag -a v1.3.0 -m "Release v1.3.0"
git push origin v1.3.0

# 8. Done! ✅
```

---

## 🔄 Common Questions

### Q: My feature branch is behind integration?
```bash
git fetch origin
git rebase origin/integration
git push -f origin feature/your-feature
```

### Q: How do I update my branch with latest integration?
```bash
git fetch origin
git merge origin/integration
git push origin feature/your-feature
```

### Q: Accidentally committed to integration?
```bash
git reset HEAD~1 origin/integration
git checkout -b feature/your-feature
git commit -m "Your message"
git push origin feature/your-feature
```

### Q: Want to cancel a PR?
1. Go to PR on GitHub
2. Click "Close pull request"
3. Delete the feature branch

### Q: Need to go back to a previous version?
```bash
git checkout v1.0.0
# Or create branch from tag
git checkout -b hotfix/issue v1.0.0
```

---

## 🎯 Best Practices

1. **Sync often** - `git pull origin integration` daily
2. **Small PRs** - Easier to review
3. **Clear commits** - Good messages
4. **Test locally** - Before pushing
5. **Request review** - Don't merge alone
6. **Respond to feedback** - Quickly
7. **Keep branches short-lived** - Delete after merge
8. **Document changes** - Update docs with code

---

## 📞 Need Help?

**Q: Can I push to main?**  
A: Only owner can. Submit PR to integration.

**Q: Branch protection blocking me?**  
A: That's intentional. Use feature branches → integration workflow.

**Q: Lost my work?**  
A: It's in git history. Run: `git reflog` to find it.

**Q: .titan got deleted?**  
A: See PROTECTION.md for recovery steps.

---

**Version**: 1.0  
**Last Updated**: July 30, 2026  
**Owner**: Repository Administrator  
**Team**: All Contributors
