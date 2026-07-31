# Agent Development Guide

Learn how to build, test, and deploy agents on the Titan OS.

---

## Quick Start: Building Your First Agent

### Step 1: Define Agent Schema

Create `.titan/agents/my-agent/schema.json`:

```json
{
  "name": "my-agent",
  "version": "1.0.0",
  "type": "custom",
  "description": "Description of what your agent does",
  "archetypes": ["research-agent"],
  "capabilities": [
    "tool:file-operations",
    "tool:git-commands",
    "external:http"
  ],
  "context": {
    "max_tokens": 200000,
    "model": "claude-opus-5"
  },
  "resources": {
    "memory_mb": 1024,
    "cpu_shares": 2.0,
    "timeout_seconds": 3600
  }
}
```

### Step 2: Implement Agent Logic

Create `.titan/agents/my-agent/index.js`:

```javascript
const { Agent } = require('@titan/runtime');

class MyAgent extends Agent {
  async initialize() {
    this.name = 'my-agent';
    this.registerTool('analyze', this.analyze.bind(this));
    this.subscribe('events/trigger', this.onTrigger.bind(this));
  }

  async analyze(data) {
    // Agent logic here
    return { result: 'analysis complete' };
  }

  async onTrigger(event) {
    console.log('Triggered:', event);
  }

  async run() {
    this.emit('status', { state: 'running' });
    // Main execution loop
  }
}

module.exports = MyAgent;
```

### Step 3: Register Agent

```bash
npm run titan:register-agent -- --path .titan/agents/my-agent
```

### Step 4: Test Agent

```bash
npm run titan:test-agent -- --name my-agent --scenario test-scenario
```

### Step 5: Deploy Agent

```bash
npm run titan:deploy-agent -- --name my-agent --environment production
```

---

## Agent Architecture

### Agent Class Structure

```javascript
class Agent extends EventEmitter {
  // Lifecycle methods
  async initialize() { }           // Called on startup
  async run() { }                  // Main execution loop
  async shutdown() { }             // Called on termination
  
  // Tool registration
  registerTool(name, handler) { }  // Register callable tool
  
  // Communication
  emit(type, data) { }             // Emit events
  on(type, handler) { }            // Listen to events
  subscribe(channel, handler) { }  // Subscribe to event channel
  publish(channel, data) { }       // Publish to channel
  
  // RPC & Service calls
  async call(agentId, method, args) { }     // Call another agent
  async invoke(toolName, args) { }          // Invoke registered tool
  
  // State management
  setState(key, value) { }         // Store state
  getState(key) { }                // Retrieve state
  clearState(key) { }              // Remove state
  
  // Logging & observability
  log(level, message, data) { }    // Structured logging
  trace(name, data) { }            // Emit trace event
  metric(name, value, tags) { }    // Record metric
}
```

---

## Built-in Agent Archetypes

### Code Agent
Specialized for code analysis, generation, and refactoring.

```javascript
const { CodeAgent } = require('@titan/agents');

class MyCodeAgent extends CodeAgent {
  async analyzeCode(filepath) {
    const content = await this.readFile(filepath);
    return this.analyze(content);
  }
}
```

**Capabilities:**
- File system access
- Git operations
- Code analysis
- Build execution
- Test running

### Research Agent
Specialized for information gathering and synthesis.

```javascript
const { ResearchAgent } = require('@titan/agents');

class MyResearchAgent extends ResearchAgent {
  async research(topic) {
    const sources = await this.search(topic);
    return this.synthesize(sources);
  }
}
```

**Capabilities:**
- HTTP/API calls
- Data retrieval
- Source analysis
- Information synthesis
- Report generation

### Planning Agent
Specialized for workflow design and orchestration.

```javascript
const { PlanningAgent } = require('@titan/agents');

class MyPlannerAgent extends PlanningAgent {
  async createPlan(objective) {
    const steps = await this.decompose(objective);
    return this.schedule(steps);
  }
}
```

**Capabilities:**
- Task decomposition
- Dependency resolution
- Resource allocation
- Progress tracking
- Plan optimization

### Execution Agent
Specialized for task automation and tool invocation.

```javascript
const { ExecutionAgent } = require('@titan/agents');

class MyExecutorAgent extends ExecutionAgent {
  async execute(plan) {
    for (const step of plan.steps) {
      await this.runStep(step);
    }
  }
}
```

**Capabilities:**
- Tool orchestration
- Error handling
- Retry logic
- Progress reporting
- Result aggregation

---

## Agent Communication

### Direct RPC Call

```javascript
// Agent A calls Agent B
const result = await this.call('agent-b-id', 'processData', {
  input: 'data'
});
```

### Pub/Sub Messaging

```javascript
// Agent A publishes to channel
this.publish('events/deployment', {
  type: 'started',
  timestamp: Date.now()
});

// Agent B subscribes to channel
this.subscribe('events/deployment', (data) => {
  console.log('Deployment event:', data);
});
```

### Broadcast Messaging

```javascript
// Send to all subscribed agents
this.broadcast('notification', {
  message: 'All agents attention!',
  priority: 'high'
});
```

---

## Tool Integration

### Register a Tool

```javascript
async initialize() {
  this.registerTool('fetch-data', async (url) => {
    const response = await fetch(url);
    return response.json();
  });
  
  this.registerTool('process-file', async (filepath) => {
    const content = await this.readFile(filepath);
    return this.process(content);
  });
}
```

### Use MCP Server Tools

