# Agent Communication Protocol

Specification for inter-agent communication in Titan OS.

---

## Overview

The Agent Communication Protocol (ACP) enables reliable, asynchronous messaging between agents with support for:

- **RPC Calls** - Synchronous request/response
- **Pub/Sub** - Asynchronous event distribution
- **Streaming** - Long-lived message streams
- **Broadcasting** - One-to-many messaging
- **Topic Routing** - Pattern-based message routing
- **Guaranteed Delivery** - Optional message persistence
- **Message Ordering** - Per-channel or global ordering

---

## Protocol Architecture

```
┌──────────────────────────────────────────────────┐
│           Agent Communication Bus               │
├──────────────────────────────────────────────────┤
│                                                  │
│  ┌──────────────────────────────────────────┐   │
│  │     RPC Dispatch Layer                   │   │
│  │  ├─ Request Routing                      │   │
│  │  ├─ Response Correlation                 │   │
│  │  └─ Timeout Management                   │   │
│  └──────────────────────────────────────────┘   │
│                                                  │
│  ┌──────────────────────────────────────────┐   │
│  │     Event Pub/Sub Layer                  │   │
│  │  ├─ Topic Management                     │   │
│  │  ├─ Subscription Routing                 │   │
│  │  └─ Message Filtering                    │   │
│  └──────────────────────────────────────────┘   │
│                                                  │
│  ┌──────────────────────────────────────────┐   │
│  │     Message Queue Layer                  │   │
│  │  ├─ Persistence                          │   │
│  │  ├─ Delivery Guarantees                  │   │
│  │  └─ Backpressure Handling                │   │
│  └──────────────────────────────────────────┘   │
│                                                  │
│  ┌──────────────────────────────────────────┐   │
│  │     Transport Layer                      │   │
│  │  ├─ TCP/WebSocket                        │   │
│  │  ├─ Message Serialization                │   │
│  │  └─ Connection Management                │   │
│  └──────────────────────────────────────────┘   │
│                                                  │
└──────────────────────────────────────────────────┘
```

---

## Message Format

### Message Envelope

All messages follow this envelope format:

```json
{
  "id": "msg-123456789",
  "type": "rpc_request|rpc_response|pub_event|broadcast|stream_data|stream_ack",
  "version": "1.0",
  "timestamp": 1690000000000,
  "sender": "agent-a-id",
  "senderName": "agent-a",
  
  "payload": {
    // Message-type-specific data
  },
  
  "headers": {
    "correlation_id": "req-123",
    "priority": "normal|high|low",
    "reply_to": "topic/channel",
    "retries": 0,
    "timeout_ms": 30000,
    "trace_id": "trace-123"
  },
  
  "metadata": {
    "agent_type": "code-agent",
    "agent_version": "1.0.0",
    "environment": "production",
    "custom": {}
  }
}
```

---

## Communication Patterns

### 1. RPC (Remote Procedure Call)

Synchronous request/response pattern.

#### Request Message

```json
{
  "id": "rpc-1",
  "type": "rpc_request",
  "sender": "agent-a",
  "payload": {
    "target_agent": "agent-b",
    "method": "processData",
    "args": { "data": [1, 2, 3] }
  },
  "headers": {
    "timeout_ms": 30000,
    "priority": "high"
  }
}
```

#### Response Message

```json
{
  "id": "rpc-resp-1",
  "type": "rpc_response",
  "sender": "agent-b",
  "payload": {
    "request_id": "rpc-1",
    "result": { "processed": true, "count": 3 },
    "error": null
  },
  "headers": {
    "correlation_id": "rpc-1"
  }
}
```

#### Implementation

```javascript
// Caller side
const result = await agent.call('agent-b-id', 'processData', {
  data: [1, 2, 3]
});

// Callee side
agent.registerTool('processData', async (args) => {
  return { processed: true, count: args.data.length };
});
```

---

### 2. Pub/Sub (Publish/Subscribe)

Asynchronous event distribution with topic-based routing.

#### Publish Message

```json
{
  "id": "pub-1",
  "type": "pub_event",
  "sender": "agent-deployment",
  "payload": {
    "channel": "events/deployment",
    "topic": "events/deployment/completed",
    "data": {
      "deployment_id": "deploy-123",
      "status": "success",
      "duration_ms": 5000
    }
  },
  "headers": {
    "priority": "normal"
  }
}
```

#### Subscribe & Receive

