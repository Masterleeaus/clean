# Phase 6: Integration & Compatibility (Weeks 21-24)

Open connectivity with external tools and services.

## Issue 6.1: MCP Compatibility & Plugin Integration Layer

**Effort**: 2 weeks  
**Priority**: P0 - External connectivity  
**Status**: `todo`  
**Dependencies**: Phase 1, Phase 3

### Description

Expose agent capabilities via Model Context Protocol (MCP) so Claude, ChatGPT, IDEs, and external agents can invoke the system and discover available tools.

### MCP Tools to Expose

**Code Analysis Tools** (10+ tools)
- `analyze_file` - AST analysis, metrics, complexity
- `search_code` - Semantic and pattern search
- `find_references` - Find all uses of symbol
- `trace_dependency` - Impact analysis
- `query_graph` - Knowledge graph queries
- `detect_violations` - Constitution violations
- `health_score` - Repository health metrics
- `diff_analysis` - Explain what changed and why

**Execution Tools** (5+ tools)
- `create_task` - Create new task with spec
- `list_tasks` - List active/completed tasks
- `get_task_status` - Check task progress
- `assign_agent` - Manually assign to agent
- `approve_task` - Grant approval for gated task

**Repository Tools** (5+ tools)
- `list_branches` - Show agent branches
- `view_pr` - View PR status and reviews
- `comment_pr` - Add comment to PR
- `trigger_ci` - Manually run CI
- `view_logs` - Access execution logs

**Configuration Tools** (5+ tools)
- `update_policy` - Modify security policy
- `update_constitution` - Modify architectural rules
- `register_agent` - Register new agent
- `set_approval_gate` - Configure approval requirement
- `rotate_secrets` - Force credential rotation

### MCP Server Implementation

```typescript
// .titan/mcp-server/src/index.ts
export const tools: Tool[] = [
  {
    name: "analyze_file",
    description: "Perform AST analysis on a file",
    inputSchema: {
      type: "object",
      properties: {
        file_path: { type: "string" },
        include_metrics: { type: "boolean" }
      }
    }
  },
  // ... 20+ more tools
];
```

### Acceptance Criteria

- [ ] MCP server exposes 20+ tools
- [ ] Tools are discoverable and documented
- [ ] Tool requests authenticated and authorized
- [ ] Results returned in structured format
- [ ] All major agent capabilities exposed
- [ ] External clients can use without API

### Key Tasks

1. Design MCP tool schema
2. Build MCP server in Python/Node.js
3. Expose code analysis tools
4. Expose execution tools
5. Expose repository tools
6. Expose configuration tools
7. Add authentication/authorization
8. Add tool documentation
9. Write comprehensive tests

### Deliverables

- MCP server implementation
- 20+ exposed tools
- MCP client for Python/Node
- Tool documentation

---

## Issue 6.2: Model Router & Provider Independence

**Effort**: 1.5 weeks  
**Priority**: P0 - Provider flexibility  
**Status**: `todo`  
**Dependencies**: 6.1, Phase 7

### Description

Smart routing of tasks to different LLM providers (Claude, GPT-4, Gemini, etc.) based on task type, cost, latency, context window, privacy requirements, and historical performance.

### Routing Logic

```yaml
router:
  routes:
    - name: "Code Generation"
      models:
        - name: "claude-opus"
          score: 100
          cost_per_token: 0.000015
          context_window: 200000
          strengths: ["reasoning", "long_context", "accuracy"]
        - name: "gpt-4"
          score: 95
          cost_per_token: 0.00003
          context_window: 8192
          strengths: ["code", "speed"]
        - name: "claude-sonnet"
          score: 85
          cost_per_token: 0.000003
          context_window: 200000
          fallback_if: ["opus_unavailable"]

    - name: "Test Writing"
      models:
        - name: "claude-sonnet"
          score: 90
          strengths: ["completeness", "coverage"]
        - name: "gpt-4"
          score: 88

    - name: "Documentation"
      models:
        - name: "claude-haiku"
          score: 85
          cost_per_token: 0.00000080
          latency: "fast"

selection_criteria:
  - primary: "task_type"
  - secondary: "cost_budget"
  - tertiary: "context_length"
  - quaternary: "historical_performance"
```

### Cost Optimization

- Per-task cost estimation
- Budget enforcement
- Batch similar tasks to maximize efficiency
- Cache common patterns/templates
- Monitor cost trends, alert on anomalies

### Acceptance Criteria

- [ ] Router supports 5+ LLM providers
- [ ] Automatic fallback if primary fails
- [ ] Cost-aware routing
- [ ] Performance metrics per model
- [ ] Historical success rate tracking
- [ ] Provider switching transparent to agents

### Key Tasks

1. Design router schema
2. Build provider adapters (Claude, GPT-4, Gemini)
3. Implement model selector
4. Build cost calculator
5. Implement fallback logic
6. Add performance tracking
7. Build cost dashboard
8. Write comprehensive tests

### Deliverables

- Model router service
- Provider adapters
- Cost calculator
- Performance dashboard

