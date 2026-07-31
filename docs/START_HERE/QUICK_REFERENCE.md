# ⚡ Quick Reference Tables

Fast lookup for common information.

---

## File Locations

| What | Where |
|------|-------|
| **Workflows** | `.github/workflows/` |
| **START_HERE guides** | `docs/START_HERE/` ← You are here |
| **Full docs** | `docs/chatgpt-agent/` |
| **Domains** | `app/Domains/` |
| **Extensions** | `app/Extensions/` |
| **Routes** | `routes/` |
| **Migrations** | `database/migrations/` |
| **Tests** | `tests/` |
| **Packages** | `packages/` |

---

## Available Domains

| Domain | Purpose | Location |
|--------|---------|----------|
| **WorkCore** | Business operations, authority | `app/Domains/WorkCore/` |
| **Engine** | Interaction engine, wizards | `app/Domains/Engine/` |
| **Entity** | Data models | `app/Domains/Entity/` |
| **TitanTrain** | Training module | `app/Domains/TitanTrain/` |
| **Marketplace** | Extension marketplace | `app/Domains/Marketplace/` |

---

## Extensions by Category

### AI Providers
- OpenRouter (multi-model)
- MultiModel (parallel)
- ModelCouncil (voting)
- Perplexity (research)
- OpenAIRealtimeChat (voice)

### AI Tools
- AIChatPro (chat)
- AIAgent (workflows)
- AIImagePro (images)
- AIVideoPro (video)
- AiPresentation (slides)
- AiMusic (music)
- AiAvatar (avatars)

### Channels
- Chatbot (base)
- TitanZeroChatbot (PWA)
- ChatbotMessenger
- ChatbotWhatsapp
- ChatbotTelegram
- ChatbotInstagram

### Integrations
- Gmail
- Google Calendar
- Google Drive
- Notion
- Outlook
- Mailchimp
- HubSpot

---

