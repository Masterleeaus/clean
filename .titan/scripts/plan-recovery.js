#!/usr/bin/env node
/**
 * Titan Zero Recovery Planner - Phase 3
 * Creates detailed recovery plans for branch recovery
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

function exec(cmd) {
  try {
    return execSync(cmd, { encoding: 'utf-8' }).trim();
  } catch (e) {
    return '';
  }
}

function createRecoveryPlan(sourceBranch) {
  const recoveryBranch = `recovery/${sourceBranch.replace(/^[a-z]+\//, '')}`;

  // Get commits from source branch
  const commits = exec(`git log main..${sourceBranch} --format=%H`).split('\n').filter(c => c);
  const commitCount = commits.length;

  const plan = {
    id: `recovery-${Date.now()}`,
    source_branch: sourceBranch,
    recovery_branch: recoveryBranch,
    base_branch: 'main',
    created_date: new Date().toISOString(),
    status: 'planned',
    plan: {
      step_1_create_recovery: {
        command: `git checkout -b ${recoveryBranch} main`,
        description: 'Create fresh recovery branch from main',
        status: 'pending',
      },
      step_2_cherry_pick: {
        commits: commits,
        commit_count: commitCount,
        expected_conflicts: [],
        status: 'pending',
      },
      step_3_compile: {
        commands: [
          'npm install',
          'npm run build',
        ],
        expected_duration: '3-5m',
        status: 'pending',
      },
      step_4_tests: {
        commands: [
          'npm test',
        ],
        coverage_target: 75,
        status: 'pending',
      },
      step_5_audit: {
        checks: [
          'no_duplicate_classes',
          'no_orphaned_routes',
          'di_container_valid',
          'namespace_collisions_check',
          'import_resolution_check',
        ],
        status: 'pending',
      },
    },
    conflicts_resolved: [],
    risk_assessment: {
      merge_risk: 'medium',
      regression_risk: 'medium',
      conflict_risk: 'medium',
      overall_assessment: 'Recovery plan created. Ready for execution.',
    },
    notes: `Created recovery plan for ${sourceBranch}. Contains ${commitCount} commits to replay.`,
  };

  return plan;
}

function writePlan(plan, branchName) {
  const recoveryDir = path.join(__dirname, '../recovery');
  const planPath = path.join(recoveryDir, `${branchName}.json`);

  if (!fs.existsSync(recoveryDir)) {
    fs.mkdirSync(recoveryDir, { recursive: true });
  }

  fs.writeFileSync(planPath, JSON.stringify(plan, null, 2));
  console.log(`✅ Recovery plan saved to ${planPath}`);
}

function printPlan(plan) {
  console.log('\n📋 Recovery Plan');
  console.log('=================');
  console.log(`Source branch: ${plan.source_branch}`);
  console.log(`Recovery branch: ${plan.recovery_branch}`);
  console.log(`Base branch: ${plan.base_branch}`);
  console.log(`Commits to replay: ${plan.plan.step_2_cherry_pick.commit_count}`);
  console.log(`\nStatus: ${plan.status}`);
  console.log(`Risk assessment: ${plan.risk_assessment.overall_assessment}`);
  console.log('\nSteps:');
  console.log(`1. Create recovery: ${plan.plan.step_1_create_recovery.command}`);
  console.log(`2. Cherry-pick: ${plan.plan.step_2_cherry_pick.commit_count} commits`);
  console.log(`3. Compile: ${plan.plan.step_3_compile.commands.join(', ')}`);
  console.log(`4. Test: Target coverage ${plan.plan.step_4_tests.coverage_target}%`);
  console.log(`5. Audit: ${plan.plan.step_5_audit.checks.length} checks`);
}

async function main() {
  const branchName = process.argv[2];

  if (!branchName) {
    console.error('Usage: plan-recovery.js <branch-name>');
    process.exit(1);
  }

  try {
    console.log(`📝 Creating recovery plan for ${branchName}...`);
    const plan = createRecoveryPlan(branchName);
    writePlan(plan, branchName);
    printPlan(plan);
    process.exit(0);
  } catch (error) {
    console.error('❌ Recovery planning failed:', error.message);
    process.exit(1);
  }
}

main();