```javascript
async initialize() {
  // Register MCP server tools
  const mcpTools = await this.registerMCP({
    server: 'my-mcp-server',
    tools: ['tool1', 'tool2']
  });
}

// Invoke MCP tool
const result = await this.invoke('tool1', { arg1: 'value' });
```

---

## State Management

### Persistent Agent State

```javascript
async initialize() {
  // Load state from storage
  this.config = await this.getState('config');
  this.data = await this.getState('data') || [];
}

async run() {
  // Update state
  this.data.push({ id: Date.now(), value: 'new' });
  await this.setState('data', this.data);
}
```

### Checkpoint & Recovery

```javascript
async run() {
  try {
    for (const item of items) {
      await this.checkpoint({ current: item.id });
      await this.processItem(item);
    }
  } catch (error) {
    // Resume from last checkpoint
    const checkpoint = await this.getCheckpoint();
    console.log('Resuming from:', checkpoint);
  }
}
```

---

## Logging & Observability

### Structured Logging

```javascript
this.log('info', 'Agent started', {
  agentId: this.id,
  version: this.version,
  capabilities: this.capabilities
});

this.log('error', 'Task failed', {
  taskId: taskId,
  error: error.message,
  stack: error.stack
});
```

### Distributed Tracing

```javascript
this.trace('analyze-code', {
  file: filepath,
  lines: 150,
  complexity: 'high'
});

this.trace('api-call', {
  endpoint: '/api/data',
  method: 'GET',
  latency_ms: 125
});
```

### Metrics Collection

```javascript
this.metric('tasks.completed', 1, {
  agent: this.name,
  status: 'success'
});

this.metric('processing.time', 1234, {
  agent: this.name,
  unit: 'ms'
});
```

---

## Testing Agents

### Unit Testing

Create `.titan/agents/my-agent/test.js`:

```javascript
const { test, expect } = require('@titan/testing');
const MyAgent = require('./index');

test('Agent initializes', async () => {
  const agent = new MyAgent();
  await agent.initialize();
  expect(agent.name).toBe('my-agent');
});

test('Agent processes data', async () => {
  const agent = new MyAgent();
  const result = await agent.analyze({ input: 'test' });
  expect(result).toBeDefined();
});
```

### Integration Testing

```javascript
test('Agent communicates with peer', async () => {
  const agentA = new MyAgent({ id: 'agent-a' });
  const agentB = new MyAgent({ id: 'agent-b' });
  
  await agentA.initialize();
  await agentB.initialize();
  
  const result = await agentA.call('agent-b', 'echo', { msg: 'hello' });
  expect(result).toBe('hello');
});
```

### Scenario Testing

```bash
npm run titan:test-agent -- --name my-agent --scenario complex-workflow
```

---

## Performance & Scaling

### Resource Limits

```json
{
  "resources": {
    "memory_mb": 2048,
    "cpu_shares": 4.0,
    "timeout_seconds": 7200,
    "max_concurrent_tasks": 5
  }
}
```

### Horizontal Scaling

```bash
# Scale agent to 3 instances
npm run titan:scale -- --agent my-agent --instances 3

# Enable auto-scaling
npm run titan:autoscale -- --agent my-agent --min 1 --max 5 --metric cpu
```

### Profiling

```bash
npm run titan:profile -- --agent my-agent --duration 60s
npm run titan:profile-memory -- --agent my-agent
npm run titan:profile-cpu -- --agent my-agent
```

---

## Deployment

### Local Deployment

```bash
npm run titan:deploy-agent -- --name my-agent --environment local
```

### Staging Deployment

```bash
npm run titan:deploy-agent -- --name my-agent --environment staging
```

### Production Deployment

```bash
npm run titan:deploy-agent -- --name my-agent --environment production --replicas 3
```

### Canary Deployment

```bash
npm run titan:canary-deploy -- --name my-agent --percentage 10
```

---

## Best Practices

1. **Always implement initialize()** - Set up tools, subscriptions, and state
2. **Use structured logging** - Include context with every log message
3. **Implement graceful shutdown** - Clean up resources in shutdown()
4. **Handle errors** - Use try/catch and emit error events
5. **Test thoroughly** - Unit, integration, and scenario tests
6. **Monitor performance** - Emit metrics for resource usage
7. **Document APIs** - Clear docstrings for agent tools
8. **Version your agent** - Semantic versioning in schema.json
9. **Use checkpoints** - For long-running, resumable operations
10. **Respect resource limits** - Stay within configured constraints

---

## Troubleshooting

### Agent Won't Start

```bash
npm run titan:debug -- --agent my-agent --verbose
```

Check `.titan/logs/agents/my-agent.log`

### High Memory Usage

```bash
npm run titan:profile-memory -- --agent my-agent --detailed
```

### Tool Not Found

```bash
npm run titan:tools:list -- --agent my-agent
npm run titan:tools:diagnose -- --agent my-agent
```

### Communication Issues

```bash
npm run titan:events:trace -- --agent my-agent --channel events/deployment
```

---

## Advanced Topics

- [Agent Clustering](./docs/agents/CLUSTERING.md)
- [Custom Archetypes](./docs/agents/CUSTOM_ARCHETYPES.md)
- [Plugin System](./docs/plugins/PLUGIN_DEVELOPMENT.md)
- [Workflow Engines](./docs/workflows/WORKFLOW_ENGINES.md)
- [Security Policies](./docs/security/AGENT_SECURITY.md)

---

**Status**: ✅ Production Ready  
**Last Updated**: July 30, 2026
