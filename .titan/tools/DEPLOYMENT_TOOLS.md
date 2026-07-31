# Manufact Deployment Guide

**Tool**: Manufact - ChatGPT Plugin  
**Purpose**: Deploy MCP services, manage CI/CD pipelines, handle infrastructure and releases  
**Best For**: Execution Agents, Backend Code Agents (deployment phase), DevOps specialists

---

## When to Use

### Initial Deployment Setup
- Connecting GitHub repository to Manufact
- Setting up continuous deployment
- Configuring deployment environments
- Planning release strategy

### Deployment Execution
- Deploying to production
- Managing preview deployments
- Rolling back releases
- Testing across clients

### Operations & Monitoring
- Tracking deployment health
- Monitoring service usage
- Managing versions and releases
- Handling incidents

---

## How to Use

### Setup Auto-Deployment
```
"Use Manufact to connect the WorkCore MCP repository and setup:
- Auto-deploy on main branch commits
- Preview deployments for each PR
- Cross-client testing before release
- Automatic rollback on failure"

"Use Manufact to configure CI/CD for this service with:
- Build on every commit
- Test before deployment
- Staging environment for testing
- Production deployment on approval"
```

### Deploy Specific Service
```
"Use Manufact to deploy the [service] MCP to production"
"Use Manufact to setup preview deployment for PR #123"
"Use Manufact to rollback [service] to previous version"
```

### Monitor & Manage
```
"Use Manufact to show deployment status and health"
"Use Manufact to setup usage analytics for this service"
"Use Manufact to configure alerts for deployment failures"
```

### Release Management
```
"Use Manufact to prepare release v1.2.0 for [service]"
"Use Manufact to publish [service] to ChatGPT marketplace"
"Use Manufact to manage version compatibility"
```

---

## Integration with Agent Workflow

### Execution Agent (Pass 1-2)
- **Goal**: Setup & Deployment
- **Use Manufact to**: Setup CI/CD, configure deployment pipeline
- **Output**: Automated deployment infrastructure

### Execution Agent (Pass 3)
- **Goal**: Verification & Polish
- **Use Manufact to**: Test deployments, verify health
- **Output**: Production-ready deployment pipeline

### Code Agent (Pass 4)
- **Goal**: Deploy after code work
- **Use Manufact to**: Deploy fixed/new feature
- **Output**: Running service in production

---

## What Manufact Provides

| Capability | Details |
|-----------|---------|
| **CI/CD Setup** | GitHub integration, build pipelines, test automation |
| **Deployment** | Auto-deploy on commits, PR preview deployments, staged rollouts |
| **Testing** | Cross-client testing, integration testing, smoke tests |
| **Monitoring** | Build logs, deployment logs, service health, usage analytics |
| **Releases** | Version management, release notes, marketplace publishing |
| **Rollback** | One-click rollback, version recovery, incident response |
| **Configuration** | Environment variables, secrets management, deployment config |

---

## Deployment Environments

**Typical Setup:**
1. **Development**: Automatic on feature branches
2. **Staging**: Automatic on integration branch
3. **Production**: Manual/automatic on main branch (configurable)
4. **Preview**: Automatic for each PR

---

## Rate Limits & Pricing

- **Free Tier**: Development/preview deployments
- **Paid Tier**: Production deployments (required for live services)
- **Note**: Free tier perfect for testing CI/CD setup

---

## Capabilities & Limitations

**Strengths:**
- Rapid CI/CD setup with GitHub integration
- Interactive tool testing via Cloud Inspector
- Detailed build and runtime logs
- Version management and rollback
- Marketplace publishing support
- Cross-client testing capabilities
- Analytics and usage tracking

**Limitations:**
- Paid service for production deployments
- Focused on MCP workloads specifically
- External databases needed for persistent data
- Not for traditional full-stack apps (use your own infra)

---

## Workflow Integration

### Execution Agent Example (Deployment)
```
Pass 1: Setup & Foundation
  → Use Manufact to connect GitHub repo
  → Configure build and deployment pipeline
  → Setup staging environment

Pass 2: Core Functionality
  → Configure environment variables
  → Setup secrets management
  → Test build process

Pass 3: Verification & Polish
  → Test full deployment pipeline
  → Verify health checks work
  → Test rollback capability

Pass 4: Documentation
  → Document deployment process
  → Create rollback procedures
  → Publish to marketplace if applicable
```

---

## Examples in Practice

### Example 1: Setup Auto-Deploy
```
Task: "Setup CI/CD for WorkCore service"
Query: "Use Manufact to:
1. Connect GitHub/Masterleeaus/clean repository
2. Setup build pipeline with tests
3. Auto-deploy main branch to production
4. Create preview deployments for PRs
5. Setup health checks"
Result: Complete CI/CD pipeline
Next: Test with real deployments, monitor
```

### Example 2: Cross-Client Testing
```
Task: "Test service across ChatGPT clients"
Query: "Use Manufact Cloud Inspector to:
1. Test MCP endpoints from web client
2. Test from desktop app
3. Test from Slack integration
4. Verify tool definitions work
5. Check performance"
Result: Cross-platform testing confirmation
Next: Deploy to production if all pass
```

### Example 3: Release Management
```
Task: "Release v1.2.0 to marketplace"
Query: "Use Manufact to:
1. Create release from main branch
2. Generate release notes
3. Publish to ChatGPT marketplace
4. Setup rolling deployment (10%/50%/100%)
5. Monitor for issues"
Result: Service available in marketplace
Next: Monitor metrics, handle feedback
```

---

## Best Practices

1. **Automate Everything**: Use auto-deploy to reduce manual steps
2. **Test First**: Configure tests before deployment
3. **Preview Deployments**: Use PR previews to validate changes
4. **Monitor Health**: Setup alerts for deployment failures
5. **Document Process**: Keep deployment runbook updated
6. **Rollback Ready**: Test rollback capability regularly
7. **Gradual Rollout**: Use staged deployment for production

---

## Common Deployment Tasks

1. **First Deploy**: Connect repo → configure pipeline → test deploy
2. **Ongoing Updates**: Push to main → auto-deploy → verify
3. **PR Testing**: Push to PR branch → auto preview → test
4. **Incident Recovery**: Rollback to previous version → fix → redeploy
5. **Scaling**: Configure resource allocation → test under load

---

## Related Tools

- **Build MCP Apps**: Generate service to deploy
- **GitHub**: Source control, trigger deployments via pushes
- **CodeRabbit**: Review code before deployment
- **Process Documentation AI**: Create deployment runbooks and procedures
- **MiniUp**: Publish static docs and dashboards alongside service

---

## Integration into Titan Architecture

Manufact deployments integrate with Titan's:
- Agent lifecycle management
- Service health monitoring
- Cross-agent communication
- Tool discovery and registration

See `.titan/blueprints/23-OBSERVABILITY-HEALTH-DOCTOR-BLUEPRINT.md` for health integration.

---

**Status**: Ready to use (Free tier for development, Paid for production)  
**Last Updated**: July 31, 2026
