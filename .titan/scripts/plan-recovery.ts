#!/usr/bin/env node
/**
 * Titan Zero Recovery Planner - Phase 3
 * Creates detailed recovery plans for branch recovery
 */

import * as fs from 'fs';
import * as path from 'path';
import { execSync } from 'child_process';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

interface RecoveryPlan {
  id: string;
  source_branch: string;
  recovery_branch: string;
  base_branch: string;
  created_date: string;
  status: 'planned' | 'in_progress' | 'completed' | 'failed' | 'merged';
  plan: {
    step_1_create_recovery: {
      command: string;
      description: string;
      status: string;
    };
    step_2_cherry_pick: {
      commits: string[];
      expected_conflicts: Array<{
        file: string;
        type: string;
        resolution: string;
      }>;
      status: string;
    };
    step_3_compile: {
      commands: string[];
      expected_duration: string;
      status: string;
    };
    step_4_tests: {
      commands: string[];
      coverage_target: number;
      status: string;
    };
    step_5_audit: {
      checks: string[];
      status: string;
    };
  };
  conflicts_resolved: Array<{
    file: string;
    conflict_type: string;
    resolution: string;
    resolved_by: string;
  }>;
  test_results?: {
    passed: number;
    failed: number;
    skipped: number;
    coverage: number;
    status: string;
  };
  audit_results?: {
    no_duplicate_classes: boolean;
    no_orphaned_routes: boolean;
    di_valid: boolean;
    namespace_collisions: boolean;
    all_imports_valid: boolean;
    issues: string[];
  };
  risk_assessment: {
    merge_risk: 'low' | 'medium' | 'high';
    regression_risk: 'low' | 'medium' | 'high';
    conflict_risk: 'low' | 'medium' | 'high';
    overall_assessment: string;
  };
  notes: string;
}

function exec(cmd: string): string {
  try {
    return execSync(cmd, { encoding: 'utf-8' }).trim();
  } catch (e) {
    return '';
  }
}

function createRecoveryPlan(sourceBranch: string, baseBranch: string = 'main'): RecoveryPlan {
  const recoveryBranch = `recovery/${sourceBranch.replace(/^[a-z]+\//, '')}`;
  const commits = exec(`git log ${baseBranch}..${sourceBranch} --format=%H`).split('\n').filter(c => c);

  const plan: RecoveryPlan = {
    id: `recovery-${Date.now()}`,
    source_branch: sourceBranch,
    recovery_branch: recoveryBranch,
    base_branch: baseBranch,
    created_date: new Date().toISOString(),
    status: 'planned',
    plan: {
      step_1_create_recovery: {
        command: `git checkout -b ${recoveryBranch} ${baseBranch}`,
        description: 'Create fresh recovery branch from main',
        status: 'pending',
      },
      step_2_cherry_pick: {
        commits,
        expected_conflicts: [],
        status: 'pending',
      },
      step_3_compile: {
        commands: [
          'composer install',
          'npm install',
          'npm run build',
        ],
        expected_duration: '3-5m',
        status: 'pending',
      },
      step_4_tests: {
        commands: [
          'npm run test',
          'npm run test:unit',
          'npm run test:integration',
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
    notes: `Created recovery plan for ${sourceBranch}. Contains ${commits.length} commits to replay.`,
  };

  return plan;
}

function writePlan(plan: RecoveryPlan, branchName: string): void {
  const recoveryDir = path.join(path.dirname(__dirname), 'recovery');
  const planPath = path.join(recoveryDir, `${branchName}.json`);

  if (!fs.existsSync(recoveryDir)) {
    fs.mkdirSync(recoveryDir, { recursive: true });
  }

  fs.writeFileSync(planPath, JSON.stringify(plan, null, 2));
  console.log(`✅ Recovery plan saved to ${planPath}`);
}

function printPlan(plan: RecoveryPlan): void {
  console.log('\n📋 Recovery Plan');
  console.log('================');
  console.log(`Source branch: ${plan.source_branch}`);
  console.log(`Recovery branch: ${plan.recovery_branch}`);
  console.log(`Base branch: ${plan.base_branch}`);
  console.log(`Commits to replay: ${plan.plan.step_2_cherry_pick.commits.length}`);
  console.log(`\nStatus: ${plan.status}`);
  console.log(`Risk assessment: ${plan.risk_assessment.overall_assessment}`);
  console.log('\nSteps:');
  console.log(`1. Create recovery: ${plan.plan.step_1_create_recovery.command}`);
  console.log(`2. Cherry-pick: ${plan.plan.step_2_cherry_pick.commits.length} commits`);
  console.log(`3. Compile: ${plan.plan.step_3_compile.commands.join(', ')}`);
  console.log(`4. Test: Target coverage ${plan.plan.step_4_tests.coverage_target}%`);
  console.log(`5. Audit: ${plan.plan.step_5_audit.checks.length} checks`);
}

async function main(): Promise<void> {
  const branchName = process.argv[2];

  if (!branchName) {
    console.error('Usage: plan-recovery.ts <branch-name>');
    process.exit(1);
  }

  try {
    console.log(`📝 Creating recovery plan for ${branchName}...`);
    const plan = createRecoveryPlan(branchName);
    writePlan(plan, branchName);
    printPlan(plan);
    process.exit(0);
  } catch (error) {
    console.error('❌ Recovery planning failed:', error);
    process.exit(1);
  }
}

main();
