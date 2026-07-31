# Titan Runtime API Reference

Complete API documentation for the Titan Agent OS runtime system.

---

## Core Runtime Module

### AgentRuntime

Main entry point for the Titan runtime system.

```javascript
const { AgentRuntime } = require('@titan/runtime');

const runtime = new AgentRuntime({
  registry: '.titan/registry',
  storage: '.titan/storage',
  config: '.titan/config/titan-agent-os.json'
});

await runtime.start();
```

#### Methods

##### `start()`
Initialize and start the runtime.

```javascript
await runtime.start();
```

##### `spawn(agentConfig)`
Spawn a new agent instance.

```javascript
const agent = await runtime.spawn({
  name: 'my-agent',
  type: 'code-agent',
  schemaPath: '.titan/agents/my-agent/schema.json'
});
```

**Returns**: `AgentInstance`

##### `getAgent(agentId)`
Retrieve running agent by ID.

```javascript
const agent = await runtime.getAgent('agent-id-123');
```

##### `listAgents(filter?)`
List all running agents.

```javascript
const agents = await runtime.listAgents({ status: 'running' });
```

##### `stopAgent(agentId, timeout?)`
Gracefully stop an agent.

```javascript
await runtime.stopAgent('agent-id-123', 30000);
```

##### `removeAgent(agentId)`
Remove an agent from the registry.

```javascript
await runtime.removeAgent('agent-id-123');
```

##### `broadcast(channel, data)`
Send message to all listening agents.

```javascript
await runtime.broadcast('events/shutdown', { reason: 'maintenance' });
```

##### `subscribe(channel, handler)`
Subscribe to events from any agent.

```javascript
runtime.subscribe('events/*', (event) => {
  console.log('Event:', event);
});
```

##### `shutdown(timeout?)`
Gracefully shutdown the entire runtime.

```javascript
await runtime.shutdown(60000);
```

---

## Agent Instance API

### AgentInstance

Running instance of an agent.

#### Properties

```javascript
agent.id              // Unique agent ID
agent.name            // Agent name
agent.type            // Agent type (code-agent, research-agent, etc.)
agent.status          // Current status (running, paused, stopped)
agent.startTime       // Timestamp when agent started
agent.config          // Agent configuration object
agent.resources       // Resource allocation and limits
```

#### Methods

##### `call(targetAgentId, method, args)`
Call a method on another agent.

```javascript
const result = await agent.call('other-agent-id', 'process', {
  data: 'input'
});
```

##### `invoke(toolName, args)`
Invoke a registered tool.

```javascript
const result = await agent.invoke('fetch-data', { url: 'http://...' });
```

##### `registerTool(name, handler)`
Register a callable tool.

```javascript
agent.registerTool('custom-tool', async (args) => {
  return { result: 'success' };
});
```

##### `publish(channel, data)`
Publish event to channel.

```javascript
agent.publish('events/status', { state: 'ready' });
```

##### `subscribe(channel, handler)`
Subscribe to channel events.

```javascript
agent.subscribe('events/task', (data) => {
  console.log('Task:', data);
});
```

##### `emit(type, data)`
Emit event to parent runtime.

```javascript
agent.emit('status', { state: 'processing' });
```

##### `setState(key, value)`
Store persistent state.

```javascript
await agent.setState('config', { key: 'value' });
```

##### `getState(key)`
Retrieve persistent state.

```javascript
const config = await agent.getState('config');
```

##### `checkpoint(data)`
Create recovery checkpoint.

```javascript
await agent.checkpoint({ progress: 50, lastId: 'item-123' });
```

##### `getCheckpoint()`
Retrieve last checkpoint.

```javascript
const checkpoint = await agent.getCheckpoint();
```

##### `log(level, message, data?)`
Structured logging.

```javascript
agent.log('info', 'Processing started', { itemCount: 100 });
agent.log('error', 'Failed to process', { error: e.message });
```

##### `trace(name, data?)`
Emit trace event for distributed tracing.

```javascript
agent.trace('process-item', { itemId: '123', duration: 500 });
```

##### `metric(name, value, tags?)`
Record metric.

```javascript
agent.metric('items.processed', 1, { agent: this.name, status: 'success' });
```

##### `pause()`
Pause agent execution.

```javascript
await agent.pause();
```

##### `resume()`
Resume agent execution.

```javascript
await agent.resume();
```

##### `stop(timeout?)`
Stop agent gracefully.

```javascript
await agent.stop(30000);
```

##### `kill()`
Forcefully terminate agent.

```javascript
await agent.kill();
```

---

## Context Window Management

### ContextManager

Manages token-based context windows for agents.

```javascript
const { ContextManager } = require('@titan/runtime');

const contextMgr = new ContextManager({
  maxTokens: 200000,
  reserveTokens: 10000,
  model: 'claude-opus-5'
});

const available = contextMgr.getAvailableTokens();
const reserved = contextMgr.reserveTokens(50000);
await contextMgr.release(reserved);
```

