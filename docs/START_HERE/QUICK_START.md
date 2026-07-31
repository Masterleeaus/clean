# ⚡ Quick Start Guide (5 Minutes)

Get your first workflow running in 5 minutes.

---

## Step 1: Go to Actions (1 minute)

```
1. Open: https://github.com/Masterleeaus/clean
2. Click: "Actions" tab at the top
3. You should see list of workflows on the left
```

**Expected:** You see workflows list

---

## Step 2: Select Master Dispatcher (1 minute)

```
1. Look for: "ChatGPT Agent Master Dispatcher"
2. Click: On the workflow name
3. You should see: "Run workflow" button
```

**Expected:** Blue "Run workflow" button appears

---

## Step 3: Configure & Run (2 minutes)

```
1. Click: "Run workflow" button
2. Dropdown appears with "Action" field
3. Select: "analyze-structure" (recommended first)
4. Leave "target" empty (optional)
5. Click: Green "Run workflow" button at bottom
```

**Expected:** Workflow starts running

---

## Step 4: Monitor & Download (1 minute)

```
1. You'll see: "ChatGPT Agent Master Dispatcher" workflow starting
2. Click: On the running workflow to see details
3. Wait: Green checkmark (✓) appears (~5 minutes)
4. When done, click: "Artifacts" section
5. Download: "chatgpt-results-{number}" file
```

**Expected:** ZIP file downloaded with results

---

## Step 5: Review Results (Optional)

```
Extract the ZIP file to see:

chatgpt-results-{number}/
├── analysis/
│   ├── structure.md       (Repository structure)
│   ├── statistics.md      (Code metrics)
│   └── dependencies.md    (Dependency info)
├── validation/            (If validation ran)
├── test-results/          (If tests ran)
├── docs/generated/        (Generated docs)
└── *.txt, *.log          (Logs)
```

**Look at:** `analysis/structure.md` first

---

## 🎉 Success!

You've successfully:
- ✅ Triggered a GitHub Actions workflow
- ✅ Let it run automation
- ✅ Downloaded results
- ✅ Got repository analysis

---

## What's Next?

### Try More Actions

Each action does something different:

```
1. Run another workflow
2. Select different "Action" from dropdown
3. Try: "validate-extensions" or "export-command-registry"
4. Compare results
```

### Explore Results

```
1. Look at generated reports in artifacts
2. Understand repository structure
3. See what commands are available
4. Review extension list
```

### Read More Guides

- [AVAILABLE_ACTIONS.md](./AVAILABLE_ACTIONS.md) - What each action does
- [QUICK_REFERENCE.md](./QUICK_REFERENCE.md) - Fast lookups
- [AGENT_INSTRUCTIONS.md](./AGENT_INSTRUCTIONS.md) - If you're an AI agent

---

## Troubleshooting

### Workflow isn't showing?
```
→ Refresh the Actions page
→ Check you're in Masterleeaus/clean repo
→ Look for "ChatGPT Agent Master Dispatcher"
```

### Run button grayed out?
```
→ Click on the workflow name first
→ Then "Run workflow" button appears
```

### Workflow fails?
```
→ Check workflow logs (click on failed run)
→ See TROUBLESHOOTING.md for common issues
→ Most failures are expected (not all checks available)
```

### Can't find artifacts?
```
→ Wait for green checkmark (✓)
→ Scroll to "Artifacts" section at bottom
→ Should say "chatgpt-results-{number}"
```

---

## Command Reference

### Available Actions in Master Dispatcher

| Action | Purpose | Time |
|--------|---------|------|
| analyze-structure | Repository layout | ~5 min |
| validate-extensions | Extension checks | ~3 min |
| export-command-registry | Command list | ~2 min |
| export-schemas | Data schemas | ~2 min |
| validate-wizards | Wizard definitions | ~3 min |
| run-tests | Test discovery | ~3 min |
| test-capability | Test single command | ~2 min |
| audit-domain | Domain audit | ~2 min |
| analyze-dependencies | Dependency check | ~2 min |
| generate-docs | API documentation | ~3 min |

---

## What Results Mean

### structure.md
Shows:
- Domains in the system
- Extensions available
- Package organization
- File counts

### statistics.md
Shows:
- Lines of code per domain
- Total PHP files
- Migration count
- Configuration files

### dependencies.md
Shows:
- PHP version requirements
- Laravel version
- Composer packages
- NPM packages

---

## Next Steps

1. **Done with this workflow?**
   → Try another action from the dropdown

2. **Want to understand more?**
   → Read [AVAILABLE_ACTIONS.md](./AVAILABLE_ACTIONS.md)

3. **Ready to implement?**
   → See [../chatgpt-agent/IMPLEMENTATION_GUIDE.md](../chatgpt-agent/IMPLEMENTATION_GUIDE.md)

4. **Are you a ChatGPT agent?**
   → Read [AGENT_INSTRUCTIONS.md](./AGENT_INSTRUCTIONS.md)

---

## Quick Reference

### Workflow Files Location
```
.github/workflows/
├── chatgpt-agent-main.yml      (Master dispatcher - use this!)
├── chatgpt-analyze.yml          (Analysis workflows)
├── chatgpt-validate.yml         (Validation workflows)
├── chatgpt-test.yml             (Test discovery)
└── chatgpt-export.yml           (Export workflows)
```

### Documentation Location
```
docs/
├── START_HERE/                  (Quick guides - you are here!)
│   ├── README.md               (Overview)
│   ├── QUICK_START.md          (This file)
│   ├── AVAILABLE_ACTIONS.md    (Action details)
│   ├── QUICK_REFERENCE.md      (Fast lookup)
│   └── AGENT_INSTRUCTIONS.md   (For AI agents)
│
└── chatgpt-agent/              (Full documentation)
    ├── INDEX.md
    ├── WORKFLOWS.md
    ├── IMPLEMENTATION_GUIDE.md
    ├── TROUBLESHOOTING.md
    └── ...
```

---

## Tips & Tricks

### Save Time
- First run takes ~5 min
- Subsequent runs: 2-3 min
- Results cached for 30 days

### Combine Actions
- Run `analyze-structure` first
- Then run specific validations
- Export data once a week

### Understand Output
- JSON files → Parse programmatically
- Markdown files → Read in text editor
- Artifacts stay for 30 days

### Auto-Scheduled
- Analysis runs automatically every Sunday 2 AM UTC
- Export runs automatically every Monday 3 AM UTC
- Manual runs anytime via GitHub UI

---

## You're Ready! 🚀

**[Go back to START_HERE →](./README.md)**

**[Learn about actions →](./AVAILABLE_ACTIONS.md)**

**[Full documentation →](../chatgpt-agent/)**

---

*Time to first workflow: 5 minutes ✅*  
*Congratulations on getting started!*