## API Endpoints (Main)

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/auth/register` | POST | Register user |
| `/api/auth/login` | POST | User login |
| `/api/user` | GET | Current user |
| `/api/v1/shared-credit/balance` | GET | Credit balance |
| `/api/app/usage-data` | GET | Usage data |
| `/api/aichat/...` | * | Chat endpoints |

---

## Available Workflows

| Workflow | File | Trigger | Use For |
|----------|------|---------|---------|
| **Master Dispatcher** | chatgpt-agent-main.yml | Manual | Run any of 10 actions |
| **Analyze** | chatgpt-analyze.yml | Manual + Weekly | Repository analysis |
| **Validate** | chatgpt-validate.yml | Manual + Push | Code validation |
| **Test** | chatgpt-test.yml | Manual + Push | Test discovery |
| **Export** | chatgpt-export.yml | Manual + Weekly | Data export |

---

## Available Actions

| # | Action | Time | Output |
|---|--------|------|--------|
| 1 | analyze-structure | 5 min | Repository layout |
| 2 | validate-extensions | 3 min | Extension health |
| 3 | export-command-registry | 2 min | Commands list |
| 4 | export-schemas | 2 min | Data schemas |
| 5 | validate-wizards | 3 min | Wizard validation |
| 6 | run-tests | 3 min | Test status |
| 7 | test-capability | 2 min | Single test |
| 8 | audit-domain | 2 min | Domain audit |
| 9 | analyze-dependencies | 2 min | Dependency check |
| 10 | generate-docs | 3 min | API docs |

---

## Documentation Files

| File | Purpose | Read Time |
|------|---------|-----------|
| README.md (START_HERE) | Overview | 5 min |
| QUICK_START.md | First steps | 5 min |
| AVAILABLE_ACTIONS.md | What each action does | 10 min |
| QUICK_REFERENCE.md | This file - fast lookup | 2 min |
| AGENT_INSTRUCTIONS.md | For AI agents | 15 min |
| INDEX.md (full docs) | Master index | 10 min |
| WORKFLOWS.md (full docs) | Detailed specs | 20 min |
| IMPLEMENTATION_GUIDE.md | Setup phases | 30 min |
| TROUBLESHOOTING.md | Common issues | 10 min |

---

## Artifact Types

| Type | Contains | Location in ZIP |
|------|----------|-----------------|
| Structure Analysis | Repository layout | `analysis/structure.md` |
| Statistics | Code metrics | `analysis/statistics.md` |
| Dependencies | Dep info | `analysis/dependencies.md` |
| Validation Results | Errors (if any) | `validation/` |
| Test Status | Test info | `test-results/` |
| Routes Export | API endpoints | `export/api-routes.md` |
| Models Export | Data models | `export/models.md` |
| Extensions | Extension list | `export/extensions.json` |
| Migrations | DB migrations | `export/migrations.md` |

---

## Common Tasks

### "What should I read?"

| Goal | Read This | Time |
|------|-----------|------|
| Get started | [QUICK_START.md](./QUICK_START.md) | 5 min |
| See options | [AVAILABLE_ACTIONS.md](./AVAILABLE_ACTIONS.md) | 10 min |
| Fast lookup | [QUICK_REFERENCE.md](./QUICK_REFERENCE.md) | 2 min |
| Agent rules | [AGENT_INSTRUCTIONS.md](./AGENT_INSTRUCTIONS.md) | 15 min |
| Full guide | [../chatgpt-agent/](../chatgpt-agent/) | 30+ min |

### "What action should I run?"

| I want to... | Run this | Time |
|--------------|----------|------|
| Understand repo | analyze-structure | 5 min |
| Check health | validate-extensions | 3 min |
| See commands | export-command-registry | 2 min |
| Understand data | export-schemas | 2 min |
| Test code | run-tests | 3 min |
| Deploy | validate-extensions + validate-wizards | 6 min |
| Get docs | generate-docs | 3 min |

### "Where is X?"

| Looking for | Location |
|-------------|----------|
| Workflows | `.github/workflows/` |
| Domains | `app/Domains/` |
| Extensions | `app/Extensions/` |
| Routes | `routes/api.php` |
| Models | `app/Domains/*/System/Models/` |
| Migrations | `database/migrations/` |
| Tests | `tests/` |
| Config | `config/` |
| Docs | `docs/` |

---

## Command Line Reference

### GitHub CLI
```bash
# List workflows
gh workflow list

# Run workflow
gh workflow run chatgpt-agent-main.yml \
  -f action=analyze-structure

# Monitor
gh run list -w chatgpt-agent-main.yml
gh run view <run-id> --log

# Download artifacts
gh run download <run-id> -n chatgpt-results-<run-id>
```

### Git
```bash
# Clone
git clone https://github.com/Masterleeaus/clean.git

# Switch branch
git checkout claude/chatgpt-agent-workflows-1pnvbm

# Pull latest
git pull origin claude/chatgpt-agent-workflows-1pnvbm
```

---

## Permission Levels

| Level | Can Read | Can Write | Can Deploy |
|-------|----------|-----------|-----------|
| **ChatGPT Agent** | ✅ All | ⚠️ Feature branch | ❌ No |
| **Contributor** | ✅ All | ✅ Feature branch | ⚠️ PR needed |
| **Maintainer** | ✅ All | ✅ All | ✅ Yes |
| **Owner** | ✅ All | ✅ All | ✅ Yes |

---

## Important Rules (MUST READ)

### Multi-Tenancy
```
❌ Never query without company_id filter
✅ Always scope to current tenant
```

### Credentials
```
❌ Never access .env or secrets
✅ Use secure vault only
```

### Escalation
```
→ Database changes → Escalate
→ Security changes → Escalate
→ Permission changes → Escalate
→ Cross-domain refactoring → Escalate
```

---

## Shortcut URLs

| What | URL |
|------|-----|
| GitHub Actions | `/actions` (on repo page) |
| Workflows | `.github/workflows/` |
| Docs START_HERE | `docs/START_HERE/` |
| Full Documentation | `docs/chatgpt-agent/` |
| Root Index | `CHATGPT_AGENT_INDEX.md` |

---

## Response Times

| Action | Time | With Cache |
|--------|------|-----------|
| analyze-structure | 5 min | 2-3 min |
| validate-extensions | 3 min | 2 min |
| export-* | 2-3 min | 1-2 min |
| validate-* | 3 min | 2 min |
| run-tests | 3 min | 2 min |
| test-capability | 2 min | 1 min |
| audit-domain | 2 min | 1 min |
| analyze-dependencies | 2 min | 1 min |
| generate-docs | 3 min | 2 min |

**Note:** Artifacts cached, so repeated actions are faster

---

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| Can't find workflow | Refresh page, check Actions tab |
| Run button grayed out | Click workflow name first |
| Workflow seems slow | Normal: 2-5 min first run, faster with cache |
| No artifacts | Wait for green checkmark, scroll down |
| Can't parse output | Try JSON files (not Markdown) |
| Need specific info | Try different action for different outputs |

---

## Support Resources

| Need | Resource |
|------|----------|
| How to use | [QUICK_START.md](./QUICK_START.md) |
| What actions do | [AVAILABLE_ACTIONS.md](./AVAILABLE_ACTIONS.md) |
| Fast lookup | This file (QUICK_REFERENCE.md) |
| Agent rules | [AGENT_INSTRUCTIONS.md](./AGENT_INSTRUCTIONS.md) |
| Full details | [../chatgpt-agent/](../chatgpt-agent/) |
| Problem solving | [../chatgpt-agent/TROUBLESHOOTING.md](../chatgpt-agent/TROUBLESHOOTING.md) |

---

## Navigation

```
You are reading: docs/START_HERE/QUICK_REFERENCE.md

Next steps:
1. [Back to START_HERE](./README.md) - Overview
2. [Try QUICK_START](./QUICK_START.md) - First workflow
3. [See All Actions](./AVAILABLE_ACTIONS.md) - What you can do
4. [Read Agent Rules](./AGENT_INSTRUCTIONS.md) - If you're an agent
5. [Full Docs](../chatgpt-agent/) - Complete reference
```

---

## Key Takeaways

✅ **10 actions** available from Master Dispatcher  
✅ **2-5 minutes** per action execution  
✅ **JSON + Markdown** outputs  
✅ **Fully documented** with examples  
✅ **Production ready** workflows  
✅ **Security conscious** design  

**Ready? [Go to QUICK_START →](./QUICK_START.md)**

---

*Quick reference for ChatGPT agents*  
*Bookmark this page for fast lookups*