#### Methods

##### `getAvailableTokens()`
Get current available tokens.

```javascript
const available = contextMgr.getAvailableTokens();
```

##### `reserveTokens(count)`
Reserve tokens for operation.

```javascript
const reservation = contextMgr.reserveTokens(50000);
// Use reservation
await contextMgr.release(reservation);
```

##### `estimateTokens(text)`
Estimate token count for text.

```javascript
const tokens = contextMgr.estimateTokens('long text...');
```

##### `getStats()`
Get context usage statistics.

```javascript
const stats = contextMgr.getStats();
// { total: 200000, used: 125000, available: 75000 }
```

---

## Tool Registration & Discovery

### ToolRegistry

Manages tool registration and discovery.

```javascript
const { ToolRegistry } = require('@titan/runtime');

const registry = new ToolRegistry();

// Register tool
registry.register({
  name: 'fetch-data',
  description: 'Fetch data from URL',
  handler: async (url) => { /* ... */ },
  schema: {
    type: 'object',
    properties: { url: { type: 'string' } }
  }
});

// Get tool
const tool = registry.get('fetch-data');

// List tools
const tools = registry.list();

// Register MCP server
await registry.registerMCP({
  server: 'github',
  tools: ['list_repos', 'get_repo', 'create_issue']
});
```

---

## Event System

### EventBus

Central event distribution system.

```javascript
const { EventBus } = require('@titan/runtime');

const eventBus = new EventBus();

// Subscribe to events
eventBus.subscribe('events/deployment', (event) => {
  console.log('Deployment event:', event);
});

// Publish events
eventBus.publish('events/deployment', {
  type: 'started',
  agentId: 'agent-123',
  timestamp: Date.now()
});

// Subscribe with filter
eventBus.subscribe('events/deployment', (event) => {
  if (event.type === 'completed') {
    console.log('Deployment completed');
  }
}, { filter: { type: 'completed' } });

// Unsubscribe
const unsubscribe = eventBus.subscribe('events/x', () => {});
unsubscribe();
```

---

## Storage & State

### StateStore

Persistent key-value state storage.

```javascript
const { StateStore } = require('@titan/runtime');

const store = new StateStore('.titan/storage');

// Set state
await store.set('agent-id', 'config', { key: 'value' });

// Get state
const config = await store.get('agent-id', 'config');

// Delete state
await store.delete('agent-id', 'config');

// List all keys
const keys = await store.keys('agent-id');

// Clear all state for agent
await store.clear('agent-id');

// TTL support
await store.set('agent-id', 'temp', { data: 'value' }, { ttl: 3600 });
```

### CheckpointManager

Checkpoint and recovery management.

```javascript
const { CheckpointManager } = require('@titan/runtime');

const checkpointMgr = new CheckpointManager('.titan/storage/checkpoints');

// Create checkpoint
const checkpointId = await checkpointMgr.create('agent-id', {
  progress: 50,
  lastProcessedId: 'item-123',
  state: { /* agent state */ }
});

// Get checkpoint
const checkpoint = await checkpointMgr.get('agent-id', checkpointId);

// Get latest checkpoint
const latest = await checkpointMgr.getLatest('agent-id');

// Restore from checkpoint
await checkpointMgr.restore('agent-id', checkpointId);

// List checkpoints
const checkpoints = await checkpointMgr.list('agent-id');

// Delete checkpoint
await checkpointMgr.delete('agent-id', checkpointId);
```

---

## Resource Management

### ResourceManager

Allocate and manage agent resources.

```javascript
const { ResourceManager } = require('@titan/runtime');

const resourceMgr = new ResourceManager({
  totalMemory: 8192,        // MB
  totalCPU: 4.0,           // CPU cores
  totalContext: 200000     // tokens
});

// Allocate resources to agent
const allocation = resourceMgr.allocate('agent-id', {
  memory: 1024,
  cpu: 1.0,
  context: 100000,
  timeout: 3600
});

// Get current usage
const usage = resourceMgr.getUsage('agent-id');

// Adjust allocation
resourceMgr.adjust('agent-id', { memory: 2048 });

// Deallocate
resourceMgr.deallocate('agent-id');

// Get system stats
const stats = resourceMgr.getStats();
```

---

## Security & Permissions

### PermissionManager

Manage agent capabilities and permissions.

```javascript
const { PermissionManager } = require('@titan/runtime');

const permMgr = new PermissionManager('.titan/config/permissions.json');

// Grant permission
permMgr.grant('agent-id', 'tool:git:push');
permMgr.grant('agent-id', 'file:write:/home/user/projects');

// Check permission
const allowed = permMgr.check('agent-id', 'tool:git:push');

// Revoke permission
permMgr.revoke('agent-id', 'tool:git:push');

// List permissions
const perms = permMgr.list('agent-id');

// List agents with permission
const agents = permMgr.getAgentsWith('tool:git:push');
```

