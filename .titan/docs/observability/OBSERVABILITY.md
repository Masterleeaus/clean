# Titan Observability Guide

Comprehensive logging, tracing, and monitoring for Titan agents.

---

## Overview

Observability in Titan OS provides three pillars:

1. **Logging** - Event records from agents
2. **Tracing** - Request flow across agents
3. **Metrics** - Quantitative measurements

Together they provide complete visibility into agent operations.

```
┌─────────────────┐       ┌─────────────┐       ┌──────────────┐
│  Agent Logs     │       │   Traces    │       │  Metrics     │
├─────────────────┤       ├─────────────┤       ├──────────────┤
│ • Structured    │       │ • Spans     │       │ • Counters   │
│ • Searchable    │       │ • Context   │       │ • Gauges     │
│ • Filterable    │       │ • Causality │       │ • Histograms │
│ • Persistent    │       │ • Latency   │       │ • Real-time  │
└─────────────────┘       └─────────────┘       └──────────────┘
         │                       │                      │
         └───────────────────────┼──────────────────────┘
                                 │
                        ┌────────▼────────┐
                        │   Dashboards    │
                        │   & Alerting    │
                        └─────────────────┘
```

---

## Structured Logging

### Log Levels

```javascript
agent.log('debug', 'Detailed information', data);    // Debugging
agent.log('info', 'Informational message', data);    // General info
agent.log('warn', 'Warning condition', data);        // Warnings
agent.log('error', 'Error condition', data);         // Errors
agent.log('fatal', 'Fatal error', data);             // Fatal errors
```

### Log Format

```json
{
  "timestamp": "2026-07-30T10:00:00.123Z",
  "level": "info",
  "logger": "code-analyzer",
  "message": "Analysis complete",
  "service": "titan-runtime",
  "environment": "production",
  "trace_id": "trace-abc123",
  "span_id": "span-def456",
  "data": {
    "file": "app.js",
    "lines": 150,
    "issues": 3,
    "duration_ms": 245
  }
}
```

### Structured Logging

```javascript
// Good: structured data
agent.log('info', 'Task completed', {
  taskId: '123',
  duration: 5000,
  status: 'success',
  results: { count: 10 }
});

// Bad: unstructured string
agent.log('info', `Task 123 completed in 5000ms with status success`);
```

### Log Context

Automatic context injection in all logs.

```javascript
agent.setLogContext({
  userId: 'user-123',
  projectId: 'project-456',
  environment: 'production'
});

// All subsequent logs include this context
agent.log('info', 'Starting task');
// Output includes userId, projectId, environment
```

### Log Filtering

```bash
# View all agent logs
npm run titan:logs -- --agent my-agent

# View with level filter
npm run titan:logs -- --agent my-agent --level error

# View with text search
npm run titan:logs -- --agent my-agent --query "failed"

# View with time range
npm run titan:logs -- --agent my-agent --since "1h ago"

# Follow logs in real-time
npm run titan:logs -- --agent my-agent --follow

# Show specific fields only
npm run titan:logs -- --agent my-agent --fields message,duration_ms
```

### Log Storage

Logs stored in multiple tiers:

```
┌──────────────────────┐
│  Current (Hot)       │  In-memory + local disk
│  1-7 days            │  Fast queries
└──────────────────────┘
         ↓
┌──────────────────────┐
│  Archive (Warm)      │  S3 or cloud storage
│  7-90 days           │  Slower, cheaper
└──────────────────────┘
         ↓
┌──────────────────────┐
│  Compliance (Cold)   │  Long-term storage
│  90+ days            │  Immutable, encrypted
└──────────────────────┘
```

---

## Distributed Tracing

### Trace Concept

A trace represents a single request flowing through multiple agents.

```
Request: Agent A → Agent B → Agent C → Agent A (response)

Trace ID: trace-abc123
├─ Span 1: Agent A initial processing (0-50ms)
│  └─ Span 2: Call to Agent B (50-150ms)
│     └─ Span 3: Agent B processing (55-145ms)
│        └─ Span 4: Call to Agent C (75-135ms)
│           └─ Span 5: Agent C processing (80-130ms)
└─ Span 6: Agent A finalization (150-200ms)
```

### Creating Spans

```javascript
// Create span
agent.startSpan('process-task', async () => {
  return await processTask(taskId);
});

// Manual span management
const span = agent.startSpan('manual-operation', {
  tags: { userId: '123' }
});

try {
  await operation();
  span.setTag('status', 'success');
} catch (e) {
  span.setTag('status', 'error');
  span.log({ event: 'error', message: e.message });
} finally {
  span.finish();
}
```

### Span Attributes

