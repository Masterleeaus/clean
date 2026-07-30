# GitHub Branch Protection Configuration

**Configure these settings in GitHub for maximum security.**

---

## Main Branch Protection Rules

### Step 1: Go to Settings → Branches

1. Go to: https://github.com/Masterleeaus/clean/settings/branches
2. Click "Add rule"
3. Branch name pattern: `main`

### Step 2: Configure Protection Rules

**Require pull request reviews before merging**
- ✅ Enable
- Require approvals: `1`
- Dismiss stale pull request approvals: ✅
- Require review from code owners: ✅

**Require status checks to pass**
- ✅ Enable
- Require branches to be up to date: ✅
- Select status checks:
  - `npm-build` (if exists)
  - `npm-test` (if exists)

**Require branches to be up to date**
- ✅ Enable

**Require code owner review**
- ✅ Enable

**Require conversation resolution**
- ✅ Enable

**Require commits to be signed**
- ✅ Enable (optional but recommended)

**Require deployments to be successful**
- ❌ Disable (unless you have deployments)

**Restrict who can push**
- ✅ Enable
- Include administrators: ✅ (applies to everyone)
- Allowed to push: (Enter only your GitHub username)

**Allow force pushes**
- ❌ Disable
- No one: ✅ (force pushes blocked)

**Allow deletions**
- ❌ Disable (prevent branch deletion)

---

## Integration Branch Protection Rules

### Step 1: Add Another Rule

1. Click "Add rule"
2. Branch name pattern: `integration`

### Step 2: Configure (Lighter Rules)

**Require pull request reviews**
- ✅ Enable
- Require approvals: `1`
- Dismiss stale PRs: ✅

**Require status checks**
- ✅ Enable
- Up to date: ✅

**Require code owner review**
- ✅ Enable

**Restrict who can push**
- ✅ Enable (optional)
- Allowed: Your username + trusted reviewers

**Allow deletions**
- ❌ Disable

---

## CODEOWNERS File

Create `.github/CODEOWNERS`:

```
# Titan System - Owner Only
.titan/ @YourGitHubUsername
.titan-backup/ @YourGitHubUsername
.backups/ @YourGitHubUsername

# Protection files
.github/branch-protection-config.md @YourGitHubUsername
.titan/PROTECTION.md @YourGitHubUsername
.titan/WORKFLOW.md @YourGitHubUsername
```

---

## Default Branch Configuration

1. Go to: Settings → General
2. Set default branch to: `integration`
3. This makes new clones use integration, not main

---

## Team Access Control

### Remove Dangerous Permissions

1. Go to: Settings → Access → Collaborators
2. For team members:
   - Set role to: "Maintain" (NOT "Admin")
   - This prevents them from:
     - Deleting branches
     - Modifying settings
     - Changing protection rules

### For Trusted Reviewers Only

1. Set role to: "Maintain"
2. Can review and approve PRs
3. Cannot push to main/integration
4. Cannot delete branches

---

## Deploy Keys

Create read-only access for CI/CD:

1. Settings → Deploy keys
2. Add key with read-only permission
3. Use for automated backups only

---

## Verification Checklist

After configuring GitHub:

- [ ] Main branch protected (view at branch settings)
- [ ] Integration branch created
- [ ] Integration is default branch
- [ ] CODEOWNERS file created
- [ ] Admin restrictions enabled
- [ ] Force push disabled
- [ ] Deletion prevention enabled
- [ ] All team members set to "Maintain" role
- [ ] .titan, .titan-backup, .backups protected
- [ ] Status checks required (if available)

---

## Testing Protection

After setup, test that protection works:

```bash
# Try to push to main (should fail)
git checkout main
echo "test" >> test.txt
git add .
git commit -m "test"
git push origin main  # Should be REJECTED

# Try to delete main (should fail)
git push origin --delete main  # Should be REJECTED

# Try to force push (should fail)
git push -f origin main  # Should be REJECTED
```

All should fail with error messages about branch protection.

---

## Emergency: Owner Lost Access

If you lose access to your own admin account:

1. Go to GitHub Settings → Authorized applications
2. Check for suspicious apps
3. Revoke unknown apps
4. Contact GitHub Support if account compromised
5. Have backup admin account ready

---

**Status**: Configuration guide provided  
**Action**: Manually configure in GitHub UI  
**Timeline**: ~15 minutes