---

## Logging & Observability

### Logger

Structured logging system.

```javascript
const { Logger } = require('@titan/runtime');

const logger = new Logger({
  level: 'info',
  json: true,
  file: '.titan/logs/runtime.log'
});

logger.info('Agent started', { agentId: 'a1', type: 'code-agent' });
logger.warn('High memory usage', { usage: 95 });
logger.error('Task failed', { error: e.message, stack: e.stack });
logger.debug('Debug info', { details: {} });
```

### Tracer

Distributed tracing.

```javascript
const { Tracer } = require('@titan/runtime');

const tracer = new Tracer({
  service: 'titan-runtime',
  backend: 'jaeger'
});

const span = tracer.startSpan('process-item', {
  tags: { itemId: '123' }
});

// Do work
await processItem(itemId);

span.finish({ status: 'success' });
```

### MetricsCollector

Metrics collection and aggregation.

```javascript
const { MetricsCollector } = require('@titan/runtime');

const collector = new MetricsCollector({
  backend: 'prometheus',
  interval: 60000
});

collector.counter('tasks.processed', 1, {
  agent: 'agent-id',
  status: 'success'
});

collector.gauge('agents.active', 5);
collector.histogram('task.duration', 1234, { unit: 'ms' });
```

---

## Execution Context

### ExecutionContext

Sandboxed execution environment for agents.

```javascript
const { ExecutionContext } = require('@titan/runtime');

const context = new ExecutionContext({
  agentId: 'agent-id',
  timeout: 3600000,
  memory: 1024,
  sandboxed: true
});

// Execute within context
const result = await context.run(async () => {
  // Code runs with resource limits and sandboxing
  return await agent.processData(input);
});
```

---

## Health & Monitoring

### HealthMonitor

Agent health checking and recovery.

```javascript
const { HealthMonitor } = require('@titan/runtime');

const monitor = new HealthMonitor({
  checkInterval: 30000,
  recoveryAttempts: 3
});

// Register health check
monitor.registerCheck('agent-id', async (agent) => {
  return agent.status === 'running';
});

// Get health status
const health = monitor.getStatus('agent-id');
// { status: 'healthy', lastCheck: timestamp, alerts: [] }

// Listen to health events
monitor.on('unhealthy', (agentId) => {
  console.log(`Agent ${agentId} is unhealthy`);
});
```

---

## Configuration

### ConfigManager

Manage runtime configuration.

```javascript
const { ConfigManager } = require('@titan/runtime');

const configMgr = new ConfigManager('.titan/config');

// Get config
const config = configMgr.get('titan-agent-os');

// Update config
configMgr.update('titan-agent-os', { feature: true });

// Reload from file
configMgr.reload('titan-agent-os');

// List all configs
const configs = configMgr.list();
```

---

## Error Handling

### AgentError

Base error class for agent exceptions.

```javascript
class AgentError extends Error {
  constructor(message, code, details) {
    super(message);
    this.code = code;
    this.details = details;
  }
}

// Usage
throw new AgentError('Failed to process', 'PROCESS_ERROR', { itemId: 'x' });
```

### Common Error Codes

- `AGENT_NOT_FOUND` - Agent not found
- `TOOL_NOT_FOUND` - Tool not registered
- `PERMISSION_DENIED` - Permission check failed
- `RESOURCE_EXHAUSTED` - Resource limit exceeded
- `TIMEOUT` - Operation timeout
- `INVALID_CONFIG` - Invalid configuration
- `COMMUNICATION_ERROR` - Inter-agent communication failed

---

## Examples

### Complete Agent Example

```javascript
const { Agent, AgentRuntime } = require('@titan/runtime');

class DataProcessor extends Agent {
  async initialize() {
    this.registerTool('process', this.process.bind(this));
    this.subscribe('events/trigger', this.onTrigger.bind(this));
  }

  async process(data) {
    this.log('info', 'Processing data', { size: data.length });
    return { processed: data.length, timestamp: Date.now() };
  }

  async onTrigger(event) {
    this.log('info', 'Triggered', event);
    this.emit('ready', { status: 'processing' });
  }

  async run() {
    while (this.status === 'running') {
      await new Promise(r => setTimeout(r, 1000));
    }
  }
}

// Usage
const runtime = new AgentRuntime();
await runtime.start();

const agent = await runtime.spawn({
  type: DataProcessor,
  name: 'processor-1'
});

const result = await agent.invoke('process', [1, 2, 3]);
console.log(result);
```

---

**Status**: ✅ Production Ready  
**Last Updated**: July 30, 2026  
**API Version**: 2.0.0