```javascript
// Subscribe to channel
agent.subscribe('events/deployment', (event) => {
  console.log('Deployment event:', event.data);
});

// Subscribe with filter
agent.subscribe('events/deployment/*', (event) => {
  if (event.data.status === 'success') {
    console.log('Deployment succeeded');
  }
}, { 
  filter: { status: 'success' }
});
```

#### Topic Patterns

```
events/deployment          - All deployment events
events/deployment/started  - Deployment started
events/deployment/progress - Deployment progress
events/deployment/completed- Deployment completed
events/deployment/failed   - Deployment failed

events/*/error            - Any error events
events/**                 - All events
```

---

### 3. Broadcasting

One-to-many messaging to all subscribed agents.

#### Broadcast Message

```json
{
  "id": "bcast-1",
  "type": "broadcast",
  "sender": "system",
  "payload": {
    "channel": "system/notification",
    "data": {
      "message": "System maintenance in 1 hour",
      "severity": "warning"
    }
  },
  "headers": {
    "priority": "high"
  }
}
```

#### Implementation

```javascript
// Send broadcast
runtime.broadcast('system/notification', {
  message: 'System maintenance',
  severity: 'warning'
});

// Receive broadcast
agent.subscribe('system/notification', (event) => {
  console.log('Notification:', event.data.message);
});
```

---

### 4. Streaming

Long-lived bidirectional message streams.

#### Stream Initiation

```json
{
  "id": "stream-1",
  "type": "stream_start",
  "sender": "agent-a",
  "payload": {
    "target_agent": "agent-b",
    "method": "processStream",
    "stream_id": "stream-123"
  }
}
```

#### Stream Data

```json
{
  "id": "stream-data-1",
  "type": "stream_data",
  "sender": "agent-b",
  "payload": {
    "stream_id": "stream-123",
    "data": { "chunk": "data", "sequence": 1 }
  }
}
```

#### Stream Acknowledgment

```json
{
  "id": "stream-ack-1",
  "type": "stream_ack",
  "sender": "agent-a",
  "payload": {
    "stream_id": "stream-123",
    "sequence": 1,
    "status": "received"
  }
}
```

#### Implementation

```javascript
// Establish stream
const stream = await agent.openStream('agent-b-id', 'processStream');

// Send data
stream.write({ chunk: 'data1' });
stream.write({ chunk: 'data2' });
stream.end();

// Receive from stream
stream.on('data', (chunk) => {
  console.log('Received:', chunk);
});

stream.on('end', () => {
  console.log('Stream closed');
});
```

---

## Routing

### Direct Routing

Send message directly to specific agent.

```
Source: agent-a
Destination: agent-b-id
Path: agent-a → MessageBus → agent-b
```

### Topic Routing

Route by topic pattern.

```
Source: agent-deployment
Topic: events/deployment/completed
Subscribers: [agent-logger, agent-monitor, agent-slack]
Path: agent-deployment → MessageBus → (logger, monitor, slack)
```

### Service Routing

Route by service name.

```
Source: agent-a
Service: storage
Target Method: save
Path: agent-a → ServiceRegistry → storage-agent → method
```

---

## Delivery Guarantees

### At-Most-Once (Default)
Message delivered 0 or 1 times. No persistence.

```javascript
agent.publish('events/x', { data: 'x' }, { 
  guarantee: 'at-most-once' 
});
```

### At-Least-Once
Message delivered 1 or more times. Persisted and retried.

```javascript
agent.publish('events/x', { data: 'x' }, { 
  guarantee: 'at-least-once',
  retries: 3,
  backoff: 'exponential'
});
```

### Exactly-Once
Message delivered exactly once. Deduplication using message ID.

```javascript
agent.publish('events/x', { data: 'x' }, { 
  guarantee: 'exactly-once',
  idempotency_key: 'unique-key'
});
```

---

## Message Ordering

### No Ordering
Messages may arrive out of order.

```javascript
agent.subscribe('events/x', handler, { ordering: 'none' });
```

### Per-Channel Ordering
Messages from same channel delivered in order.

```javascript
agent.subscribe('events/x', handler, { ordering: 'per-channel' });
```

### Global Ordering
All messages delivered in order across all channels.

```javascript
agent.subscribe('*', handler, { ordering: 'global' });
```

---

## Message Filtering

### Simple Filters