```javascript
span.setTag('http.method', 'POST');
span.setTag('http.url', 'https://api.example.com/data');
span.setTag('http.status_code', 200);

span.log({
  event: 'cache_hit',
  cache_key: 'user:123',
  hit_rate: 0.85
});

span.setBaggageItem('user_id', '123');
```

### Trace Context Propagation

Trace context automatically propagated across agents.

```
Agent A executes:
  trace_id: trace-abc123
  span_id: span-1
  baggage: { user_id: '123' }
         ↓
         Calls Agent B
         ↓
Agent B receives:
  trace_id: trace-abc123  (same)
  span_id: span-2         (new parent: span-1)
  baggage: { user_id: '123' }  (inherited)
```

### Querying Traces

```bash
# Find traces for specific agent
npm run titan:traces -- --agent my-agent

# Find traces by trace ID
npm run titan:traces -- --trace-id trace-abc123

# Find slow traces (> 1 second)
npm run titan:traces -- --min-duration 1000

# Find traces with errors
npm run titan:traces -- --status error

# Export trace
npm run titan:traces -- --trace-id trace-abc123 --format json
```

### Trace Visualization

Traces visualized in timeline view:

```
Timeline for trace-abc123:

Agent A      ████████████████████░░░░░░░░░░░░░░
Agent B          ████████████████░░░░░░░░░░░░
Agent C                 ████████████░░░░░░░░

 0ms                                        150ms
```

---

## Metrics & Monitoring

### Metric Types

```javascript
// Counter - monotonically increasing value
agent.counter('tasks.processed', 1, { status: 'success' });

// Gauge - point-in-time value
agent.gauge('agents.active', 5);
agent.gauge('memory.usage', 1024);

// Histogram - distribution of values
agent.histogram('request.latency', 125, { unit: 'ms' });
agent.histogram('file.size', 1024000, { unit: 'bytes' });

// Rate - events per second
agent.rate('requests.per_second', 10.5);
```

### Metric Tags

```javascript
agent.counter('api.calls', 1, {
  endpoint: '/users',
  method: 'GET',
  status: '200'
});

// Query by tags
npm run titan:metrics -- --metric api.calls --tags status:200
```

### Common Metrics

```
Agent Lifecycle:
├─ agents.spawned        - Number of agents created
├─ agents.running        - Current running agents
├─ agents.stopped        - Total agents stopped
└─ agents.errors         - Agent errors

Task Execution:
├─ tasks.started         - Total tasks started
├─ tasks.completed       - Total tasks completed
├─ tasks.failed          - Total task failures
└─ task.duration         - Task execution time

Resource Usage:
├─ memory.usage          - Current memory
├─ memory.peak           - Peak memory
├─ cpu.usage             - Current CPU
└─ context.tokens_used   - Context tokens used

Communication:
├─ messages.sent         - Total messages sent
├─ messages.received     - Total messages received
├─ messages.failed       - Failed messages
└─ message.latency       - Message round-trip time

Tool Usage:
├─ tool.calls            - Tool invocations
├─ tool.duration         - Tool execution time
├─ tool.errors           - Tool errors
└─ tool.latency          - Tool latency
```

### Metrics Query

```bash
# View metric
npm run titan:metrics -- --metric tasks.completed

# View metric with aggregation
npm run titan:metrics -- --metric task.duration --aggregate p95,p99

# View metrics for specific agent
npm run titan:metrics -- --agent my-agent

# View metrics over time range
npm run titan:metrics -- --metric api.calls --since "1h ago"

# View top N values
npm run titan:metrics -- --metric api.calls --top 10 --group-by endpoint
```

---

## Dashboards

### Built-in Dashboards

**System Overview**
- Active agents
- Resource utilization
- Error rate
- Message throughput

**Agent Performance**
- Task execution time
- Error rate
- Resource usage
- Communication latency

**Communication**
- Message volume
- Latency percentiles
- Failure rate
- Topic distribution

**Security**
- Failed authentications
- Denied permissions
- Audit events
- Threat alerts

### Custom Dashboards

```javascript
dashboard = new Dashboard('my-dashboard');
dashboard.addChart('tasks.completed', {
  type: 'timeseries',
  aggregation: 'sum',
  interval: '1m'
});
dashboard.addChart('task.duration', {
  type: 'heatmap',
  tags: { agent: 'my-agent' }
});
await dashboard.save();
```

---

## Alerting

### Alert Rules

```json
{
  "alerts": [
    {
      "name": "high_error_rate",
      "condition": "count(level:error) > 10 in 5m",
      "severity": "critical",
      "notify": ["email", "slack", "pagerduty"]
    },
    {
      "name": "slow_tasks",
      "condition": "p95(task.duration) > 5000 in 5m",
      "severity": "warning",
      "notify": ["email"]
    },
    {
      "name": "memory_high",
      "condition": "gauge(memory.usage) > 2048",
      "severity": "high",
      "notify": ["slack"]
    }
  ]
}
```

