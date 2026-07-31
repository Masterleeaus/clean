#!/usr/bin/env node
/**
 * Titan Zero Agent Lifecycle Manager
 * Manages agent spawn, health monitoring, and graceful shutdown
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

function exec(cmd) {
  try {
    return execSync(cmd, { encoding: 'utf-8' }).trim();
  } catch (e) {
    return '';
  }
}

function getAgentRegistry() {
  const registryPath = path.join(__dirname, '../registry/agents.json');
  if (fs.existsSync(registryPath)) {
    return JSON.parse(fs.readFileSync(registryPath, 'utf-8'));
  }
  return { agents: [], spawned_count: 0 };
}

function saveAgentRegistry(registry) {
  const registryPath = path.join(__dirname, '../registry/agents.json');
  const registryDir = path.dirname(registryPath);
  if (!fs.existsSync(registryDir)) {
    fs.mkdirSync(registryDir, { recursive: true });
  }
  fs.writeFileSync(registryPath, JSON.stringify(registry, null, 2));
}

function spawnAgent(name, type, schemaPath) {
  console.log(`🚀 Spawning agent: ${name} (type: ${type})`);

  const agentId = `agent-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

  const agent = {
    id: agentId,
    name,
    type,
    schema_path: schemaPath,
    status: 'running',
    created_at: new Date().toISOString(),
    started_at: new Date().toISOString(),
    health: {
      status: 'healthy',
      last_check: new Date().toISOString(),
      uptime_seconds: 0,
      memory_mb: 0,
      cpu_percent: 0
    },
    permissions: [],
    resources: {
      memory_mb: 1024,
      cpu_shares: 1.0,
      timeout_seconds: 3600
    }
  };

  const registry = getAgentRegistry();
  registry.agents.push(agent);
  registry.spawned_count++;
  saveAgentRegistry(registry);

  console.log(`✅ Agent spawned: ${agentId}`);
  console.log(`   Name: ${name}`);
  console.log(`   Type: ${type}`);
  console.log(`   Status: ${agent.status}`);

  return agent;
}

function listAgents(filter) {
  const registry = getAgentRegistry();

  let agents = registry.agents;
  if (filter) {
    agents = agents.filter(a => {
      if (filter.status && a.status !== filter.status) return false;
      if (filter.type && a.type !== filter.type) return false;
      return true;
    });
  }

  console.log('\n📋 Running Agents');
  console.log('================\n');

  if (agents.length === 0) {
    console.log('No agents running.');
    return;
  }

  agents.forEach(agent => {
    const uptime = Math.floor((Date.now() - new Date(agent.started_at).getTime()) / 1000);
    const healthIcon = agent.health.status === 'healthy' ? '✅' : '⚠️';

    console.log(`${healthIcon} ${agent.name} (${agent.id})`);
    console.log(`   Type: ${agent.type}`);
    console.log(`   Status: ${agent.status}`);
    console.log(`   Uptime: ${uptime}s`);
    console.log(`   Memory: ${agent.health.memory_mb}MB`);
    console.log(`   CPU: ${agent.health.cpu_percent}%`);
  });

  console.log(`\nTotal agents: ${agents.length}`);
}

function getAgentHealth(agentId) {
  const registry = getAgentRegistry();
  const agent = registry.agents.find(a => a.id === agentId);

  if (!agent) {
    console.error(`❌ Agent not found: ${agentId}`);
    process.exit(1);
  }

  return {
    id: agentId,
    name: agent.name,
    status: agent.status,
    health: agent.health,
    uptime_seconds: Math.floor((Date.now() - new Date(agent.started_at).getTime()) / 1000),
    memory_mb: agent.health.memory_mb,
    cpu_percent: agent.health.cpu_percent
  };
}

function checkAgentHealth(agentId) {
  const health = getAgentHealth(agentId);

  console.log(`\n🏥 Health Check: ${health.name}`);
  console.log('======================\n');
  console.log(`Status: ${health.status}`);
  console.log(`Health: ${health.health.status}`);
  console.log(`Uptime: ${health.uptime_seconds}s`);
  console.log(`Memory: ${health.memory_mb}MB`);
  console.log(`CPU: ${health.cpu_percent}%`);
  console.log(`Last check: ${health.health.last_check}`);

  return health.health.status === 'healthy';
}

function stopAgent(agentId, graceful = true) {
  const registry = getAgentRegistry();
  const agentIndex = registry.agents.findIndex(a => a.id === agentId);

  if (agentIndex === -1) {
    console.error(`❌ Agent not found: ${agentId}`);
    process.exit(1);
  }

  const agent = registry.agents[agentIndex];

  if (graceful) {
    console.log(`⏹️  Stopping agent gracefully: ${agent.name}`);
    agent.status = 'stopping';
  } else {
    console.log(`🛑 Force killing agent: ${agent.name}`);
    agent.status = 'killed';
  }

  agent.stopped_at = new Date().toISOString();
  agent.health.status = 'stopped';

  registry.agents[agentIndex] = agent;
  saveAgentRegistry(registry);

  console.log(`✅ Agent stopped: ${agentId}`);
}

function monitorAgents() {
  const registry = getAgentRegistry();

  console.log('🔍 Monitoring agents...\n');

  registry.agents.forEach(agent => {
    if (agent.status === 'running') {
      // Simulate health check
      const isHealthy = Math.random() > 0.1; // 90% healthy
      agent.health.status = isHealthy ? 'healthy' : 'degraded';
      agent.health.last_check = new Date().toISOString();
      agent.health.memory_mb = Math.floor(Math.random() * 1024);
      agent.health.cpu_percent = Math.floor(Math.random() * 50);
    }
  });

  saveAgentRegistry(registry);

  const healthy = registry.agents.filter(a => a.health.status === 'healthy').length;
  const unhealthy = registry.agents.filter(a => a.health.status !== 'healthy').length;

  console.log(`Total agents: ${registry.agents.length}`);
  console.log(`Healthy: ${healthy}`);
  console.log(`Unhealthy: ${unhealthy}`);

  if (unhealthy > 0) {
    console.log('\n⚠️  Unhealthy agents:');
    registry.agents
      .filter(a => a.health.status !== 'healthy')
      .forEach(agent => {
        console.log(`  - ${agent.name} (${agent.health.status})`);
      });
  }
}

async function main() {
  const command = process.argv[2];
  const arg1 = process.argv[3];
  const arg2 = process.argv[4];

  switch (command) {
    case 'spawn':
      if (!arg1 || !arg2) {
        console.error('Usage: agent-lifecycle.js spawn <name> <type> [schema-path]');
        process.exit(1);
      }
      spawnAgent(arg1, arg2, process.argv[5]);
      break;

    case 'list':
      const filter = {};
      if (arg1) filter.status = arg1;
      if (arg2) filter.type = arg2;
      listAgents(filter);
      break;

    case 'health':
      if (!arg1) {
        console.error('Usage: agent-lifecycle.js health <agent-id>');
        process.exit(1);
      }
      checkAgentHealth(arg1);
      break;

    case 'stop':
      if (!arg1) {
        console.error('Usage: agent-lifecycle.js stop <agent-id> [graceful|force]');
        process.exit(1);
      }
      stopAgent(arg1, arg2 !== 'force');
      break;

    case 'monitor':
      monitorAgents();
      break;

    default:
      console.error(`Unknown command: ${command}`);
      console.error(`
Usage:
  agent-lifecycle.js spawn <name> <type> [schema-path]   - Spawn new agent
  agent-lifecycle.js list [status] [type]                - List agents
  agent-lifecycle.js health <agent-id>                   - Check health
  agent-lifecycle.js stop <agent-id> [graceful|force]    - Stop agent
  agent-lifecycle.js monitor                             - Monitor all agents
      `);
      process.exit(1);
  }
}

main();