```javascript
// Filter by agent type
agent.subscribe('events/*', handler, { 
  filter: { senderType: 'code-agent' } 
});

// Filter by priority
agent.subscribe('events/*', handler, { 
  filter: { priority: 'high' } 
});

// Multiple filters (AND)
agent.subscribe('events/*', handler, { 
  filter: { 
    senderType: 'code-agent',
    priority: 'high'
  } 
});
```

### Regex Filters

```javascript
agent.subscribe('events/*', handler, { 
  filter: { 
    topic: /^events\/deployment\/.+/ 
  } 
});
```

### Custom Filters

```javascript
agent.subscribe('events/*', handler, { 
  filter: (message) => {
    return message.payload.data.priority === 'high' 
      && message.sender !== 'self';
  }
});
```

---

## Error Handling

### Message Delivery Errors

```
Message Lost → Persist & Retry
Network Error → Exponential backoff
Timeout → Retry with new correlation ID
Invalid Message → NAK and drop
Queue Full → Backpressure signal
```

### Error Response Format

```json
{
  "id": "rpc-error-1",
  "type": "rpc_response",
  "payload": {
    "request_id": "rpc-1",
    "result": null,
    "error": {
      "code": "TIMEOUT",
      "message": "Request timeout after 30s",
      "details": {
        "timeout_ms": 30000,
        "retries": 3
      }
    }
  }
}
```

### Handling Errors

```javascript
try {
  const result = await agent.call('agent-b', 'method', args);
} catch (error) {
  if (error.code === 'TIMEOUT') {
    console.log('Request timed out, retrying...');
  } else if (error.code === 'AGENT_NOT_FOUND') {
    console.log('Agent not found');
  }
}
```

---

## Performance Characteristics

### Latency
- Local agents: < 1ms
- Remote agents (same datacenter): 5-20ms
- Remote agents (different datacenter): 50-200ms
- Retries: exponential backoff (2s, 4s, 8s, 16s)

### Throughput
- Single connection: ~10,000 msgs/sec
- Broadcast to 100 agents: ~5,000 msgs/sec
- Streaming: 100+ MB/sec

### Message Size
- Default max: 10 MB
- Can be configured per message type
- Large payloads should use streaming

---

## Security

### Message Authentication
All messages include sender identity verified against registry.

### Message Encryption
Optional TLS encryption for inter-agent communication.

```javascript
agent.call('agent-b', 'method', args, { 
  encrypted: true 
});
```

### Permission Checking
Messages validated against sender permissions.

```
Sender Permission Check:
1. Agent A requests to call Agent B method X
2. Check Agent A has permission for "agent:call"
3. Check Agent A has permission for "method:X" on Agent B
4. Allow or deny based on permissions
```

### Audit Logging
All messages can be logged for audit trail.

```
Message Log Format:
- timestamp
- sender_id
- receiver_id
- message_type
- method/topic
- status (success/failure)
- latency_ms
```

---

## Monitoring & Observability

### Metrics

```
Messages published
├─ by type (rpc, pub, broadcast, stream)
├─ by status (sent, delivered, failed)
└─ by latency percentiles (p50, p95, p99)

Messages consumed
├─ by topic
├─ by agent
└─ by status

Queue depth
├─ messages pending
└─ bytes pending

Errors
├─ timeout errors
├─ delivery failures
└─ serialization errors
```

### Tracing

Every message includes `trace_id` for distributed tracing.

```javascript
{
  "headers": {
    "trace_id": "trace-abc123",
    "span_id": "span-def456"
  }
}
```

### Logging

```javascript
agent.log('debug', 'Message sent', {
  messageId: msg.id,
  target: msg.sender,
  method: msg.payload.method,
  latency: 5
});
```

---

## Examples

### Complete Communication Example

```javascript
// Agent A: Makes request
const agentA = await runtime.spawn({ name: 'agent-a' });

agentA.registerTool('notify', async (message) => {
  console.log('Notified:', message);
});

// Agent B: Responds to requests
const agentB = await runtime.spawn({ name: 'agent-b' });

agentB.registerTool('process', async (data) => {
  // Notify Agent A of progress
  await agentA.invoke('notify', 'Processing started');
  
  const result = await processData(data);
  
  // Notify Agent A of completion
  await agentA.invoke('notify', 'Processing complete');
  
  return result;
});

// Agent A: Calls Agent B
const result = await agentA.call(agentB.id, 'process', { data: 'x' });
console.log('Result:', result);
```

---

**Status**: ✅ Production Ready  
**Version**: 1.0.0  
**Last Updated**: July 30, 2026