### Alert Actions

```javascript
// Automatic remediation
{
  "alerts": [{
    "name": "agent_unhealthy",
    "condition": "agent.health == 'unhealthy'",
    "actions": [
      { "type": "restart_agent" },
      { "type": "notify", "channel": "slack" }
    ]
  }]
}
```

---

## Health Checks

### Agent Health Monitoring

```javascript
// Define health check
agent.defineHealthCheck(async () => {
  return {
    status: 'healthy',
    uptime: process.uptime(),
    lastCheck: Date.now(),
    details: {
      memory: process.memoryUsage(),
      queue: await agent.getQueueDepth()
    }
  };
});

// Query health
const health = await runtime.health.check('agent-id');
```

### Health Status

```
healthy    - All checks passing
degraded   - Some checks failing
unhealthy  - Critical checks failing
unknown    - No recent checks
```

---

## Performance Profiling

### CPU Profiling

```bash
npm run titan:profile-cpu -- --agent my-agent --duration 60s
npm run titan:profile-cpu -- --agent my-agent --output profile.json
```

Output includes:
- Hot functions
- Call graph
- Flame graph
- CPU time distribution

### Memory Profiling

```bash
npm run titan:profile-memory -- --agent my-agent

npm run titan:profile-memory -- --agent my-agent --heap-snapshot
```

Output includes:
- Heap size
- Memory allocation
- Garbage collection stats
- Memory leaks

### Latency Profiling

```bash
npm run titan:profile-latency -- --agent my-agent --operations task.run

# Output: latency percentiles and distribution
```

---

## Log Aggregation

### Centralized Logging

Logs aggregated from all agents to central store.

```
Agent 1 logs → ┐
Agent 2 logs → ├─ Log Aggregator → Elasticsearch → Kibana
Agent 3 logs → ┘
```

### Query Aggregated Logs

```bash
# Full-text search across all agents
npm run titan:logs:search -- "database connection"

# Filter by multiple agents
npm run titan:logs -- --agents agent-1,agent-2,agent-3 --level error

# Aggregate by field
npm run titan:logs:aggregate -- --by agent,level
```

---

## Best Practices

### Logging

1. **Use Structured Logging**
   ```javascript
   // Good
   agent.log('info', 'Task completed', { taskId, duration });
   
   // Bad
   agent.log('info', `Task ${taskId} completed in ${duration}ms`);
   ```

2. **Include Context**
   - Request ID / Trace ID
   - User ID / Agent ID
   - Environment
   - Version

3. **Don't Log Secrets**
   - Mask API keys
   - Encrypt PII
   - Redact passwords

4. **Appropriate Log Levels**
   - DEBUG: Detailed flow
   - INFO: State changes
   - WARN: Potential issues
   - ERROR: Failures
   - FATAL: System down

### Tracing

1. **Trace Important Operations**
   - HTTP requests
   - Database queries
   - External API calls
   - Agent communication

2. **Include Relevant Tags**
   - User ID
   - Resource ID
   - Status
   - Duration

3. **Set Baggage for Context**
   - User information
   - Request context
   - Environment

### Metrics

1. **Instrument Key Operations**
   - Task execution
   - Resource usage
   - API calls
   - Error rates

2. **Use Appropriate Aggregations**
   - COUNT for totals
   - GAUGE for points
   - HISTOGRAM for distributions
   - RATE for frequency

3. **Tag Strategically**
   - Agent type
   - Operation
   - Status
   - Environment

---

## Troubleshooting

### High Memory Usage

```bash
npm run titan:profile-memory -- --agent my-agent
npm run titan:logs -- --agent my-agent --level warn

# Check for memory leaks in logs
npm run titan:metrics -- --metric memory.usage --agent my-agent
```

### High Latency

```bash
npm run titan:profile-latency -- --agent my-agent --operations "*"
npm run titan:traces -- --min-duration 1000 --since "1h ago"

# Identify slow spans
npm run titan:traces:analyze -- --metric p95_latency
```

### High Error Rate

```bash
npm run titan:logs -- --agent my-agent --level error --since "1h ago"
npm run titan:metrics -- --metric errors --agent my-agent

# Get error distribution
npm run titan:logs:aggregate -- --query level:error --by message
```

---

**Status**: ✅ Production Ready  
**Last Updated**: July 30, 2026  
**Backends**: Elasticsearch, Prometheus, Jaeger, CloudWatch
