#!/usr/bin/env node
/**
 * Titan Zero Branch Scan - Phase 1
 * Automatically inspects every branch and categorizes them for recovery
 */

import { execSync } from 'child_process';
import * as fs from 'fs';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

interface BranchInfo {
  name: string;
  parent: string;
  ahead: number;
  behind: number;
  unique_commits: number;
  changed_files: string[];
  commit_count: number;
  status: 'already_merged' | 'fast_forward' | 'cherry_pick_candidate' | 'rebase_needed' | 'unrelated' | 'duplicate' | 'orphaned';
  conflict_risk: 'low' | 'medium' | 'high';
  last_modified: string;
  author: string;
  recovery_plan: string;
  reason?: string;
  tags: string[];
}

interface BranchAudit {
  scan_date: string;
  total_branches: number;
  categories: {
    already_merged: number;
    fast_forward: number;
    cherry_pick_candidate: number;
    rebase_needed: number;
    unrelated: number;
    duplicate: number;
    orphaned: number;
  };
  branches: BranchInfo[];
}

const MAIN_BRANCH = 'main';
const EXCLUDED_BRANCHES = ['main', 'develop', 'master', 'integration', 'release', 'recovery'];

function exec(cmd: string): string {
  try {
    return execSync(cmd, { encoding: 'utf-8' }).trim();
  } catch (e) {
    return '';
  }
}

function getAllBranches(): string[] {
  const output = exec('git branch -a');
  return output
    .split('\n')
    .map(line => line.replace(/^\*?\s+/, '').replace(/^remotes\/origin\//, ''))
    .filter(branch => branch && !EXCLUDED_BRANCHES.includes(branch) && !branch.startsWith('HEAD'))
    .filter((branch, index, self) => self.indexOf(branch) === index); // unique
}

function getBranchInfo(branchName: string): Partial<BranchInfo> {
  const ahead = parseInt(
    exec(`git rev-list --count ${MAIN_BRANCH}..${branchName}`).split(/\s+/)[0] || '0',
    10
  ) || 0;

  const behind = parseInt(
    exec(`git rev-list --count ${branchName}..${MAIN_BRANCH}`).split(/\s+/)[0] || '0',
    10
  ) || 0;

  const lastModified = exec(`git log -1 --format=%ai ${branchName}`).split('T')[0];
  const author = exec(`git log -1 --format=%an ${branchName}`);
  const commitCount = parseInt(
    exec(`git rev-list --count ${branchName}`).split(/\s+/)[0] || '0',
    10
  ) || 0;

  const changedFiles = exec(`git diff --name-only ${MAIN_BRANCH}...${branchName}`)
    .split('\n')
    .filter(f => f);

  return {
    ahead,
    behind,
    last_modified: lastModified,
    author,
    commit_count: commitCount,
    changed_files: changedFiles,
  };
}

function categorizeBranch(branch: string, info: Partial<BranchInfo>): Partial<BranchInfo> {
  // Check if already merged
  if (info.ahead === 0 && info.behind === 0) {
    return {
      status: 'already_merged',
      reason: 'No unique commits',
      recovery_plan: 'Already integrated into main',
      conflict_risk: 'low',
      tags: ['merged'],
    };
  }

  // Check if can fast-forward
  if (info.behind === 0 && info.ahead! > 0) {
    return {
      status: 'fast_forward',
      reason: 'All commits are unique to this branch',
      recovery_plan: 'Direct merge or fast-forward rebase',
      conflict_risk: 'low',
      tags: ['clean-history'],
    };
  }

  // Check for rebase needed
  if (info.behind! > 0) {
    return {
      status: 'rebase_needed',
      reason: `Behind main by ${info.behind} commits`,
      recovery_plan: 'Rebase onto current main, then cherry-pick recovery',
      conflict_risk: 'medium',
      tags: ['needs-rebase'],
    };
  }

  // Default to cherry-pick candidate
  return {
    status: 'cherry_pick_candidate',
    reason: 'Needs integration via cherry-pick recovery',
    recovery_plan: 'Create recovery branch, cherry-pick commits',
    conflict_risk: 'medium',
    tags: ['recoverable'],
  };
}

function scanBranches(): BranchAudit {
  console.log('🔍 Scanning branches...');

  const branches = getAllBranches();
  const branchInfos: BranchInfo[] = [];
  const categories = {
    already_merged: 0,
    fast_forward: 0,
    cherry_pick_candidate: 0,
    rebase_needed: 0,
    unrelated: 0,
    duplicate: 0,
    orphaned: 0,
  };

  for (const branch of branches) {
    process.stdout.write(`  Scanning ${branch}...`);

    const info = getBranchInfo(branch);
    const categorization = categorizeBranch(branch, info);

    const branchInfo: BranchInfo = {
      name: branch,
      parent: MAIN_BRANCH,
      ahead: info.ahead || 0,
      behind: info.behind || 0,
      unique_commits: info.ahead || 0,
      changed_files: info.changed_files || [],
      commit_count: info.commit_count || 0,
      last_modified: info.last_modified || new Date().toISOString().split('T')[0],
      author: info.author || 'unknown',
      status: (categorization.status as any) || 'cherry_pick_candidate',
      conflict_risk: (categorization.conflict_risk as any) || 'medium',
      recovery_plan: categorization.recovery_plan || '',
      reason: categorization.reason || '',
      tags: (categorization.tags as any) || [],
    };

    branchInfos.push(branchInfo);
    categories[branchInfo.status]++;

    console.log(` [${branchInfo.status}]`);
  }

  return {
    scan_date: new Date().toISOString(),
    total_branches: branches.length,
    categories,
    branches: branchInfos,
  };
}

function writeAudit(audit: BranchAudit): void {
  const registryDir = path.join(path.dirname(__dirname), 'registry');
  const auditPath = path.join(registryDir, 'branches.json');

  if (!fs.existsSync(registryDir)) {
    fs.mkdirSync(registryDir, { recursive: true });
  }

  fs.writeFileSync(auditPath, JSON.stringify(audit, null, 2));
  console.log(`\n✅ Audit saved to ${auditPath}`);
}

function printSummary(audit: BranchAudit): void {
  console.log('\n📊 Branch Scan Summary');
  console.log('=====================');
  console.log(`Total branches: ${audit.total_branches}`);
  console.log(`Scan date: ${audit.scan_date}`);
  console.log('\nCategories:');
  console.log(`  Already merged: ${audit.categories.already_merged}`);
  console.log(`  Fast-forward: ${audit.categories.fast_forward}`);
  console.log(`  Cherry-pick candidate: ${audit.categories.cherry_pick_candidate}`);
  console.log(`  Rebase needed: ${audit.categories.rebase_needed}`);
  console.log(`  Unrelated: ${audit.categories.unrelated}`);
  console.log(`  Duplicate: ${audit.categories.duplicate}`);
  console.log(`  Orphaned: ${audit.categories.orphaned}`);

  console.log('\n🔄 Branches by Category:');
  for (const branch of audit.branches) {
    console.log(`  ${branch.name.padEnd(40)} [${branch.status}]`);
  }
}

async function main(): Promise<void> {
  try {
    const audit = scanBranches();
    writeAudit(audit);
    printSummary(audit);
    process.exit(0);
  } catch (error) {
    console.error('❌ Scan failed:', error);
    process.exit(1);
  }
}

main();
