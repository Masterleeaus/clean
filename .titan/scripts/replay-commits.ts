#!/usr/bin/env node
/**
 * Titan Zero Commit Replay - Phase 4
 * Executes cherry-pick sequences on recovery branches
 */

import { execSync } from 'child_process';
import * as fs from 'fs';
import * as path from 'path';

interface ReplayResult {
  id: string;
  recovery_branch: string;
  total_commits: number;
  successful_picks: number;
  failed_picks: number;
  conflicts: Array<{
    commit: string;
    file: string;
    resolution: string;
  }>;
  status: 'success' | 'partial' | 'failed';
  timestamp: string;
  duration_ms: number;
}

function exec(cmd: string): string {
  try {
    return execSync(cmd, { encoding: 'utf-8' }).trim();
  } catch (e) {
    return '';
  }
}

function replayCommits(recoveryBranch: string, commits: string[]): ReplayResult {
  const startTime = Date.now();
  const result: ReplayResult = {
    id: `replay-${Date.now()}`,
    recovery_branch: recoveryBranch,
    total_commits: commits.length,
    successful_picks: 0,
    failed_picks: 0,
    conflicts: [],
    status: 'success',
    timestamp: new Date().toISOString(),
    duration_ms: 0,
  };

  console.log(`🔄 Replaying ${commits.length} commits on ${recoveryBranch}...`);

  for (let i = 0; i < commits.length; i++) {
    const commit = commits[i];
    process.stdout.write(`  [${i + 1}/${commits.length}] Cherry-picking ${commit.substring(0, 7)}...`);

    try {
      exec(`git cherry-pick ${commit}`);
      result.successful_picks++;
      console.log(' ✅');
    } catch (err: any) {
      const conflict = exec('git status --porcelain').split('\n').filter(l => l.startsWith('UU'));

      if (conflict.length > 0) {
        console.log(' ⚠️ (conflicts)');
        result.failed_picks++;
        result.status = 'partial';

        for (const conflictFile of conflict) {
          result.conflicts.push({
            commit,
            file: conflictFile.replace(/^UU\s+/, ''),
            resolution: 'manual',
          });
        }

        // Abort this cherry-pick
        exec('git cherry-pick --abort');
      } else {
        console.log(' ❌');
        result.failed_picks++;
        result.status = 'failed';
      }
    }
  }

  result.duration_ms = Date.now() - startTime;
  return result;
}

function writeReplayResult(result: ReplayResult): void {
  const recoveryDir = path.join(__dirname, '../recovery');
  const resultPath = path.join(recoveryDir, 'replay.json');

  if (!fs.existsSync(recoveryDir)) {
    fs.mkdirSync(recoveryDir, { recursive: true });
  }

  fs.writeFileSync(resultPath, JSON.stringify(result, null, 2));
  console.log(`\n✅ Replay result saved to ${resultPath}`);
}

function printResult(result: ReplayResult): void {
  console.log('\n📊 Replay Results');
  console.log('=================');
  console.log(`Recovery branch: ${result.recovery_branch}`);
  console.log(`Total commits: ${result.total_commits}`);
  console.log(`Successful: ${result.successful_picks}`);
  console.log(`Failed: ${result.failed_picks}`);
  console.log(`Conflicts: ${result.conflicts.length}`);
  console.log(`Duration: ${(result.duration_ms / 1000).toFixed(2)}s`);
  console.log(`Status: ${result.status}`);

  if (result.conflicts.length > 0) {
    console.log('\n⚠️ Conflicts detected:');
    for (const conflict of result.conflicts) {
      console.log(`  - ${conflict.file} (commit ${conflict.commit.substring(0, 7)})`);
    }
  }
}

async function main(): Promise<void> {
  const recoveryBranch = process.argv[2];
  const commitsArg = process.argv[3];

  if (!recoveryBranch || !commitsArg) {
    console.error('Usage: replay-commits.ts <recovery-branch> <commits-json>');
    process.exit(1);
  }

  try {
    const commits = JSON.parse(commitsArg);
    const result = replayCommits(recoveryBranch, commits);
    writeReplayResult(result);
    printResult(result);
    process.exit(result.status === 'success' ? 0 : 1);
  } catch (error) {
    console.error('❌ Replay failed:', error);
    process.exit(1);
  }
}

main();
